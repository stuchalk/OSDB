<?php

/**
 * Class TechniquesController
 */
class TechniquesController extends AppController
{
    public $uses=['Technique','Report'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a particular technique
     * Endpoint /osdb.info/techniques/view/{id}[/format]
     * @param mixed $id
     * @param string $format
     */
    public function view($id,$format="")
    {
        // Defaults for variables
        $error = "";$json=[];

        // Search for collection by name if id is not a number
        if(!is_numeric($id)) {
            // Get the report id if one exists for this chemical and technique
            $error = "";
            // Get the collection id if there is one
            $techs = $this->Technique->find('list', ['fields' => ['matchstr'], 'recursive' => -1]);
            if(in_array($id,$techs)) {
                $data = $this->Technique->find('first', ['conditions' => ['matchstr' => $id], 'recursive' => -1]);
            } else {
                $data = $this->Technique->find('first', ['conditions' => ['title like ' => '%'.$id.'%'], 'recursive' => -1]);
            }
            if(empty($data)) {
                $error='Technique not found (' . $id . ')';
            } else {
                $id = $data['Technique']['id'];
            }
        }
        if($error!="") {
            if($format=="") {
                exit($error);
            } elseif($format=="XML") {
                $this->Export->xml('osdb','technique',['error'=>$error]);
            } elseif($format=="JSON") {
                $this->Export->json('osdb','technique',['error'=>$error]);
            }
        }

        // Get the collection metadata and the substances/spectra in the collection
        $osdbpath=Configure::read('url');
        $data = $this->Technique->find('first', ['conditions' => ['Technique.id' => $id],'recursive'=>-1]);
        $subs=$this->Report->bySubstance('tech',$id);
        //debug($subs);exit;

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
            $this->Export->xml("osdb_collection_".$data['Technique']['name'],"technique",$json);
        } elseif($format=='JSON') {
            $this->Export->json("osdb_collection_".$data['Technique']['name'],"technique",$json);
        }
    }

    /**
     * List the techniques
     * Endpoint /osdb.info/techniques[/index]
     */
    public function index()
    {
        $data=$this->Technique->find('list',['fields'=>['id','type'],'order'=>['type']]);
        $this->set('data',$data);
    }

}