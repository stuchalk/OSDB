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
                $this->Session->setFlash('Substance created.');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('Substance could not be created.');
            }
        } else {
            // Nothing to do here?
        }
    }

    /**
     * View a substance
     */
    public function view($id)
    {
        $data=$this->Substance->find('first',['conditions'=>['Substance.id'=>$id],'recursive'=>4]);
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
     * View a list of substances
     */
    public function index()
    {
        $data=$this->Substance->find('list',['fields'=>['id','name','first'],'order'=>['first','name']]);
        $this->set('data',$data);
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