<?php

/**
 * Class FilesController
 * This is aliased as 'spectra' for access
 */
class FilesController extends AppController {

    public $uses = ['File','Collection','Identifier','Report','Technique'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index','totalfiles','upload','add','view','stats');
    }

    /**
     * List the files
     */
    public function index()
    {
        $data=$this->File->find('list',['fields'=>['id','filename'],'order'=>['id']]);
        $this->set('data',$data);
    }

    /**
     * Add a file
     * @param string $curl
     */
    public function add($curl="no")
    {
        if($this->request->is('post')) {
            $data=$this->request->data;
            if($curl=="yes") {
                debug($data);
            }
            if(empty($data['File']['file'][0]['name'])) {
                $this->Flash->error('No file uploaded');
                $this->redirect($this->referer());
            }
            // Data has array of arrays for file to accomodate multiple file uploads
            $files=$data['File']['file'];
            $url=$data['File']['url'];
            foreach($files as $file) {
                if($data['File']['substance_id']=='') {
                    $chm=$data['File']['substance'];
                    $sid=$this->File->getChem($chm); // Adds substance if it does not exist
                    $data['File']['substance_id']=$sid;
                }
                $data['File']['file']=$file;
                // Convert file
                $id=$this->File->convert($data);
                // Add URL if present
                if(!empty($url)) {
                    $this->Report->id=$id;
                    $this->Report->saveField('url',$url);
                }
                // Add splash
                $rep=$this->Report->find('first',['conditions'=>['Report.id'=>$id]]);
                if($rep['Report']['technique_id']=='03') {
                    $this->Identifier->getSplashId($id);
                }
            }
            // Redirect to view or echo out if using curl
            if($curl=="yes") {
                echo 'https://sds.coas.unf.edu/osdb/substances/view/'.$data['File']['substance_id'];exit;
            }
            $this->redirect('/substances/view/'.$data['File']['substance_id']);
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

    /**
     * generate stats on the types of spectra in the OSDB
     */
    public function stats()
    {
        $techs=$this->Technique->find('all');
        $stats=[];
        foreach($techs as $tech) {
            $t=$tech['Technique'];
            $stats[$t['type']]=$this->File->find('count',['conditions'=>['technique_id'=>$t['id']]]);
        }
        if(isset($this->request->params['requested'])) { return $stats; }
    }
}