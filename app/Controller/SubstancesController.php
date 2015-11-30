<?php

/**
 * Class SubstancesController
 */
class SubstancesController extends AppController
{
    public $uses=['Substance','Identifier','Pubchem.Chemical','Chemspider.Rdf'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a list of substances
     * @param string $format
     */
    public function index($format="")
    {
        $count=$this->Substance->find('count');
        $cutoff=Configure::read('index.display.cutoff');
        if($count>$cutoff) {
            // Chunk by first letter
            $data=$this->Substance->find('list',['fields'=>['id','name','first'],'order'=>['first','name']]);
        } else {
            $data=$this->Substance->find('list',['fields'=>['id','name'],'order'=>['first','name']]);
        }
        if($format=="") {
            $this->set('count',$count);
            $this->set('data',$data);
        } else {
            $osdbpath=Configure::read('url');
            $out['substance']=[];$title="osdb_substance_list";
            if($count>$cutoff) {
                foreach($data as $chunk) {
                    foreach($chunk as $id=>$name) {
                        $c['name']=$name;
                        $c['url']=$osdbpath.'/substances/view/'.$id;
                        $out['substance'][]=$c;
                    }
                }
            } else {
                foreach($data as $id=>$name) {
                    $c['name']=$name;
                    $c['url']=$osdbpath.'/substances/view/'.$id;
                    $out['substance'][]=$c;
                }
            }
            $out['accessed']=date(DATE_ATOM);
            $out['url']=$osdbpath.'/substances';
            if($format=="XML") {
                $this->Export->xml($title,"substances",$out);
            } elseif($format=='JSON') {
                $this->Export->json($title,"substances",$out);
            }
        }
    }

    /**
     * Add a new substance
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            //echo "<pre>";print_r($this->request->data);echo "</pre>";exit;
            $this->Substance->create();
            if ($this->Substance->save($this->request->data))
            {
                $this->Flash->set('Substance created.');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->set('Substance could not be created.');
            }
        } else {
            // Nothing to do here?
        }
    }

    /**
     * View a substance
     * @param integer $id
     * @param string $format
     */
    public function view($id,$format="")
    {
        $error = "";
        if(!is_numeric($id)) {
            // Get the report id is one exists for this chemical and technique
            $sid = $error = "";
            // Get the sid if there is one
            $data = $this->Substance->find('first', ['conditions' => ['name' => $id], 'recursive' => -1]);
            if (empty($data)) {
                $data = $this->Identifier->find('first', ['conditions' => ['value' => $id], 'recursive' => -1]);
                if (empty($data)) {
                    $error='Compound not found (' . $id . ')';
                } else {
                    $id = $data['Identifier']['substance_id'];
                }
            } else {
                $id = $data['Substance']['id'];
            }
        }
        if($error!="") {
            if($format=="") {
                exit($error);
            } elseif($format=="XML") {
                $this->Export->xml('osdb','spectra',['error'=>$error]);
            } elseif($format=="JSON") {
                $this->Export->json('osdb','spectra',['error'=>$error]);
            }
        }

        $c=['Identifier'=>['fields'=>['id','type','value']],
            'System'=>['Context'=>['Dataset'=>['Report']]]];
        $data=$this->Substance->find('first',['conditions'=>['Substance.id'=>$id],'contain'=>$c,'recursive'=>-1]);
        $osdbpath=Configure::read('url');
        if($format=="XML"||$format=="JSON") {
            $json['uid']="osdb:substance:".$id;
            unset($data['Substance']['first']);unset($data['Substance']['updated']);
            $json+=$data['Substance'];
            $ids=[];
            foreach($data['Identifier'] as $i) {
                if($i['type']!='name') {
                    $ids[$i['type']]=$i['value'];
                }
            }
            $json['identifiers']=$ids;
            $syss=[];$specs=[];
            foreach($data['System'] as $sys) {
                $syss[]=['name'=>$sys['name'],'url'=>$osdbpath.'/systems/view/'.$sys['id']];
                foreach($sys['Context'] as $context) {
                    $rpt=$context['Dataset']['Report'];
                    $specs[]=['title'=>$rpt['title'],'url'=>$osdbpath.'/spectra/view/'.$rpt['id']];
                }
            }
            $json['systems']=$syss;
            $json['spectra']=$specs;
            $json['accessed']=date(DATE_ATOM);
            $json['url']=$osdbpath.'/substances/view/'.$id;
            $json['website']=$osdbpath;
            if($format=="XML") {
                $this->Export->xml("osdb_substance_".$data['Substance']['name'],"substance",$json);
            } elseif($format=='JSON') {
                $this->Export->json("osdb_substance_".$data['Substance']['name'],"substance",$json);
            }
        }
        $this->set('data',$data);
    }

    /**
     * Update a substance
     */
    public function update($id)
    {
        if($this->request->is('post'))
        {
            $this->Substance->create();
            if ($this->Substance->save($this->request->data))
            {
                $this->Session->setFlash('Substance udated.');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('Substance could not be updated.');
            }
        } else {
            $data=$this->Substance->find('first',['conditions'=>['Substance.id'=>$id],'recursive'=>3]);
            $this->set('data',$data);
            $this->set('id',$id);
        }
    }

    /**
     * Delete a substance
     */
    public function delete($id)
    {
        $this->Substance->delete($id);
        $this->redirect(['action' => 'index']);
    }

    /**
     * Search pubchem via plugin...
     * @param $name
     */
    public function pubchem($name)
    {
        $data=$this->Chemical->check($name);
        debug($data);exit;
    }

    /**
     * jQuery request with query variable
     */
    public function search()
    {
        $term=$this->request->query['term'];
        $temp=$this->Identifier->find('all',['fields'=>['DISTINCT Identifier.substance_id','Substance.name'],'order'=>['Substance.name'],'conditions'=>['Identifier.value like'=>'%'.$term.'%'],'recursive'=>1]);
        $data=[];
        for($x=0;$x<count($temp);$x++) {
            $data[$x]['id']=$temp[$x]['Identifier']['substance_id'];
            $data[$x]['value']=$temp[$x]['Substance']['name'];
        }
        echo json_encode($data);exit;
    }

    /**
     * Get meta for chemicals
     */
    public function meta()
    {
        $cs=$this->Substance->find('all',['recursive'=>1,'conditions'=>['id'=>1],'contain'=>['Identifier'=>['conditions'=>['type'=>'casrn']]]]);
        foreach($cs as $c) {
            $i=$c['Identifier'];$s=$c['Substance'];
            // Search PubChem
            $cid=$this->Chemical->cid('name',$i[0]['value']);
            if($cid) {
                // Add the PubChem ID
                $test=$this->Identifier->find('first',['conditions'=>['substance_id'=>$s['id'],'type'=>'pubchemId']]);
                if(empty($test)) {
                    $this->Identifier->add(['substance_id'=>$s['id'],'type'=>'pubchemId','value'=>$cid]);
                }
                echo "<h3>".$s['name']." (PubChem)</h3>";
                echo "<ul>";
                $ps=['pcformula'=>'MolecularFormula','iupacname'=>'IUPACName','inchi'=>'InChI','inchikey'=>'InChIKey','molweight'=>'MolecularWeight'];
                foreach($ps as $t=>$p) {
                    if($t=='pcformula'||$t=='molweight') {
                        // Check to see if the value is already in the DB
                        $test=$this->Substance->find('list',['fields'=>['id',$t],'conditions'=>['id'=>$s['id']]]);
                        if($test[$s['id']]=='') {
                            $meta=$this->Chemical->property($p,$cid);
                            if(isset($meta[$p])) {
                                echo "<li>".$p.": ".$meta[$p]."</li>";
                                $this->Substance->save(['id'=>$s['id'],$t=>$meta[$p]]);
                                $this->Substance->clear();
                            }
                        }
                    } else {
                        // Check to see if the value has already been added
                        $test=$this->Identifier->find('first',['conditions'=>['substance_id'=>$s['id'],'type'=>$t]]);
                        if(empty($test)) {
                            $meta=$this->Chemical->property($p,$cid);
                            if(isset($meta[$p])) {
                                echo "<li>".$p.": ".$meta[$p]."</li>";
                                $this->Identifier->add(['substance_id'=>$s['id'],'type'=>$t,'value'=>$meta[$p]]);
                            }
                        }
                    }
                }
                echo "</ul>";
            }
            // Search ChemSpider
            $meta=$this->Rdf->search($i[0]['value']);
            if($meta) {
                echo "<h3>".$s['name']." (ChemSpider)</h3>";
                echo "<ul>";
                $ps=['chemspiderId'=>'id','pcformula'=>'formula','iupacname'=>'name','smiles'=>'smiles','inchi'=>'inchi','inchikey'=>'inchikey'];
                foreach($ps as $t=>$p) {
                    if(isset($meta[$p])) {
                        echo "<li>".$p.": ".$meta[$p]."</li>";
                        if($t=='pcformula') {
                            $this->Substance->save(['id'=>$s['id'],$t=>$meta[$p]]);
                            $this->Substance->clear();
                        } else {
                            $test=$this->Identifier->find('first',['conditions'=>['substance_id'=>$s['id'],'type'=>$t]]);
                            if(empty($test)) {
                                $this->Identifier->add(['substance_id'=>$s['id'],'type'=>$t,'value'=>$meta[$p]]);
                            }
                        }
                    }
                }
                //debug($meta);
                echo "</ul>";
            }
            // Cleanup
            echo "<h3>Cleanup</h3>";
            echo "<ul>";
            $pcid=$this->Identifier->find('list',['fields'=>['substance_id','value'],'conditions'=>['substance_id'=>$s['id'],'type'=>'pubchemId']]);
            if(empty($pcid)) {
                $cid=$this->Chemical->cid('name',$meta['inchikey']);
                if($cid) {
                    $this->Identifier->add(['substance_id'=>$s['id'],'type'=>'pubchemId','value'=>$cid]);
                } else {
                    $cid='';
                }
            } else {
                $cid=$pcid[$s['id']];
            }
            echo "<li>CID: ".$cid."</li>";
            $mw=$this->Substance->find('list',['fields'=>['id','molweight'],'conditions'=>['id'=>$s['id']]]);
            if($mw[$s['id']]=='') {
                // Use inchikey from ChemSpider search to get molweight from PubChem
                $mw=$this->Chemical->property('MolecularWeight',$cid);
                if($mw) {
                    $this->Substance->save(['id'=>$s['id'],'molweight'=>$mw['MolecularWeight']]);
                    $this->Substance->clear();
                }
                echo "<li>MW: ".$mw['MolecularWeight']."</li>";
                //debug($mw);exit;
            }
            echo "</ul>";
        }
        exit;
    }

}