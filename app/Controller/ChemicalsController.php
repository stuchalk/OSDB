<?php

/**
 * Class ChemicalsController
 */
class ChemicalsController extends AppController {

	public $uses=array('Chemical','Crossref','Pubchem.Chemical','CIR');

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('view');
    }

    /**
     * View a list of the chemicals
     */
	public function index()
	{
		$data=$this->Chemical->find('list', ['fields'=>['id','name','first'],'order'=>['first','name']]);
		$this->set('data',$data);
	}

	/**
	 * View a particular chemical
	 * @param $id
	 */
	public function view($id)
	{
		$data=$this->Chemical->find('first',['conditions'=>['id'=>$id]]);
        $this->set('data',$data);
	}

	/**
	 * function lookup
	 */
	public function lookup()
	{
		$conds=array('inchistr'=>'NA');
		$refs=$this->Chemical->find('list',array('fields'=>array('id','cas'),'conditions'=>$conds));
		foreach($refs as $id=>$cas)
		{
			echo "CAS: ".$cas."<br />";
			$cid=$this->Pubchem->getcid('cas',$cas);
			if($cid=="") { echo "Not found by CAS#<br />";continue; }
			$data=$this->Pubchem->data($cid,'InChI,InChiKey');
			$this->Chemical->save(array('Chemical'=>array('id'=>$id,'inchistr'=>$data['InChI'],'inchikey'=>$data['InChIKey'],'pubchem_id'=>$data['CID'])));
			echo "<pre>";print_r($data);echo "</pre>";
		}
		$refs=$this->Chemical->find('list',array('fields'=>array('id','name'),'conditions'=>$conds));
		foreach($refs as $id=>$name)
		{
			echo "Name: ".$name."<br />";
			$cid=$this->Pubchem->getcid('cas',$name);
			if($cid=="") { echo "Not found by name<br />";continue; }
			$data=$this->Pubchem->data($cid,'InChI,InChiKey');
			$this->Chemical->save(array('Chemical'=>array('id'=>$id,'inchistr'=>$data['InChI'],'inchikey'=>$data['InChIKey'],'pubchem_id'=>$data['CID'])));
			echo "<pre>";print_r($data);echo "</pre>";
		}
		$refs=$this->Chemical->find('list',array('fields'=>array('id','formula'),'conditions'=>$conds));
		// Make requests first
		$listkeys=array();
		foreach($refs as $id=>$formula)
		{
			echo "Formula: ".$formula."<br />";
			$listkey=$this->Pubchem->getcid('formula',$formula);
			$listkeys[$id]=$listkey;
			echo "ListKey: ".$listkey."<br />";
		}
		echo "<pre>";print_r($listkeys);echo "</pre>";
		sleep(15); // Wait to process results
		// Retrieve results
		foreach($listkeys as $id=>$listkey)
		{
			echo "Formula: ".$formula."<br />";
			$cids=$this->Pubchem->getcid('formula',$id,$listkey); // $id passed as dummy variable (not used in getcid with listkey)
			if(!is_array($cids)&&$cids=="")
			{
				echo "Not found by formula<br />";
			}
			elseif(isset($cids['IdentifierList']))
			{
				if(count($cids['IdentifierList']['CID'])==1)
				{
					$data=$this->Pubchem->data($cids['IdentifierList']['CID'][0],'InChI,InChiKey');
					$this->Chemical->save(array('Chemical'=>array('id'=>$id,'inchistr'=>$data['InChI'],'inchikey'=>$data['InChIKey'],'pubchem_id'=>$data['CID'])));
					echo "<pre>";print_r($data);echo "</pre>";
				}
				else
				{
					echo "Found multiple CIDs - ".json_encode($cids['IdentifierList']['CID'])."<br />";
				}
			}
			else
			{
				echo "Unexpected response<br />";
				echo "<pre>";print_r($cids);echo "</pre>";
			}
		}
		exit;
	}

    public function test()
    {
        $data=$this->Chemical->name('aspirin');
        //$data=$this->CIR->search('name','aspirin');
        echo "<pre>";print_r($data);echo "</pre>";exit;
    }
}

?>
