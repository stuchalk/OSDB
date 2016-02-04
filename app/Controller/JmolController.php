<?php

/**
 * Class JmolController
 */
class JmolController extends AppController
{

    public $uses = false;

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Proxy server for http accessible mol files
     * Format https://sds.coas.unf.edu/osdb/jmol/proxy?url=<URL>
     */
    public function proxy()
    {
        $url=$this->request->query['url'];
        $h=get_headers($url,true);
        header('Content-Type: '.$h['Content-Type']);
        $f=file_get_contents($url);
        echo $f;
        exit;
    }
}