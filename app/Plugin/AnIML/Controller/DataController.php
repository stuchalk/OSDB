<?php

/**
 * Class Data Controller
 * Wanted to use fileController but has keyword issues
 */
class DataController extends AnimlAppController
{

    public $uses = ['Animl.Core', 'Animl.CoreComplex', 'Animl.CoreElement', 'Animl.CoreSimple', 'Animl.CoreAttrgroup', 'Animl.Tech', 'Animl.TechComplex', 'Animl.TechElement', 'Animl.TechSimple', 'Animl.Jcamp', 'Animl.Animl'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('convert');
    }

    // Use only for testing without access to fedora
    public function testing()
    {
        $jcamp = Array("TITLE" => "Tartrazin, 1.1944mg/100ml", "OWNER" => "Converted from PECSS");
        $this->Animl->convertAniml($jcamp);
    }

    /**
     * function convert
     */
    public function convert()
    {
        if($this->data)
        {
            // Upload the file
            $upload=$this->data['Data']['file'];
            $file=file($upload['tmp_name'],FILE_IGNORE_NEW_LINES);

            // Convert file to jcampxml format (interm format as PHP array)
            if($this->data['Data']['type']=='jcamp')
            {
                // Cleanup and decompress
                $jcamp = new $this->Jcamp($file);
                $jcamp->clean();
                $jcamp->uncomment();
                $jcamp->ldrs();
                $jcamp->validate();
                $jcamp->standardize();
                $jcamp->decompress();
                // $ldrs=$jcamp->getLdrs();
                // echo "<pre>";print_r($ldrs);echo "</pre>";exit;

                // Save XML
                $path="files".DS."jcampxml".DS.str_replace(array('jdx','dx','DX'),'xml',$upload['name']);
                $fp=fopen(WWW_ROOT.$path,'w');
                fwrite($fp,$jcamp->makexml());
                fclose($fp);

                // Show XML
                header("Location: /".$path);
                exit;
                $ldrs=$jcamp->getLdrs();
            }
            elseif($this->data['Data']['type']=='peuv')
            {

            }

            // Convert jcampxml to AnIML
            unset($ldrs['DATA'][1]['raw']); // Removed for conversion, but needed for debug
            //echo "<pre>";print_r($ldrs);echo "</pre>";exit;

            // What type of AnIML file is it? (based on analytical technique)
            if($ldrs['DATATYPE']==Configure::read('jcamp.datatypes.uvvis'))
            {
                // Get the atdd document for the technique+variant
                $tech=$this->data['Data']['tech'];
                if(isset($this->data['Data'][$tech])):  $atdd=Configure::read('animl.uvvis.atdd.'.$this->data['Data'][$tech]);
                else:                                   $default=Configure::read('animl.uvvis.defaults.atdd');
                                                        $atdd=Configure::read('animl.uvvis.atdd.'.$default);  // Default
                endif;



                // Get the atdd as an array
                // $atdd=$this->Animl->readAtdd($atdd); // Reuse $atdd

                // Get the top level layout of core
                //$croot=$this->CoreComplex->AnIMLType();

                // Get the top level layout of technique
                //$troot=$this->TechComplex->TechniqueType();

                // What are the requirements?
                //$reqs=Configure::read('animl.uvvis.required');

                // What are the defaults?
                //$defs=Configure::read('animl.uvvis.defaults');

                // TODO: What sampleRole to use?
                //if(isset($this->data['samplerole'])):   $samplerole=$this->data['samplerole'];
                //else:                                   $samplerole=Configure::read('animl.uvvis.samplerole');  // Default
                //endif;

                $output = $this->Animl->convertAniml($ldrs,$atdd,$tech);
                echo "ANIML";
                echo "<pre>";print_r($output);echo "</pre>";
                exit;


            }
            elseif($ldrs['DATATYPE']==Configure::read('jcamp.datatypes.ir'))
            {

            }
            elseif($ldrs['DATATYPE']==Configure::read('jcamp.datatypes.nmr'))
            {
                echo "LDRS<br/>";
                echo "<pre>";print_r($ldrs);echo "</pre>";
                exit;
            }
        }
        else
        {
            // Get the supported technique types
            $techs=Configure::read('animl.techniques');
            //echo "<pre>";print_r($techs);echo "</pre>";

            $this->set('techs',$techs);
            foreach($techs as $type=>$tech)
            {
                $temp=Configure::read('animl.'.$type.'.schemas');
                //echo "<pre>";print_r($temp);echo "</pre>";exit;
                $this->set($type,$temp);
            }
        }
    }

}