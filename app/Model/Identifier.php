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

    public function getWikidataId($sid,$name)
    {
        $url="https://www.wikidata.org/w/api.php?action=wbsearchentities&search=".urlencode($name)."&language=en&format=json";
        $json=file_get_contents($url);
        $data=json_decode($json,true);
        if(!empty($data['search'])) {
            $wid=$data['search'][0]['id'];
            $resp=$this->add(['substance_id'=>$sid,'type'=>'wikidata','value'=>$wid]);
        } else {
            $resp=false;
        }
        return $resp;
    }
}