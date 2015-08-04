<?php
require_once(APP."Vendor".DS."Reader.php");
/**
 * Class TextFilesController
 * test
 */
class TextFilesController extends AppController
{
    public $uses=['File','TextFile','Publication','Ruleset','Rule','Propertytype','Property','Activity'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles');
    }
    /**
     * List the text files
     */
    public function index()
    {
        $data=$this->TextFile->find('list',['fields'=>['id','version','file_id'],'order'=>['file_id']]);
        $this->set('data',$data);
        $files=$this->File->find('list',['fields'=>['id','filename','publication_id'],'order'=>['publication_id']]);
        $this->set('files',$files);
        $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
        $this->set('pubs',$pubs);

    }

    /**
     * Add a new text file
     */
    public function add($id=null)
    {
        if (!empty($this->data)||$id!=null) {
            if(!empty($this->data)){
                $id=$this->data['TextFile']['inputFile'];
            }
            $file=$this->File->find('first',['conditions'=>['File.id'=>$id],'recursive'=>3]); //get the file of interest

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $pdfToTextPath = Configure::read("pdftotextPath.windows"); //save path to the pdftotext for the server
            }elseif (PHP_OS=="Linux") {
                $pdfToTextPath=Configure::read("pdftotextPath.linux");
            }elseif (PHP_OS=="FreeBSD") {
                $pdfToTextPath=Configure::read("pdftotextPath.freebsd");
            }else{
                $pdfToTextPath=Configure::read("pdftotextPath.mac");
            }
            $fileToExtract=WWW_ROOT.'files'.DS.'pdf'.DS.$file['File']['publication_id'].DS.$file['File']['filename'];// find the path to the file name

            if($file['File']['format']==0) {
                $text = shell_exec($pdfToTextPath . ' -layout -r 300 -H ' . $file['Propertytype']['height'] . ' -W ' . $file['Propertytype']['width'] . ' "' . $fileToExtract . '" -'); //run the extraction
                $ruleset=$file['Propertytype']['Ruleset'];
                $ruleset=$this->Ruleset->generateRulesetArray($ruleset); //convert ruleset to a compatible config
            }else{
                $text = shell_exec($pdfToTextPath . ' -layout -r 300  "' . $fileToExtract . '" -'); //run the extraction
                $ruleset=$this->Ruleset->find("first",['conditions'=>['Ruleset.id'=>4],'recursive'=>2]);
                $ruleset=$this->Ruleset->generateRulesetArray($ruleset);
            }
            if(empty($text)){
                $this->Session->setFlash('File improperly converted, Text Empty');
                return $this->redirect('/textfiles/add/');
            }
            if(empty($ruleset)) {
                $this->Session->setFlash('File improperly converted, No ruleset found');
                return $this->redirect('/textfiles/add/');
            }
            $Reader=new Reader(); //initialize reader
            $text=$Reader->FixCharacters($text,Configure::read("textReplacementArray")); //Replace mis read characters
            $stream = fopen('php://temp','r+'); //save the text to a stream so that the reader can use it
            fwrite($stream, $text);
            rewind($stream); //set the stream back to position 0;



            $Reader->SetConfig($ruleset); //set the config for this file
            $Reader->setStream($stream); //load the file stream into the reader
            $Json=$Reader->ReadFile(); //extract the data
            if(empty($Json)){
                $this->Session->setFlash('Reading Text Returned Empty Array, Double Check Ruleset');
                return $this->redirect('/textfiles/add/');
            }
            $i=0; //use this while loop to make sure the data is in the right columns

            while(isset($Json['Data'][0][$i])) {
                if ($i != 0) { //if this is not the first item
                    if (!isset($Json['Data'][0][$i-1])|| strlen($Json['Data'][0][$i-1]) != strlen($Json['Data'][0][$i])) { //check if this one has more spaces in front than the last one
                        $Json['Data'][2][]=$Json['Data'][0][$i]; //copy the data
                        $Json['Data'][3][]=$Json['Data'][1][$i];

                        unset($Json['Data'][0][$i]); //remove it from first column
                        unset($Json['Data'][1][$i]);


                    }
                }
                $i++;
            }
            if(is_array($Json['CAS'])) {
                foreach ($Json['CAS'] as $i => $cas) { //remove CAS from name
                    $Json['chemicalName'][$i] = substr($Json['chemicalName'][$i], 0, (strpos($Json['chemicalName'][$i], $cas) - 2));
                }
            }
            array_walk_recursive($Json,"trim_array"); //clean up all the extras spaces left behind
            //var_dump($Json);
            $data=['TextFile'=> [ //save the data to a new array
                'text'=>$text,
                'file_id'=>$file['File']['id'],
                'version'=>1,
                'extracted_data'=>json_encode($Json)
            ]];
            //remove old text files
            $textfile=$this->TextFile->find('list',['conditions'=>['TextFile.file_id'=>$id]]);
            if(count($textfile)){
                    $this->TextFile->deleteAll(['TextFile.file_id'=>$id]);
            }

            $this->TextFile->create(); //create a new TextFile
            if($this->TextFile->save($data)){
                $this->Activity->create();
                $activity=['Activity'=>[
                    'user_id'=>$this->Auth->user('id'),
                    'file_id'=>$file['File']['id'],
                    'step_num'=>2,
                    'type'=>'extract',
                    'comment'=>'',
                ]];
                $this->Activity->save($activity);
                return $this->redirect('/textfiles/view/'.$this->TextFile->id);
            }



        }else{
            $rules=$this->Ruleset->find('list',['fields'=>['id','name']]);
            $file = $this->File->find('list', ['fields'=>['id','filename']]);
            $this->set('file', $file);
            $this->set('rulesets', $rules);
        }

    }

    /**
     * View a text file
     * @param $id
     */
    public function view($id)
    {
        $textfile=$this->TextFile->find('first',['conditions'=>['TextFile.id'=>$id]]);
        $file=$this->File->find('first',['conditions'=>['File.id'=>$textfile['TextFile']['file_id']],'recursive'=>1]);
        if(isset($_GET['submitGithubIssue'])){
            $args=array();
            $args['title']="Problem with File ".$file['File']['filename'];
            $args['body']=$this->Session->read('Auth.User.username')." reported ".$_POST['body'];
            $args['assignee']="whinis";

            $token="15c0a4e7edcbb328d40c52b1d5035f38338ad47b";
            $req = curl_init();
            curl_setopt($req,CURLOPT_URL,"https://api.github.com/repos/stuchalk/Springer/issues?access_token=".$token);
            curl_setopt($req, CURLOPT_POST, true);
            curl_setopt($req, CURLOPT_USERAGENT, "Whinis Springer app");
            curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($args));
            curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($req, CURLOPT_TIMEOUT, 30);
            $result = curl_exec($req);
            $http_code = curl_getinfo($req,  CURLINFO_HTTP_CODE);
            $http_errno = curl_errno($req);
            echo $result;
            curl_close($req);
            die();
        }
        $this->set('textfile',$textfile);
        $path=Configure::read('path');
        $pdf=$path.'/files/pdf/'.$file['File']['publication_id'].'/'.$file['File']['filename'];
        $this->set('pdf',$pdf);
        $this->set('path',$path);
    }

    /**
     * Update a text file
     */
    public function update($id)
    {
        if(!empty($this->request->data))
        {
            $this->TextFile->id=$id;
            $this->TextFile->save($this->request->data);
            $this->redirect('/textfiles/view/'.$id);

        } else {
            $textfile=$this->TextFile->find('first',['conditions'=>['TextFile.id'=>$id]]);
            $this->set('textfile',$textfile);
            $this->set('id',$id);
            $rules=$this->Ruleset->find('list',['fields'=>['id','name']]);
            $this->set('rulesets', $rules);
            $file=$this->File->find('list',['fields'=>['id','filename']]);
            $this->set('file', $file);
        }
    }
    public function edit($id){
        if(!empty($this->request->data)) {
            $textfile=$this->TextFile->find('first',['conditions'=>['TextFile.id'=>$id]]);
            $textfile['TextFile']['text']=$this->request->data['text'];

            $file=$this->File->find('first',['conditions'=>['File.id'=>$textfile['TextFile']['file_id']],'recursive'=>3]); //get the file of interest

            if($file['File']['format']==0) {
                $ruleset=$file['Propertytype']['Ruleset'];
                $ruleset=$this->Ruleset->generateRulesetArray($ruleset); //convert ruleset to a compatible config
            }else{
                $ruleset=$this->Ruleset->find("first",['conditions'=>['Ruleset.id'=>4],'recursive'=>2]);
                $ruleset=$this->Ruleset->generateRulesetArray($ruleset);
            }
            if(empty($ruleset)) {
                die('{"error":"File improperly converted, No ruleset found"}');
            }
            $Reader=new Reader(); //initialize reader
            $textfile['TextFile']['text']=$Reader->FixCharacters($textfile['TextFile']['text'],Configure::read("textReplacementArray")); //Replace mis read characters
            $stream = fopen('php://temp','r+'); //save the text to a stream so that the reader can use it
            fwrite($stream, $textfile['TextFile']['text']);
            rewind($stream); //set the stream back to position 0;



            $Reader->SetConfig($ruleset); //set the config for this file
            $Reader->setStream($stream); //load the file stream into the reader
            $Json=$Reader->ReadFile(); //extract the data
            if(empty($Json)){
                die('{"error":"Reading Text Returned Empty Array, Double Check Ruleset"}');
            }
            $i=0; //use this while loop to make sure the data is in the right columns

            while(isset($Json['Data'][0][$i])) {
                if ($i != 0) { //if this is not the first item
                    if (!isset($Json['Data'][0][$i-1])|| strlen($Json['Data'][0][$i-1]) != strlen($Json['Data'][0][$i])) { //check if this one has more spaces in front than the last one
                        $Json['Data'][2][]=$Json['Data'][0][$i]; //copy the data
                        $Json['Data'][3][]=$Json['Data'][1][$i];

                        unset($Json['Data'][0][$i]); //remove it from first column
                        unset($Json['Data'][1][$i]);


                    }
                }
                $i++;
            }
            if(is_array($Json['CAS'])) {
                foreach ($Json['CAS'] as $i => $cas) { //remove CAS from name
                    $Json['chemicalName'][$i] = substr($Json['chemicalName'][$i], 0, (strpos($Json['chemicalName'][$i], $cas) - 2));
                }
            }
            array_walk_recursive($Json,"trim_array"); //clean up all the extras spaces left behind
            $textfile['TextFile']['extracted_data']=json_encode($Json);
            unset($textfile['TextFile']['id']);
            $this->TextFile->clear();
            $this->TextFile->create();
            if($this->TextFile->save($textfile)) {
                die('{"result":"success","id":'.$this->TextFile->id.'}');
            }else{
                die('{"result":"error"}');
            }
        }

    }

    /**
     * Delete a text file
     */
    public function delete($id)
    {
        $this->TextFile->delete($id);
        return $this->redirect(['action' => 'index']);
    }

    public function totalfiles()
    {
        $data=$this->TextFile->find('count');
        return $data;
    }

}

//used to trim the array
function trim_array(&$item, $key)
{
    $item=trim($item);
}


?>