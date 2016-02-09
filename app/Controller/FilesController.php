<?php

/**
 * Class FilesController
 * This is aliased as 'spectra' for the access
 */
class FilesController extends AppController {

    public $uses = ['File','Collection','Publication','Identifier','Report'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index','totalfiles','upload','add','view');
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
     * Add a file
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            $data=$this->request->data;
            // Data has array of arrays for file to accomodate multiple file uploads
            $files=$data['File']['file'];
            foreach($files as $file) {
                if($data['File']['substance_id']=='') {
                    $chm=$data['File']['substance'];
                    $sid=$this->File->getChem($chm); // Adds substance if it does not exist
                    $data['File']['substance_id']=$sid;
                }
                $data['File']['file']=$file;
                //debug($data);exit;
                $id=$this->File->convert($data);
            }
            $rep=$this->Report->find('first',['conditions'=>['Report.id'=>$id]]);
            if($rep['Report']['technique_id']=='03') {
                $this->Identifier->getSplashId($id);
            }
            // Redirect to view
            $this->redirect('/spectra/view/' . $id);
        } else {
            $this->upload();
        }
    }

    /**
     * Upload a file
     */
    public function upload()
    {
        $cols=$this->Collection->find('list',['fields'=>['id','name']]);
        $this->set('cols',$cols);
        $this->render('add');
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
            $this->File->id=$id;
            $this->File->save($this->request->data);
            $this->redirect(['action' => 'index']);
        } else {
            $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
            $this->set('data',$data);
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
        $this->redirect(['action' => 'index']);
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
     * Check the upload of chemical metadata (for testing/debugging)
     * @param $chem
     * @param $debug
     */
    public function check($chem,$debug)
    {
        $this->File->getchem($chem,$debug);
    }

}