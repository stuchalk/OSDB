<?php

/**
 * Class ChemicalsController for the PubChem Plugin
 */
class ChemicalsController extends AppController
{

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Get names of a chemical from PubChem
     * @param $name
     */
    public function cid($name)
    {
        $data=$this->Chemical->cid($name);
        $this->set('data',$data);
        $this->render('display');
    }

    /**
     * Get the property of a chemical from PubChem
     * @param $prop
     * @param $term
     */
    public function property($prop,$term)
    {
        $data=$this->Chemical->property($prop,$term);
        $this->set('data',$data);
        $this->render('display');
    }

    /**
     * Check Pubchem for a chemical
     * @param $name
     * @param $cas
     */
    public function check($name,$cas="")
    {
        $data=$this->Chemical->check($name,$cas);
        $this->set('data',$data);
        $this->render('display');
    }
}