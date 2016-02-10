<?php
/**
 * Created by PhpStorm.
 * User: n00002621
 * Date: 5/28/15
 * Time: 9:59 AM
 */

/**
 * Class SystemsController
 */
class SystemsController extends AppController {

    public $uses=['System','SubstancesSystem'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a system
     * @param $id
     * @param $format
     */
    public function view($id,$format="")
    {
        $contain=['Substance'=>['fields'=>['name'],
                        'Identifier'=>['fields'=>['type','value']]],
                    'Context'=>['fields'=>['id'],
                        'Dataset'=>['fields'=>['id'],
                            'Propertytype','Report']]];
        $data=$this->System->find('first',['conditions'=>['System.id'=>$id],'contain'=>$contain,'recursive'=> 3]);
        //debug($data);exit;
        $osdbpath=Configure::read('url');
        if($format=="XML"||$format=="JSON") {
            $json['uid']="osdb:system:".$id;
            $json+=$data['System'];
            // Substances
            $subs=[];
            foreach($data['Substance'] as $sub) {
                $subs[]=['name'=>$sub['name'],'url'=>$osdbpath.'/substances/view/'.$sub['id'].'/'.$format];
            }
            // Spectra
            $specs=[];
            foreach($data['Context'] as $context) {
                $rpt=$context['Dataset']['Report'];
                $specs[]=['title'=>$rpt['title'],'url'=>$osdbpath.'/spectra/view/'.$rpt['id'].'/'.$format];
            }
            $json['substances']=$subs;
            $json['spectra']=$specs;
            $json['accessed']=date(DATE_ATOM);
            $json['url']=$osdbpath.'/systems/view/'.$id.'/'.$format;
            $json['website']=$osdbpath;
            if($format=="XML") {
                $this->Export->xml("osdb_system_".$data['System']['id'],"system",$json);
            } elseif($format=='JSON') {
                $this->Export->json("osdb_system_".$data['System']['id'],"system",$json);
            }
        }
        $this->set('data',$data);
    }

    /**
     * View list of systems
     * @param $format
     */
    public function index($format="")
    {
        $data=$this->System->find('all',['order'=>['name']]);
        $type='systems';
        if($format=="") {
            $this->set('data',$data);
        } else {
            $osdbpath=Configure::read('url');
            $out['substance']=[];$title="osdb_substance_list";
            foreach($data as $id=>$name) {
                $c['name']=$name;
                $c['url']=$osdbpath.'/'.$type.'/view/'.$id;
                $out['system'][]=$c;
            }
            $out['accessed']=date(DATE_ATOM);
            $out['url']=$osdbpath.'/'.$type;
            if($format=="XML") {
                $this->Export->xml($title,$type,$out);
            } elseif($format=='JSON') {
                $this->Export->json($title,$type,$out);
            }
        }
    }

    /**
     * Testing function
     */
    public function findsys()
    {
        $sid1="00001";$sid2="";
        debug($sid1);debug($sid2);
        $sarray=[$sid1];
        if($sid2!="") { $sarray[]=$sid2; }
        $data=$this->SubstancesSystem->findUnique($sarray);
        debug($data);exit;
    }

}