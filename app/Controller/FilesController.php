<?php

/**
 * Class FilesController
 */
class FilesController extends AppController {

    public $uses = ['File','Substance','Identifier','Technique'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles');
    }

    /**
     * Add a file
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            $data=$this->request->data;
            $data['File']['name']=$data['File']['file']['name'];
            $data['File']['size']=$data['File']['file']['size'];
            $data['File']['type']=$data['File']['file']['type'];
            $tmpname=$data['File']['file']['tmp_name'];
            unset($data['File']['file']);

            // TODO: Mime type not correct for .jdx files

            // Add to database
            $this->File->create();
            if(!$this->File->save($data)){
                debug($this->File->validationErrors); die();
            }
            $id=str_pad($this->File->id,9,"0",STR_PAD_LEFT);

            // Move file to storage location (based on extension)
            $ext = pathinfo($data['File']['name'], PATHINFO_EXTENSION);
            $path="files".DS.$ext;
            $folder = new Folder(WWW_ROOT.$path,true,0777);
            $path=$path.DS.$id.".".$ext;
            move_uploaded_file($tmpname,WWW_ROOT.$path);
            $this->File->saveField('path',"/".$path);

            // Get version of format
            if($ext=="jdx"||$ext=="dx") {
                $file=file_get_contents(WWW_ROOT.$path);
                // Detect line endings
                if(stristr($file,"\r\n")) {
                    $eol="\r\n";
                } elseif(stristr($file,"\r")) {
                    $eol="\r";
                } elseif(stristr($file,"\n")) {
                    $eol="\r";
                }
                // JCAMP version
                list(,$temp)=explode("JCAMP-DX=",$file,2);
                list($ver,)=explode(" ",$temp,2);
                $this->File->saveField('version',$ver);
                // JCAMP Data Type
                list(,$temp)=explode("DATA TYPE=",$file,2);
                list($type,)=explode($eol,$temp,2); // Must use ' here not "
                if(stristr($type,"NMR")) {
                    // Find nucleus
                    list(, $temp) = explode("OBSERVE NUCLEUS=", $file, 2);
                    list($nuc,) = explode($eol, $temp, 2); // Must use ' here not "
                    $tech = $this->Technique->find('first', ['conditions' => ['matchstr' => str_replace("^", "", $nuc) . "NMR"]]);
                }
                $this->File->saveField('technique_id',$tech['Technique']['id']);

            }

            // Add substance_id
            $sub=$this->Identifier->find('first',['conditions'=>['value'=>$data['File']['substance']]]);
            debug($sub);exit;

            // Capture activity
            $this->Activity->create();
            $activity=['Activity'=>[
                'user_id'=>$this->Auth->user('id'),
                'file_id'=>$this->File->id,
                'step_num'=>1,
                'type'=>'upload',
                'comment'=>'',
            ]];
            $this->Activity->save($activity);

            // Redirect to view
            return $this->redirect('/files/view/' . $this->File->id);
        } else {
            // Get source_id here
        }
    }

    /**
     * Add a whole bunch of files
     */
    public function massUpload(){
        if($this->request->is('post')) {
            $zip = new ZipArchive;
            $res = $zip->open($this->request->data['File']['file']['tmp_name']);
            if ($res === TRUE) {
                $zip->extractTo(WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name']);
                $zip->close();
                $dir=WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name'];
                $files=scandir($dir);
                foreach($files as $file){
                    if(pathinfo($file, PATHINFO_EXTENSION) == "pdf") {
                        $requestFile['File']=array();
                        $requestFile['File']['file']['name']=pathinfo($file, PATHINFO_FILENAME).".pdf";;
                        $requestFile['File']['file']['tmp_name']=WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name'].DS.$requestFile['File']['file']['name'];
                        $requestFile['File']['file']['size']=filesize($requestFile['File']['file']['tmp_name']);
                        $requestFile['File']['publication_id']=$this->request->data['File']['publication_id'];
                        $requestFile['File']['num_systems']=$this->request->data['File']['num_systems'];
                        $response=$this->requestAction('/Files/add',$requestFile);
                        if($response!==true){
                            echo "Failed :".$requestFile['File']['file']['name']."(".$response.")<br>";
                        }else{
                            echo "Added :".$requestFile['File']['file']['name']."<br>";
                        }

                        //unlink($requestFile['File']['file']['tmp_name']);
                    }
                }
                if($dir!==""&&$dir!==Null) {
                    $files = array_diff(scandir($dir), array('.', '..'));
                    foreach ($files as $file) {
                        unlink($dir . DS . $file);
                    }
                    rmdir($dir);
                }
                return $this->redirect('/Files/');
            } else {
                return $this->redirect('/Files/massUpload'.$this->File->id);
            }
        }else{
            $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
            $this->set('pubs',$pubs);
        }
    }

    /**
     * View a file
     * @param $id
     */
    public function view($id)
    {
        $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
        $this->set('data',$data);
    }

    /**
     * Update a file
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            //echo "<pre>";print_r($this->request->data);echo "</pre>";exit;
            $this->File->id=$id;
            $this->File->save($this->request->data);
            $this->redirect(['action' => 'index']);
        } else {
            $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
            $this->set('data',$data);
            $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
            $this->set('pubs',$pubs);
            $this->set('id',$id);
        }
    }

    /**
     * Delete a file
     * @param $id
     */
    public function delete($id)
    {
        $this->File->delete($id);
        return $this->redirect(['action' => 'index']);

    }

    /**
     * List the files
     */
    public function index()
    {
        $data=$this->File->find('list',['fields'=>['id','filename','publication_id'],'order'=>['id','publication_id']]);
        $this->set('data',$data);
        $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
        $this->set('pubs',$pubs);
    }

    /**
     * Count the files
     * @return mixed
     */
    public function totalfiles()
    {
        $data=$this->File->find('count');
        return $data;
    }

    /**
     * Processing
     */
    public function processing()
    {
        $files=$this->File->find('list',['fields'=>['id','filename','publication_id'],'order'=>['id','filename']]);
        $this->set('files',$files);
        $textfile=$this->TextFile->find('list',['fields'=>['id','file_id'],'order'=>['id','file_id']]);
        $this->set('textfile',$textfile);
        $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
        $this->set('pubs',$pubs);
    }

}
?>