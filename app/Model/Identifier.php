<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
App::uses('HttpSocket', 'Network/Http');

/**
 * Class Identifier
 * Identifier model
 * Identifiers are metadata that identify substances (specifically)
 */
class Identifier extends AppModel
{

    public $belongsTo = ['Substance'];

    /**
     * General function to add a new identifier
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model = 'Identifier';
        $this->create();
        $ret = $this->save([$model => $data]);
        $this->clear();
        return $ret[$model];
    }

    /**
     * Get Wikidata ID for compound
     * @param $sid
     * @param $type
     * @param $value
     * @return bool|int
     */
    public function getWikidataId($sid, $type, $value)
    {
        // Uses Wikidata SPARQL REST call to get wikidata code via InChIKey (P235), CAS (P231), or CID (P662) search
        if ($type=='inchikey') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P235 \"".$value."\"}";
        } elseif ($type=='casrn') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P231 \"".$value."\"}";
        } elseif ($type=='pubchemid') {
            $sparql="PREFIX wdt: <http://www.wikidata.org/prop/direct/> select ?c where { ?c wdt:P662 \"".$value."\"}";
        }
        $url = "https://query.wikidata.org/sparql?query=".urlencode($sparql)."&format=json";
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        if (!empty($data['results']['bindings'][0]['c'])) {
            $wid = str_replace("http://www.wikidata.org/entity/", "", $data['results']['bindings'][0]['c']['value']);
            $resp = $this->add(['substance_id' => $sid, 'type' => 'wikidata', 'value' => $wid]);
        } else {
            $resp = false;
        }
        return $resp;
    }

    /**
     * Get splash id for spectra (MS only currently)
     * @param $rid
     * @return boolean
     */
    public function getSplashId($rid = null)
    {
        $Rep = ClassRegistry::init('Report');

        $c = ['Dataset' => ['fields' => ['setType', 'property', 'kind'],
            'Dataseries' => ['fields' => ['type', 'format', 'level', 'processedType'],
                'Datapoint' => [
                    'Data' => ['fields' => ['datatype', 'text', 'number', 'title', 'id'],
                        'Property' => ['fields' => ['name']],
                        'Unit' => ['fields' => ['name', 'symbol']]],
                    'Condition' => ['fields' => ['datatype', 'text', 'number', 'title', 'id'],
                        'Property' => ['fields' => ['name']],
                        'Unit' => ['fields' => ['name', 'symbol']]]]]]];
        $data = $Rep->find('first',['conditions'=>['Report.id'=>$rid],'contain'=>$c,'recursive'=>-1]);
        // What type of data is it? choices are MS, IR, UV, NMR, RAMAN
        $type = $data['Dataset']['property'];
        // Where is the spectral data?
        $spectrum = $data['Dataset']['Dataseries'][0]['Datapoint'][0];
        // Put together data packet
        $xdata=json_decode($spectrum['Condition'][0]['number'],true);
        $ydata=json_decode($spectrum['Data'][0]['number'],true);
        if($type=="Mass Spectrometry") { // MS
            $sarray=['ions'=>[],'type'=>'MS'];
            for($i=0;$i<count($xdata);$i++) {
                $sarray['ions'][]=['mass'=>$xdata[$i],'intensity'=>$ydata[$i]];
            }
        } elseif($type=="Nuclear Magnetic Resonance") { // NMR
            $sarray=['ions'=>[],'type'=>'NMR'];
            for($i=0;$i<count($xdata);$i++) {
                $sarray['ions'][]=['mass'=>$xdata[$i],'intensity'=>$ydata[$i]];
            }
        }
        $json=json_encode($sarray);
        $http = new HttpSocket();
        $path='http://splash.fiehnlab.ucdavis.edu/splash/it';
        $response=$http->post($path,'',['body'=>$json,'header'=>['Content-Type'=>'application/json']]);
        $splash=$response->body;
        // Get splash from response
        if (stristr($splash, '{')) {
            $data = json_decode($splash, true);
        } else {
            $data = json_decode('["'.$splash.'"]', true);
        }
        if(!isset($data['error'])) {
            $Rep->id = $rid;
            $Rep->saveField('splash', $data[0]);
            $Rep->clear();
            $this->log('splash','Retrieved splash ('.$data[0].') on report '.$rid);
            return $data[0];
        } else {
            $this->log('splash','Error trying to get splash: '.$data['error']);
            return false;
        }
    }

    
    
    public function getchebi($id) {
        $cid=$this->find('first',['conditions'=>['type'=>'pubchemid','substance_id'=>$id]]);
        debug($cid);exit;
        $url="https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/10214376/synonyms/JSON";

    }
}