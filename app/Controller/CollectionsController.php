<?php

/**
 * Class CollectionsController
 */
class CollectionsController extends AppController
{

    public $uses = ['Collection','Report'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('check','index','view');
    }

    /**
     * View a list of the collections (all or for a user ($uid))
     * @param integer $uid
     */
    public function index($uid=null)
    {
        $count=0;
        if(is_null($uid)) {
            $count=$this->Collection->find('count');
        } else {
            $count=$this->Collection->find('count',['conditions'=>['user_id'=>$uid]]);
        }
        $cutoff=Configure::read('index.display.cutoff');
        if($count>$cutoff) {
            if(is_null($uid)) {
                $data = $this->Collection->find('list',['fields'=>['id','name','first'],'order'=>['first','name']]);
            } else {
                $data = $this->Collection->find('list',['fields'=>['id','name','first'],'order'=>['first','name'],'conditions'=>['user_id'=>$uid]]);
            }
        } else {
            if(is_null($uid)) {
                $data = $this->Collection->find('list',['fields'=>['id','name'],'order'=>['first','name']]);
            } else {
                $data = $this->Collection->find('list',['fields'=>['id','name'],'order'=>['first','name'],'conditions'=>['user_id'=>$uid]]);
            }
        }
        $this->set('count',$count);
        $this->set('data', $data);
    }

    /**
     * Add a collection
     */
    public function add()
    {
        if($this->request->is('post')) {
            $this->Collection->add($this->request->data['Collection']);
            $this->redirect('/users/dashboard');
        } else {
            $data = $this->Collection->find('list',['fields'=>['name'],'order'=>['name']]);
            $this->set('data',$data);
        }
    }

    /**
     * View a particular collection
     * @param integer $id
     * @param string $format
     */
    public function view($id,$format="")
    {
        // Defualts for variables
        $error = "";$json=[];

        // Search for collection by name if id is not a number
        if(!is_numeric($id)) {
            // Get the report id is one exists for this chemical and technique
            $error = "";
            // Get the collection id if there is one
            $data = $this->Collection->find('first', ['conditions' => ['name' => $id], 'recursive' => -1]);
            if (empty($data)) {
                $error='Collection not found (' . $id . ')';
            } else {
                $id = $data['Collection']['id'];
            }
        }
        if($error!="") {
            if($format=="") {
                exit($error);
            } elseif($format=="XML") {
                $this->Export->xml('osdb','collection',['error'=>$error]);
            } elseif($format=="JSON") {
                $this->Export->json('osdb','collection',['error'=>$error]);
            }
        }

        // Get the collection metadata and the substances/spectra in the collection
        $c=['User'];$osdbpath=Configure::read('url');
        $data = $this->Collection->find('first', ['conditions' => ['Collection.id' => $id],'contain'=>$c,'recursive'=>-1]);
        $subs=$this->Report->bySubstance('col',$id);

        // Format data for output
        if($format=="") {
            $this->set('data',$data);
            $this->set('subs',$subs);
        } else {
            $json=[];
            $json['collection']=$data['Collection'];
            unset($json['collection']['user_id']);unset($json['collection']['first']);
            foreach($subs as $name=>$meta) {
                $comp=[];
                $comp['name']=$name;
                $comp['inchikey']=$meta['inchikey'];
                $comp['spectra']=[];
                foreach($meta['spectra'] as $tid=>$tech) {
                    $comp['spectra'][]=['technique'=>$tech,'url'=>$osdbpath.'/spectra/view/'.$tid];
                }
                $comp['url']=$osdbpath.'/substance/view/'.$meta['id'];
                $json['substances'][]=$comp;
            }
            $json['accessed']=date(DATE_ATOM);
            $json['url']=$osdbpath.'/collections/view/'.$id;
        }
        if($format=="XML") {
            $this->Export->xml("osdb_collection_".$data['Collection']['name'],"collection",$json);
        } elseif($format=='JSON') {
            $this->Export->json("osdb_collection_".$data['Collection']['name'],"collection",$json);
        }
    }

    /**
     * Check the value of field (jQuery)
     * @param string $field
     * @param string $value
     */
    public function check($field="",$value="")
    {
        $c = $this->Collection->find('count',['fields'=>[$field],'conditions'=>[$field=>$value]]);
        echo $c;exit;
    }
}