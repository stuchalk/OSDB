<?php

/**
 * Class IdentifiersController
 * Actions related to dealing with identifiers
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class IdentifiersController extends AppController
{

    public $uses=['Identifier','Report'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index','view','test','checksplashes');
    }

    /**
     * List the quantities
     */
    public function index()
    {
        $data=$this->Identifier->find('list',['fields'=>['Parameter.id','base'],'order'=>['base']]);
        $this->set('data',$data);
    }

    /**
     * View a quantity
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Identifier->find('first',['conditions'=>['Identifier.id'=>$id],'recursive'=>1]);
        //echo "<pre>";print_r($data);echo "</pre>";exit;
        $this->set('data',$data);
    }

    /**
     * Search for a substance using one of its identifiers
     * @param $term
     */
    public function search($term)
    {
        $data=$this->Identifier->find('all',['fields'=>['DISTINCT Identifier.substance_id','Substance.name'],'order'=>['Substance.name'],'conditions'=>['Identifier.value like'=>'%'.$term.'%'],'recursive'=>1]);
        $this->set('data',$data);
        $this->render('view');
    }

    /**
     * Add Wikidata code to substance
     * @param $name
     */
    public function wikidata($name)
    {
        $sub=$this->Identifier->find('first',['fields'=>['id','substance_id'],'conditions'=>['value'=>$name]]);
        if(!empty($sub)) {
            $sid=$sub['Identifier']['substance_id'];
            $resp=$this->Identifier->find('first',['fields'=>['id','value'],'conditions'=>['substance_id'=>$sid,'type'=>'inchikey']]);
            $key=$resp['Identifier']['value'];
            debug($sub);debug($key);
            $data=$this->Identifier->getWikidataId($sub['Identifier']['substance_id'],'inchikey',$key);
            $this->redirect('/substances/view/'.$sid);
        } else {
            exit;
        }
    }

    /**
     * Add splash id to a report
     * @param integer $id
     */
    public function addsplash($id)
    {
        $this->Identifier->getSplashId($id);
        $this->redirect('/reports/view/'.$id);
    }

    public function checksplashes()
    {
        // Get current SPLASH
        $reps=$this->Report->find('all',['fields'=>['id','title','splash','technique_id']]);
        $data=[];
        foreach($reps as $rep) {
            $r=$rep['Report'];
            //debug($r);
            if($r['technique_id']!=3) { continue; }
            $id=$r['id'];
            $d=['id'=>$id,'title'=>$r['title'],'old'=>$r['splash']];
            if(is_null($r['splash'])) {
                // If new spectrum with no SPLASH add it...
                $n=$this->Identifier->getSplashId($id);
                $this->Report->id=$id;
                $this->Report->saveField('splash',$n);
                $this->Report->clear();
                $d['new']=$n;$d['action']='added';
            } else {
                $c=$r['splash'];
                $n=$this->Identifier->getSplashId($id);
                $d['new']=$n;
                if($c!=$n) {
                    $this->Report->id=$id;
                    $this->Report->saveField('splash',$n);
                    $this->Report->clear();
                    $d['action']='update';
                } else {
                    $d['action']='none';
                }
            }
        }
        debug($data);exit;
    }

    public function addchebi($cid) {
        $this->Identifier->getChebi($cid);
        $this->redirect('/substances/view/'.$cid);
    }
}