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
        $this->Auth->allow('index','view');
    }

    /**
     * View a list of the collections (all or for a user ($uid))
     * @param integer $uid
     */
    public function index($uid=null,$format="")
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
        if($this->request->is('ajax')&&!empty($this->request->data)) {
            $new=$this->Collection->add($this->request->data['Collection']);
            if(isset($new['id'])) {
                echo '{ "id": "'.$new['id'].'", "name": "'.$new['name'].'" }'; exit;
            } else {
                echo "failure"; exit;
            }
        } elseif($this->request->is('post')) {
            $this->Collection->add($this->request->data['Collection']);
            $this->redirect('/users/dashboard');
        } else {
            //debug($this->request);exit;
            $data = $this->Collection->find('list',['fields'=>['name'],'order'=>['name']]);
            $this->set('data',$data);
            $this->set('ajax',false);
            if($this->request->is('ajax')||isset($this->request->params['requested'])) {
                $this->set('ajax',true);
                $this->layout='ajax';
            }}
    }

    /**
     * View a particular collection
     * @param integer $id
     * @param string $format
     */
    public function view($id,$format="")
    {
        // Search for collection by name if id is not a number
        $error = "";
        if(!is_numeric($id)) {
            // Get the collection id if there is one
            $data = $this->Collection->find('first', ['conditions' => ['name' => $id], 'recursive' => -1]);
            if (empty($data)) {
                $error='Collection not found (' . $id . ')';
            } else {
                $id = $data['Collection']['id'];
            }
        }
        
        // Show error if you cant find the collection
        if($error!="") {
            header("Content-Type: application/json");
            echo '{ "error": "'.$error.'" }';
            exit;
        }

        // Get the collection metadata and the substances/spectra in the collection
        $c=['User'];$osdbpath=Configure::read('url');
        $data = $this->Collection->find('first', ['conditions' => ['Collection.id' => $id],'contain'=>$c,'recursive'=>-1]);
        $subs=$this->Report->bySubstance('col',$id);

        // Format data for output
        $json=[];
        if($format==""||strtolower($format)=="html") {
            $this->set('data',$data);
            $this->set('subs',$subs);
        } elseif(strtolower($format)=="xml"||strtolower($format)=="json") {
            $col=$data['Collection'];
            unset($col['user_id']);unset($col['first']);unset($col['id']);
            $json=['id'=>$id,'uid'=>"osdb:collection:".$id] + $col;
            $json['compounds']=[];
            foreach($subs as $name=>$meta) {
                $comp=[];
                $comp['name']=$name;
                $comp['url']=$osdbpath.'/compounds/view/'.$meta['id'];
                $comp['spectra']=[];
                foreach($meta['spectra'] as $tid=>$tech) {
                    $comp['spectra'][]=['technique'=>$tech,'url'=>$osdbpath.'/spectra/view/'.$tid];
                }
                $json['compounds'][]=$comp;
            }
            $json=["site"=>$osdbpath,"accessed"=>date(DATE_ATOM),"url"=>$osdbpath.'/collections/view/'.$id,"count"=>1,'collection'=>$json];
            if(strtolower($format)=="xml") {
                $this->Export->xml("osdb_collection_".$data['Collection']['name'],"collection",$json);
            } elseif(strtolower($format)=='json') {
                $this->Export->json("osdb_collection_".$data['Collection']['name'],"collection",$json);
            }
        } else {
            header("Content-Type: application/json");
            echo '{ "error": "Invalid request (\''.$format.'\' is not an acceptable value)" }';
            exit;
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