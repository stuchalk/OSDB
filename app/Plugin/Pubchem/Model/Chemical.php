<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Chemical
 * Chemical model
 */
class Chemical extends AppModel
{

    public $path="http://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/";

    public $useTable = false;

    /**
     * Get the PubChem CID for chemical based on name or CAS search of names
     * You can use names, ids, cas# etc...
     * Format returned has CID and Synonyms in separate parts of array
     * @param $name
     * @return bool
     */
    public function cid($name)
    {
        $HttpSocket = new HttpSocket();
        $url=$this->path.'name/'.rawurlencode($name).'/synonyms/JSON';
        //echo $url;exit;
        $json=$HttpSocket->get($url);
        //echo $json;exit;
        $syns=json_decode($json['body'],true);
        if(isset($syns['Fault'])):	return false;
        else:						return $syns['InformationList']['Information'][0]['CID'];
        endif;
    }

    /**
     * Get a list of synonyms of a chemical
     * @param $cid
     * @return bool
     */
    public function synonyms($cid)
    {
        $HttpSocket = new HttpSocket();
        $url=$this->path.'cid/'.$cid.'/synonyms/JSON';
        //echo $url;exit;
        $json=$HttpSocket->get($url);
        //echo $json;exit;
        $syns=json_decode($json['body'],true);
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
     * @return bool
     */
    public function property($props,$cid) {
        $HttpSocket = new HttpSocket();
        $url=$this->path.'cid/'.rawurlencode($cid).'/property/'.$props.'/JSON';
        $json=$HttpSocket->get($url);
        //echo $url;exit;
        $meta=json_decode($json['body'],true);
        if(isset($meta['Fault'])):	return false;
        else:						return $meta['PropertyTable']['Properties'][0];
        endif;
    }

    /**
     * Check for a
     * @param $name
     * @param $cas
     * @return mixed
     */
    public function check($name,$cas="")
    {
        // Get CID if exists by checking name then CAS
        $cid=$this->cid($name);
        if($cid==false) {
            $cid=$this->cid($cas);
            if($cid==false) {
                return false;
            }
        }
        //echo $cid;exit;
        // Get property data
        $props="MolecularFormula,MolecularWeight,CanonicalSMILES,InChI,InChIKey,IUPACName";
        return $this->property($props,$cid);
    }
}
