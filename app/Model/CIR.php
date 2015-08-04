<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
class CIR extends AppModel {

    public $useTable=false;

    public function search($type="name",$id)
    {
        $HttpSocket = new HttpSocket();
        if($type=="name"||$type=="cas")
        {
            $url="http://cactus.nci.nih.gov/chemical/structure/".rawurlencode($id)."/names/xml";
            $xmlfile =$HttpSocket->get($url);
            $xml = simplexml_load_string($xmlfile,'SimpleXMLElement',LIBXML_NOERROR|LIBXML_NOENT);
            $output=json_decode(json_encode($xml),true);
            return $output['data']['item'];
            //echo "<pre>";print_r($output);echo "</pre>";exit;

        }
    }

}

?>