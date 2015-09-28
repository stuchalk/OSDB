<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class File
 * AnIML display files for Pittcon workshop
 */
class File extends AnimlAppModel
{
    public $useTable = false;

    /**
     * Build JSON array ready for display using jquery/flot
     * @param $array
     */
    public function toJson($array)
    {

    }

    /**
     * Get the samples in an AnIML file
     * @param $pid
     * @return array
     */
    public function getSamples($pid)
    {
        $animl=$this->simple($pid);
        $samples=$animl->xpath('//a:Sample');$return=[];
        foreach($samples as $sample) {
            $return[]=['name'=>(string) $sample['name'],'id'=>(string) $sample['sampleID']];
        }
        return $return;
    }

    /**
     * Get the results in an AnIML file
     * @param $pid
     * @return array
     */
    public function getResults($pid)
    {
        $animl=$this->simple($pid);
        $expts=$animl->xpath('//a:ExperimentStep');$rtemp=[];$return=[];
        foreach($expts as $expt) {
            $expt->registerXPathNamespace('a','urn:org:astm:animl:schema:core:draft:0.90');
            $rtemp['expt']=(string) $expt['experimentStepID'];
            // Get samples
            $samples=$expt->xpath('//a:SampleReference');
            foreach($samples as $sample) {
                $rtemp['sample']=(string) $sample['sampleID'];
            }
            // Get results
            $results=$expt->xpath('//a:Result');
            foreach($results as $result) {
                $rtemp['result']=(string) $result['name'];
            }
            $return[]=$rtemp;
        }
        return $return;
    }

    public function getMeta($pid)
    {
        $animl=$this->simple($pid);$return=[];
        $temp=$animl->xpath("//a:Parameter[@name='Descriptive Name']");
        $return['sample']=$temp[0]->S;
        return $return;
    }

    public function getData($pid)
    {
        $animl=$this->simple($pid);$return=[];
        $temp=$animl->xpath("//a:Series[@seriesID='wavelength1']/a:AutoIncrementedValueSet/a:StartValue/a:F");
        $return['xstart']=(float) $temp[0];
        $temp=$animl->xpath("//a:Series[@seriesID='wavelength1']/a:AutoIncrementedValueSet/a:Increment/a:F");
        $return['inc']=(float) $temp[0];
        $temp=$animl->xpath("//a:Series[@seriesID='absorbance1']/a:IndividualValueSet/a:F");
        foreach($temp as $y) {
            $return['ydata'][]= (float) $y;
        }
        return $return;
    }

    public function xpath($pid,$xpath)
    {
        $animl=$this->simple($pid);$return=[];
        $temp=$animl->xpath("//a:".$xpath);
        $return['xpath']=$temp[0];
        return $return;
    }

    /**
     * Helper to get ANIML file datastream and setup in SimpleXML
     * @param $pid
     * @return SimpleXMLElement
     */
    private function simple($pid)
    {
        $Dstream=ClassRegistry::init('Datastream');
        $file=$Dstream->content($pid,'ANIML');
        $animl=simplexml_load_string($file['content'],'SimpleXMLElement',LIBXML_NOERROR|LIBXML_NOENT);
        $animl->registerXPathNamespace('a','urn:org:astm:animl:schema:core:draft:0.90');
        return $animl;
    }
}