<?php

/**
 * Class CoreController
 */
class FilesController extends AnimlAppController {

    public $uses=['Animl.File','Animl.Core','Animl.Animl','Exptml','Service','Datastream'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * function index
     * Finds all the ANIML streams in Fedora
     */
    public function index()
    {
        // Get data
        $filter="FILTER (regex(str(?stream),'ANIML','i') && regex(str(?pid),'exptml','i'))";
        $query="select * from <#ri> where { ?pid <fedora-view:disseminates> ?stream. ?pid <dc:title> ?title. ".$filter." } ORDER BY ?title";
        $files=$this->Service->risearch($query);

        // Get information about the spectra and samples this file contains
        for ($x=0;$x<count($files);$x++) {
            $files[$x]['samples']=$this->File->getSamples($files[$x]['pid']);
            $files[$x]['results']=$this->File->getResults($files[$x]['pid']);
        }
        //echo "<pre>";print_r($files);echo "</pre>";exit;

        $this->set('files',$files);
    }

    /**
     * View ANIML stream on object
     * @param $pid
     */
    public function view($pid)
    {
        //$data=$this->Datastream->content($pid,'ANIML',[],'array');
        $meta=$this->File->getMeta($pid);

        $data=$this->File->getData($pid);

        $samples=$this->File->getSamples($pid);

        $results=$this->File->getResults($pid);

        $this->set('meta',$meta);
        $this->set('data',$data);
        $this->set('samples',$samples);
        $this->set('results',$results);
        //echo "<pre>";print_r($data);echo "</pre>";exit;
    }

    /**
     * Create a SVG from an AnIML file
     * @param $pid
     * @param $step
     * @param $result
     */
    public function svg($pid,$step='step1',$result='Spectrum')
    {
        $data=$this->Service->saxon($pid."*ANIML","animl:xsl1*XSLT",['step'=>$step,'result'=>$result],'browser');
        echo "<pre>";print_r($data);echo "</pre>";exit;
    }

    public function flot($pid="exptml:dat13")
    {
        $data=$this->Datastream->content($pid,'JCAMPXML',[],'array');
        $this->set('data',$data);
    }

    public function xpath($pid,$xpath)
    {
        $data=$this->File->xpath($pid,$xpath);
        $this->set('data',$data);
    }

}