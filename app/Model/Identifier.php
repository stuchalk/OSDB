<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Identifier
 * Identifier model
 * Identifiers are metadata that identify substances (specifically)
 */
class Identifier extends AppModel {

    public $belongsTo=['Substance'];

    /**
     * General function to add a new identifier
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Identifier';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

    public function getWikidataId($sid,$type,$value)
    {
        // Uses Wikidata SPARQL REST call to get wikidata code via InChIKey (P235), CAS (P231), or CID (P662) search
        if($type=='inchikey') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P235 \"".$value."\"}";
        } elseif($type=='casrn') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P231 \"".$value."\"}";
        } elseif($type=='pubchemid') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P662 \"".$value."\"}";
        }
        $url="https://query.wikidata.org/sparql?query=".urlencode($sparql)."&format=json";
        $json=file_get_contents($url);
        $data=json_decode($json,true);
        //debug($data);
        if(!empty($data['results']['bindings'][0]['c'])) {
            $wid=str_replace("http://www.wikidata.org/entity/","",$data['results']['bindings'][0]['c']['value']);
            $resp=$this->add(['substance_id'=>$sid,'type'=>'wikidata','value'=>$wid]);
        } else {
            $resp=false;
        }
        return $resp;
    }
}