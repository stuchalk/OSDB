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
        $this->Auth->allow('index','view','test','checksplashes','addpng');
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
     * Add a png image of molecule
     * @param $id
     * @param $down
     * @return array
     */
    public function addpng($id,$down="no")
    {
        $key=$this->Identifier->find("list",['conditions'=>['substance_id'=>$id,'type'=>'inchikey'],'fields'=>['substance_id','value']]);
        $smi=$this->Identifier->find("list",['conditions'=>['substance_id'=>$id,'type'=>'smiles'],'fields'=>['substance_id','value']]);
        //debug($key);debug($smi);
        $path=WWW_ROOT.'img'.DS.'mol'.DS.$key[$id].'.png';
        //debug($path);
        //exit;
        if(!file_exists($path)) {
            //echo "In if statement<br />";
            exec('/opt/local/bin/obabel -:"'.$smi[$id].'" -O '.$path,$response,$status);
            //debug($response);debug($status);
            if(!file_exists($path)) {
                return false;
            }
        }
        if($down=="yes") {
            // Download added so that http://osdb.info can get png file without loading openbabel in server
            header("Content-Type: image/png");
            header('Content-Disposition: attachment; filename="'.$key[$id].'.png"');
            readfile($path);exit;
        } else {
            return true;
        }
        exit;
    }

    /**
     * Add Wikidata code to substance
     * @param $sid
     */
    public function wikidata($sid=null)
    {
        if(!is_null($sid)) {
            $resp=$this->Identifier->find('first',['fields'=>['id','value'],'conditions'=>['substance_id'=>$sid,'type'=>'inchikey']]);
            $key=$resp['Identifier']['value'];
            $data=$this->Identifier->getWikidataId($sid,'inchikey',$key);
            if(!$data) {
                $resp=$this->Identifier->find('first',['fields'=>['id','value'],'conditions'=>['substance_id'=>$sid,'type'=>'smiles']]);
                if(isset($resp['Identifier']['value'])) {
                    $key=$resp['Identifier']['value'];
                    $data=$this->Identifier->getWikidataId($sid,'smiles',$key);
                }
            }
            if(!$data) {
                $resp=$this->Identifier->find('first',['fields'=>['id','value'],'conditions'=>['substance_id'=>$sid,'type'=>'pubchemid']]);
                if(isset($resp['Identifier']['value'])) {
                    $key=$resp['Identifier']['value'];
                    $data=$this->Identifier->getWikidataId($sid,'pubchemid',$key);
                }
            }
        }
        $this->redirect('/substances/view/'.$sid);

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

    /**
     * Check database splashes against current specification and update if needed
     */
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

    /**
     * Add the chebi identifier for a compound
     * @param $cid
     */
    public function addchebi($cid) {
        $this->Identifier->getChebi($cid);
        $this->redirect('/substances/view/'.$cid);
    }
}