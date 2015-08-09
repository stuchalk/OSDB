<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class File
 * File model Testing
 */
class File extends AppModel
{
    public $format=0;

    public $actsAs = ['Containable'];
    public $belongsTo = ['Substance','Technique'];
    public $hasOne = ['Dataset'];

    /*
     * function getCode
     * Gets the property type code from a file that has already been transferred to the pdf folder
     * @parameter $filename: The name of the file to extract the property type code from
     * @parameter $publicationID: ID of the publication in string format
     * @return $propertyID: returned the found property id if it exist.
     */
    public function getCode($filename,$publicationID){
        $fileToExtract=WWW_ROOT.'files'.DS.'pdf'.DS.$publicationID.DS.$filename;// find the path to the file name
        if (file_exists($fileToExtract))
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $pdfToTextPath = Configure::read("pdftotextPath.windows"); //save path to the pdftotext for the server
            }elseif (PHP_OS=="Linux") {
                $pdfToTextPath=Configure::read("pdftotextPath.linux");
            }elseif (PHP_OS=="FreeBSD") {
                $pdfToTextPath=Configure::read("pdftotextPath.freebsd");
            }else{
                $pdfToTextPath=Configure::read("pdftotextPath.mac");
            }
            $status=0;
            $this->format = 0;
            $str=shell_exec($pdfToTextPath.' -layout -r 300 -H 1000 -W 4000 "'. $fileToExtract.'" -'); //run the extraction
            preg_match("!Property Type: \[(.*)\]!",$str,$matches); //general match
            if(empty($matches)){
                preg_match("!Property Code (.*)!",$str,$matches); //match for file that uses code instead of type
                if(!empty($matches)) {
                    $this->format = 1;
                }
            }
            if(!isset($matches[1])){
                return false;
            }
            return trim($matches[1]);
        } else {
            throw new Exception($filename.": File Not Found");
        }
    }

    /**
     * Using Pubchem, get the metadata about the compounds
     * @param $name
     * @return mixed
     */
    public function getChem($name)
    {
        $Chm = ClassRegistry::init('Pubchem.Chemical');
        $Sub = ClassRegistry::init('Substance');
        $Idn = ClassRegistry::init('Identifier');

        $nih=$Chm->check($name);
        $sub=$Sub->add(['name'=>ucfirst(strtolower($name)),'formula'=>$nih['MolecularFormula'],'molweight'=>$nih['MolecularWeight']]);
        $sid=$sub['id'];
        $iarray=['CID'=>'pubchemId','CanonicalSMILES'=>'smiles','InChI'=>'inchi','InChIKey'=>'inchikey','IUPACName'=>'iupacname'];
        foreach($iarray as $field=>$type) {
            $Idn->add(['substance_id'=>$sid,'type'=>$type,'value'=>$nih[$field]]);
        }
        // Get synonyms
        $syns=$Chm->synonyms($nih['CID']);
        foreach($syns as $syn) {
            $Idn->add(['substance_id'=>$sid,'type'=>'name','value'=>$syn]);
       }
        return $sid;
        //debug($sub);exit;
    }

    /**
     * General function to add a new file
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='File';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}