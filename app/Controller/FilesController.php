<?php
set_time_limit(0);
/**
 * Class FilesController
 */
class FilesController extends AppController {

    public $uses = array('File','Publication','Propertytype','Activity','TextFile');

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
            $uploadedFile=array();
            if (!empty($this->request->params['requested'])) {
                $uploadedFile=$this->request->params['File'];
            }else{
                $uploadedFile=$this->request->data['File'];
            }
            // Get the filename and filesize
            $uploadedFile['filename']=$uploadedFile['file']['name'];
            $uploadedFile['filesize']=$uploadedFile['file']['size'];
            // Move PDF file to storage location
            $path=WWW_ROOT."files".DS."pdf".DS.$uploadedFile['publication_id'];
            $folder = new Folder($path,true,0777);
            if (!empty($this->request->params['requested'])) {
                rename($uploadedFile['file']['tmp_name'],$path.DS.$uploadedFile['filename']);
            }else{
                move_uploaded_file($uploadedFile['file']['tmp_name'],$path.DS.$uploadedFile['filename']);
            }
            // Get PDF version using Utility component function
            $uploadedFile['pdf_version']=$this->Utils->pdfVersion($path.DS.$uploadedFile['filename']);
            // Add URL to file on Springer website
            $uploadedFile['url']="http://link.springer.com/chapter/10.1007/".str_replace(".pdf","",$uploadedFile['filename']);
            // Clear File array
            unset($uploadedFile['file']);
            // Get publication code
            $code=$this->File->getCode($uploadedFile['filename'], $uploadedFile['publication_id']);
            $propertytype=$this->Propertytype->find('first', ['conditions'=>['Propertytype.code'=>$code]]);

            if(!empty($propertytype))
            {
                $propertyid=$propertytype['Propertytype']['id'];

            } else {
                $this->Propertytype->create();
                $newpropertytype=['Propertytype'=>['code'=>$code]];
                $this->Propertytype->save($newpropertytype);
                $propertyid=$this->Propertytype->id;

            }
            $uploadedFile['propertytype_id']=$propertyid;
            $uploadedFile['format']=$this->File->format;
            if(!isset($code)||$code===false){
                if (!empty($this->request->params['requested'])) {
                    return "File Missing Property Code";
                }else {
                    $this->Session->setFlash('File Missing Property Code');
                    return $this->redirect('/Files/view/' . $this->File->id);
                }
            }
            // Add to database
            $uploadedFile['File']=$uploadedFile;
            $this->File->create();
            if(!$this->File->save($uploadedFile)){
                debug($this->File->validationErrors); die();
            }
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
            if (!empty($this->request->params['requested'])) {
                return true;
            }else {
                return $this->redirect('/Files/view/' . $this->File->id);
            }

        } else {
            $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
            $this->set('pubs',$pubs);
        }
    }

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
     */
    public function view($id)
    {
        $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
        $this->set('data',$data);
    }

    /**
     * Update a file
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

    public function totalfiles()
    {
        $data=$this->File->find('count');
        return $data;
    }

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