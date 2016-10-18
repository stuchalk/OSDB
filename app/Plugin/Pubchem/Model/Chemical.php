<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Chemical - Chemical model
 */
class Chemical extends AppModel
{

    public $path="http://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/";
    public $useTable = false;

    /**
     * Get the PubChem CID for a chemical based on name/CAS search of names
     * You can use names, ids, cas# etc...
     * Format returned has CID and Synonyms in separate parts of array
     * @param $name
     * @param $debug
     * @return bool
     */
    public function cid($name,$debug=false)
    {
        if($debug) { echo "<b>function (cid)</b><br />"; }
        $HttpSocket = new HttpSocket();
        $url=$this->path.'name/'.rawurlencode($name).'/synonyms/JSON';
        if($debug) { echo $url."<br />"; }
        $json=$HttpSocket->get($url);
        $syns=json_decode($json['body'],true);
        if(isset($syns['Fault'])) {
            if($debug) { echo "An error occured: ".$syns['Fault']['Message']."<br />"; }
            return false;
        } else {
            $cid=$syns['InformationList']['Information'][0]['CID'];
            if($debug) { echo $cid."<br />"; }
            return $cid;
        }
    }

    /**
     * Get a list of synonyms of a chemical
     * @param $cid
     * @param $debug
     * @return bool
     */
    public function synonyms($cid,$debug=false)
    {
        if($debug) { echo "<b>function (synonyms)</b><br />"; }
        $HttpSocket = new HttpSocket();
        $url=$this->path.'cid/'.$cid.'/synonyms/JSON';
        if($debug) { echo $url."<br />"; }
        $json=$HttpSocket->get($url);
        $syns=json_decode($json['body'],true);
        if($debug) { debug($syns); }
        if(isset($syns['Fault'])):	return false;
        else:						return $syns['InformationList']['Information'][0]['Synonym'];
        endif;
    }

    /**
     * Get a property of a chemical
     * List of proprties available at
     * http://pubchem.ncbi.nlm.nih.gov/pug_rest/PUG_REST.html#_Toc409516770
     * @param $props
     * @param $cid
     * @param $debug
     * @return bool
     */
    public function property($props,$cid,$debug=false) {
        if($debug) { echo "<b>function (property)</b><br />"; }
        $HttpSocket = new HttpSocket();
        $url=$this->path.'cid/'.rawurlencode($cid).'/property/'.$props.'/JSON';
        if($debug) { echo $url."<br />"; }
        $json=$HttpSocket->get($url);
        $meta=json_decode($json['body'],true);
        if($debug) { echo "<pre>".print_r($meta)."</pre>"; }
        if(isset($meta['Fault'])):	return false;
        else:						return $meta['PropertyTable']['Properties'][0];
        endif;
    }

    /**
     * Find all cids from a formula
     * @param $form
     * @param bool $debug
     * @return mixed
     */
    public function formula($form,$debug=false)
    {
        // Get listkey token
        if($debug) { echo "<b>function (by formula)</b><br />"; }
        $HttpSocket = new HttpSocket();
        $url=$this->path.'formula/'.rawurlencode($form).'/JSON';
        if($debug) { echo $url."<br />"; }
        $json=$HttpSocket->get($url);
        $resp=json_decode($json['body'],true);
        // Get list of compounds
        $url2=$this->path.'listkey/'.rawurlencode($resp['Waiting']['ListKey']).'/cids/JSON';
        if($debug) { echo $url2."<br />"; }
        $resp2=['Waiting'=>[]];
        while(isset($resp2['Waiting'])) {
            if($debug) { debug($resp2); }
            $json=$HttpSocket->get($url2);
            $resp2=json_decode($json['body'],true);
        }
        $cids=$resp2['IdentifierList']['CID'];
        if($debug) { debug($cids); }
        return $cids;
    }

    /**
     * Check for a
     * @param $name
     * @param $cas
     * @param $debug
     * @return mixed
     */
    public function check($name,$cas="",$debug)
    {
        // Get CID if exists by checking name then CAS
        $cid=$this->cid($name,$debug);
        if($cid==false) {
            $cid=$this->cid($cas,$debug);
            if($cid==false) {
                return false;
            }
        }
        //echo $cid;exit;
        // Get property data
        $props="MolecularFormula,MolecularWeight,CanonicalSMILES,InChI,InChIKey,IUPACName";
        return $this->property($props,$cid,$debug);
    }
}
