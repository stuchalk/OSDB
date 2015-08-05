<?php
// File containing all necessary functions to convert jcampxml to animl
// Version 1.0
// Stuart J. Chalk
// Created: 2012-01-21

class Animl extends AnimlAppModel
{

    public $useTable=false;

    public $file=array();

    function __construct($file)
    {
        $this->file=$file;
    }

    function makeAniml()
    {
        // Load file
        $jcampxml= simplexml_load_string($this->file);

        // Work out which technique it is...
        if(stristr((string) $jcampxml->datatype,'uv/vis'))
        {
            $technique="uvvis";
            $animlxml=simplexml_load_file('templates/animl_0.37_uvvis_1.36_tmp.xml',NULL,LIBXML_DTDLOAD | LIBXML_NOENT);
        }
        elseif(stristr((string) $jcampxml->datatype,'infrared'))
        {
            $technique="ir";
            $animlxml=simplexml_load_file('templates/animl_0.37_ir_1.36_tmp.xml',NULL,LIBXML_DTDLOAD | LIBXML_NOENT);
        }
        $animlxml->registerXPathNamespace('a','urn:org:astm:animl:schema:core:draft:0.37');


        // Define parameters based on what the incoming file format is
        if($jcampxml->type=="jcamp")
        {
            $format="jcamp";
            // These are sample parameters
            $parray=array(	'sampledescription'=>'SAMPLE_DESC_NAME','casname'=>'CHEMICAL_CASNAME','casname'=>'CHEMICAL_CASNAME',
                'names'=>'SUBSTANCE_DESC_NAME','molform'=>'SUBSTANCE_FORMULA','casregistryno'=>'CHEMICAL_CASRN',
                'wiswesser'=>'CHEMICAL_WISWESSER','belisteinlawsonno'=>'CHEMICAL_BLN','mp'=>'SAMPLE_MP_MIN','bp'=>'SAMPLE_BP_MIN',
                'refractiveindex'=>'SAMPLE_RI_VALUE','density'=>'SAMPLE_DENSITY','mw'=>'SUBSTANCE_MOLARMASS','concentrations'=>'SAMPLE_CONC',
                'samplingprocedure'=>'SAMPLE_PREP','state'=>'SAMPLE_STATE','pathlength'=>'INST_SAMPLE_PATHLENGTH',
                'pressure'=>'SAMPLE_PRESSURE','temperature'=>'SAMPLE_TEMP','origin'=>'SAMPLE_ORIGIN');
        }
        elseif($jcampxml->type=="peasc")
        {
            $format="peasc";
            // This is an instrumemnt parameter
            $parray=array('scanspeed'=>'SCAN_SPEED');
        }


        // Add parameters
        foreach($parray as $key => $value)
        {
            $temp=$animlxml->xpath("//a:Parameter[@id='$value']");
            if((string) $jcampxml->$key=="")
            {
                if($key=="sampledescription"):	$temp[0]->String="Unknown";
                else:							$dom=dom_import_simplexml($temp[0]);
                    $dom->parentNode->removeChild($dom);
                endif;
            }
            else
            {
                $type=$temp[0]['parameterType'];
                $temp[0]->$type=$jcampxml->$key;
                if($key=="scanspeed" & $technique=="uvvis")
                {
                    // <!ENTITY nm_per_s "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='nm/s'><SIUnit exponent='1' factor='1e-9'>m</SIUnit><SIUnit exponent='-1' factor='1'>s</SIUnit></Unit>">
                    $unit=$temp[0]->addChild("Unit");
                    $unit->addAttribute('label','nm/s');
                    $siunit=$unit->addChild('SIUnit','m');
                    $siunit->addAttribute('factor','1e-9');
                    $siunit->addAttribute('exponent','1');
                    $siunit=$unit->addChild('SIUnit','s');
                    $siunit->addAttribute('factor','1');
                    $siunit->addAttribute('exponent','-1');
                }
            }
        }

        // These are method properties
        $marray=array(	'instname'=>'Device/Name','instserial'=>'Device/SerialNumber','instmanu'=>'Device/Manufacturer','firmwareversion'=>'Device/FirmwareVersion'
        ,'softwaremanu'=>'Software/Manufacturer','softwarename'=>'Software/Name','softwareversion'=>'Software/Version');
        $method=$animlxml->xpath("//a:Method");
        foreach($marray as $key => $value)
        {
            list($parent,$child)=explode("/",$value);
            $method[0]->$parent->$child=$jcampxml->$key;
        }
        // Add UNF specific info to Method
        if(stristr($_SERVER['REMOTE_ADDR'],'139.62'))
        {
            $method[0]->Author->Name="Stuart Chalk";
            $method[0]->Author->Affiliation="University of North Florida";
            $method[0]->Author->Role="Research Professor";
            $method[0]->Author->Email="schalk@unf.edu";
            $method[0]->Author->Phone="904-620-1938";
            $method[0]->Author->Location="Department of Chemistry, 1 UNF Drive, Jacksonville, FL 32224 USA";
        }

        // Work on attributes
        $aarray=array('title'=>'EXPT_NAME/name','npoints'=>'SPECTRUM_XYSERIES/length');
        foreach($aarray as $key => $value)
        {
            list($id,$attr)=explode("/",$value);
            $temp=$animlxml->xpath("//*[@id='$id']");
            if((string) $jcampxml->$key=="")
            {
                $temp[0][$attr]="Unknown";
            }
            else
            {
                $temp[0][$attr]=$jcampxml->$key;
            }
        }

        // Work around for elements with no IDREF
        // Owner (put in Author)
        $temp=$animlxml->xpath("//a:Method");
        if((string) $jcampxml->owner!=""):	$temp[0]->Author->Name=$jcampxml->owner;
        else:								$dom=dom_import_simplexml($temp[0]);
            $dom->parentNode->removeChild($dom);
        endif;

        // Timestamp
        $temp=$animlxml->xpath("//a:Infrastructure");
        if((string) $jcampxml->date!=""):	list($year,$month,$day)=explode("/",$jcampxml->date);
            $hr=$min=$sec="";
            if((string) $jcampxml->time!="") { list($hr,$min,$sec)=explode(":",$jcampxml->time); }
            $timestamp=date(DATE_ATOM, mktime($hr,$min,$sec,$month,$day,$year));
            $temp[0]->Timestamp=$timestamp;
        else:								$dom=dom_import_simplexml($temp[0]);
            $dom->parentNode->removeChild($dom);
        endif;

        // Work on plot axes
        // x axis
        $temp=$animlxml->xpath("//a:Series[@id='SPECTRUM_XAXIS']");
        $xunit=$jcampxml->xunits;
        $xlabel=$jcampxml->xlabel;
        if($xlabel==""&&$xunit=="nm"):			$xlabel="Wavelength";
        elseif($xlabel==""&&$xunit=="eV"):		$xlabel="Radiant Energy";
        elseif($xlabel==""&&$xunit=="1/CM"):	$xlabel="Wavenumber";
        endif;
        $temp[0]['name']=$xlabel;
        // If I could add entities using simpleXML I would but it does not seem to do that
        $unit=$temp[0]->addChild("Unit");
        if(stristr($xunit,"nm"))
        {
            // <!ENTITY nm "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='nm'><SIUnit factor='1e-9' exponent='1'>m</SIUnit></Unit>">
            $unit->addAttribute('label','nm');
            $siunit=$unit->addChild('SIUnit','m');
            $siunit->addAttribute('factor','1e-9');
            $siunit->addAttribute('exponent','1');
        }
        elseif(stristr($xunit,"eV"))
        {
            // <!ENTITY eV "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='eV'><SIUnit factor='1.60218e-19' exponent='1'>kg</SIUnit><SIUnit factor='1' exponent='2'>m</SIUnit><SIUnit factor='1' exponent='-2'>s</SIUnit></Unit>">
            $unit->addAttribute('label','eV');
            $siunit=$unit->addChild('SIUnit','kg');
            $siunit->addAttribute('factor','1.60218e-19');
            $siunit->addAttribute('exponent','1');
            $siunit=$unit->addChild('SIUnit','m');
            $siunit->addAttribute('factor','1');
            $siunit->addAttribute('exponent','2');
            $siunit=$unit->addChild('SIUnit','s');
            $siunit->addAttribute('factor','1');
            $siunit->addAttribute('exponent','-2');
        }
        elseif(stristr($xunit,"1/cm")) // Matches upper or lower case
        {
            // <!ENTITY reciprocal_cm "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='1/cm'><SIUnit exponent='-1' factor='1e-2'>m</SIUnit></Unit>">
            $unit->addAttribute('label','1/cm');
            $siunit=$unit->addChild('SIUnit','m');
            $siunit->addAttribute('factor','1e-2');
            $siunit->addAttribute('exponent','-1');
        }

        // y axis (Label must be intensity)
        $temp=$animlxml->xpath("//a:Series[@id='SPECTRUM_YAXIS']");
        $yunit=$jcampxml->yunits;
        $unit=$temp[0]->addChild("Unit");
        if(stristr($yunit,"abs")||$yunit=="A")
        {
            // <!ENTITY A "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='A'><SIUnit exponent='1' factor='1'>1</SIUnit></Unit>">
            $unit->addAttribute('label','A');
            $siunit=$unit->addChild('SIUnit','1');
            $siunit->addAttribute('factor','1');
            $siunit->addAttribute('exponent','1');
        }
        elseif(stristr($yunit,"trans")||$yunit=="T")
        {
            //<!ENTITY T "<Unit xmlns='urn:org:astm:animl:schema:core:draft:0.37' label='T'><SIUnit exponent='1' factor='1'>1</SIUnit></Unit>">
            $unit->addAttribute('label','T');
            $siunit=$unit->addChild('SIUnit','1');
            $siunit->addAttribute('factor','1');
            $siunit->addAttribute('exponent','1');
        }

        // Work on plot data
        // x axis
        $temp=$animlxml->xpath("//a:Series[@id='SPECTRUM_XAXIS']");
        $type=$temp[0]['seriesType'];
        $temp[0]->AutoIncrementedValueSet->StartValue->$type=$jcampxml->firstx;
        $temp[0]->AutoIncrementedValueSet->Increment->$type=$jcampxml->deltax;

        // y axis
        $axis=$animlxml->xpath("//a:Series[@id='SPECTRUM_YAXIS']");
        $type=$axis[0]['seriesType'];
        $temp=$axis[0]->IndividualValueSet[0];
        $dom=dom_import_simplexml($temp->$type);
        $dom->parentNode->removeChild($dom);
        foreach($jcampxml->data->set->pro->xy as $point)
        {
            list($x,$y)=explode(",",$point);
            $temp->addChild($type,$y);
        }

        // Work on AuditTrail Section
        $audit=$animlxml->xpath("//a:AuditTrailEntry");  // There is a default one in the tempalte file
        $audit[0]->Timestamp=date(DATE_ATOM);
        $filename=str_replace('xml','asc',$jcampxml->filename);
        if($format=="peasc"):		$audit[0]->Reason="Conversion of PE Winlab ASCII file '".$filename."' to AnIML";
        elseif($format=="jcamp"):	$audit[0]->Reason="Conversion of JCAMP file '".$filename."' to AnIML";
        endif;

        // Return animl file
        return $animlxml->asXML();
    }

    // Private methods for making AnIML files

    // AnIML Element (ComplexType) ROOT
    // SampleSet Element (ComplexType)
    private function parameter()
    {

    }

    // Private methods for interpretting the AnIML Schema and Technique Definition Documents

    // Read the AnIML core schema
    public function readCore($path="https://eureka.coas.unf.edu/files/animl-core_ordered.xsd")
    {
        // Uses XSLT stream of xslt:xml2json object to convert XML to json using saxon (this avoids using SimpleXML)
        $Service = ClassRegistry::init('Service');
        $json=$Service->saxon($path,'xslt:xml2json*XSLT',[],'json');
        $data=json_decode($json,true);
        return $data;
    }

    // Read the AnIML technique schema
    public function readTech($path="https://eureka.coas.unf.edu/files/animl-technique_ordered.xsd")
    {
        // Uses XSLT stream of xslt:xml2json object to convert XML to json using saxon (this avoids using SimpleXML)
        $Service = ClassRegistry::init('Service');
        $json=$Service->saxon($path,'xslt:xml2json*XSLT',[],'json');
        $data=json_decode($json,true);
        return $data;
    }

    // Read Atdd file
    public function readAtdd($path="")
    {
        // Uses XSLT stream of xslt:xml2json object to convert XML to json using saxon (this avoids using SimpleXML)
        $Service = ClassRegistry::init('Service');
        $json=$Service->saxon($path,'xslt:xml2json*XSLT',[],'json');
        $data=json_decode($json,true);
        return $data;
    }

    //  Convert JcampXML to AniML
    public function convertAniml($ldrs,$atdd,$tech)
    {
        $file = file_get_contents(WWW_ROOT.DS."files".DS."jcampcross.json");
        $cross = json_decode($file, true);
        $ns = "urn:org:astm:animl:schema:core:draft:0.90";

        // Create new simpleXML object
        // Process requirements first
        $string = '<?xml version="1.0" encoding="UTF-8"?>
                    <AnIML xmlns="urn:org:astm:animl:schema:core:draft:0.90"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xsi:schemaLocation="urn:org:astm:animl:schema:core:draft:0.90 http://animl.cvs.sourceforge.net/viewvc/animl/schema/animl-core.xsd"
                            version="0.90">
                    </AnIML>';

        $animl = simplexml_load_string($string);
        $animl->registerXPathNamespace('a', $ns);
        //echo "<pre>";print_r($animl);echo "</pre>";

        // Add LDRS
        $unique=1;
        foreach($cross as $ldr=>$larray) {
            echo $ldr."<br />";
            if(empty($larray['animlXpath'])) { continue; } // Go to next LDR
            foreach($larray["animlXpath"] as $xpath) {
                if ($xpath[0] == '/') {
                    $xpath = substr($xpath, 1);
                }
                if ($xpath[0] == '/') {
                    $xpath = substr($xpath, 1);
                }
                $xpathExp = explode("/", $xpath);

                // Make new part of XML tree
                // Get value of LDR
                $level = 1;
                $ldrset = "no";
                if ($larray['default'] == "{uniqueid}") {
                    $value = "ID" . str_pad($unique, 3, "0", STR_PAD_LEFT);
                    $unique++;
                } elseif ($larray['default'] == "{atdd}") {
                    $value = $atdd;
                } elseif ($larray['default'] == "{tech}") {
                    $value = $tech;
                } elseif (isset($ldrs[$ldr])) {
                    $value = $ldrs[$ldr];
                } else {
                    $value = $larray['default'];
                }
                // Go through each part and build on the previous
                // We will need to add more levels for some of the xpath expressions
                $count=count($xpathExp);
                foreach ($xpathExp as $part) {
                    if (strpos($part, "@") === FALSE) {  // This will trigger for all parts with no "@" character
                        // Element
                        list($element, $index) = explode("[", substr($part, 0, -1), 2);
                        if ($level == 1) {
                            if (!property_exists($animl, $element)) {
                                if($level==$count) {
                                    $level2 = $animl->addChild($element, $value, $ns);
                                } else {
                                    $level2 = $animl->addChild($element, null, $ns);
                                }
                            } else {
                                $temp = $animl->xpath("//a:" . $element);
                                $level2 = $temp[$index - 1];
                            }
                        } elseif ($level == 2) {
                            if (!property_exists($level2, $element)) {
                                if($level==$count) {
                                    $level3 = $level2->addChild($element, $value, $ns);
                                } else {
                                    $level3 = $level2->addChild($element, null, $ns);
                                }
                            } else {
                                $level2->registerXPathNamespace('a', $ns);
                                $temp = $level2->xpath("//a:" . $element);
                                $level3 = $temp[$index - 1];
                            }
                        } elseif ($level == 3) {
                            if (!property_exists($level3, $element)) {
                                if($level==$count) {
                                    $level4 = $level3->addChild($element, $value, $ns);
                                } else {
                                    $level4 = $level3->addChild($element, null, $ns);
                                }
                            } else {
                                $level3->registerXPathNamespace('a', $ns);
                                $temp = $level3->xpath("//a:" . $element);
                                $level4 = $temp[$index - 1];
                            }
                        } elseif ($level == 4) {
                            if (!property_exists($level4, $element)) {
                                if($level==$count) {
                                    $level5 = $level4->addChild($element, $value, $ns);
                                } else {
                                    $level5 = $level4->addChild($element, null, $ns);
                                }
                            } else {
                                $level4->registerXPathNamespace('a', $ns);
                                $temp = $level4->xpath("//a:" . $element);
                                $level5 = $temp[$index - 1];
                            }
                        } elseif ($level == 5) {
                            if (!property_exists($level5, $element)) {
                                if($level==$count) {
                                    $level6 = $level5->addChild($element, $value, $ns);
                                } else {
                                    $level6 = $level5->addChild($element, null, $ns);
                                }
                            } else {
                                $level5->registerXPathNamespace('a', $ns);
                                $temp = $level5->xpath("//a:" . $element);
                                $level6 = $temp[$index - 1];
                            }
                        } elseif ($level == 6) {
                            if (!property_exists($level6, $element)) {
                                if($level==$count) {
                                    $level7 = $level6->addChild($element, $value, $ns);
                                } else {
                                    $level7 = $level6->addChild($element, null, $ns);
                                }
                            } else {
                                $level6->registerXPathNamespace('a', $ns);
                                $temp = $level6->xpath("//a:" . $element);
                                $level7 = $temp[$index - 1];
                            }
                        }
                        $level++;
                    } elseif (substr($part, 0, 1) == "@") { // This will trigger only for parts that start with "@"
                        // Attribute
                        $attr = str_replace("@", "", $part);
                        if ($level == 1) {
                            $attrs = $animl->attributes();
                            if (!isset($attrs[$attr])) {
                                $animl->addAttribute($attr, $value);
                            } else {
                                $animl[$attr] = $value;
                            }
                        } elseif ($level == 2) {
                            $attrs = $level2->attributes();
                            if (!isset($attrs[$attr])) {
                                $level2->addAttribute($attr, $value);
                            } else {
                                $level2[$attr] = $value;
                            }
                        } elseif ($level == 3) {
                            $attrs = $level3->attributes();
                            if (!isset($attrs[$attr])) {
                                $level3->addAttribute($attr, $value);
                            } else {
                                $level3[$attr] = $value;
                            }
                        } elseif ($level == 4) {
                            $attrs = $level4->attributes();
                            if (!isset($attrs[$attr])) {
                                $level4->addAttribute($attr, $value);
                            } else {
                                $level4[$attr] = $value;
                            }
                        } elseif ($level == 5) {
                            $attrs = $level5->attributes();
                            if (!isset($attrs[$attr])) {
                                $level5->addAttribute($attr, $value);
                            } else {
                                $level5[$attr] = $value;
                            }
                        } elseif ($level == 6) {
                            $attrs = $level6->attributes();
                            if (!isset($attrs[$attr])) {
                                $level6->addAttribute($attr, $value);
                            } else {
                                $level6[$attr] = $value;
                            }
                        } elseif ($level == 7) {
                            $attrs = $level7->attributes();
                            if (!isset($attrs[$attr])) {
                                $level7->addAttribute($attr, $value);
                            } else {
                                $level7[$attr] = $value;
                            }
                        }
                        $ldrset = "yes";
                        break; // End of xpath
                    } else {
                        // Will trigger when "@" is present somewhere other than the start of $part.
                        // Equivalent code for xpath expression that defines an element
                        // with a named attribute i.e. Category[@name='Dispersive Method']
                        list($element, $attrPart) = explode("[", $part, 2);
                        $attr = "[" . $attrPart;

                        // Takes attribute section and splits into Name and Value for addAttribute()
                        $remove = ["'", '[', ']', '@'];
                        $attrExp = str_replace($remove, "", $attr);
                        list($attrName, $attrVal) = explode("=", $attrExp, 2);
                        echo $attrName.":".$attrVal."<br />";
                        $isAttr = "no";
                        if ($level == 1) {
                            $children = $animl->xpath("//a:" . $element);
                            foreach($children as $child) {
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])) {
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($animl, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level2 = $animl->addChild($element, $value, $ns);
                                } else {
                                    $level2 = $animl->addChild($element, null, $ns);
                                }
                                $level2->addAttribute('a:'.$attrName, $attrVal, $ns);
                            } else {
                                $temp = $animl->xpath("//a:" . $element . $attr);
                                $level2 = $temp[0];
                            }
                        } elseif ($level == 2) {
                            $level2->registerXPathNamespace('a', $ns);
                            $children = $level2->xpath("//a:" . $element);
                            foreach($children as $child){
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])){
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($level2, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level3 = $level2->addChild($element, $value, $ns);
                                } else {
                                    $level3 = $level2->addChild($element, null, $ns);
                                }
                                $level3->addAttribute('a:'.$attrName, $attrVal, $ns);
                            } else {
                                $level2->registerXPathNamespace('a', $ns);
                                $temp = $level2->xpath("//a:" . $element . $attr);
                                $level3 = $temp[0];
                            }
                        } elseif ($level == 3) {
                            $level3->registerXPathNamespace('a', $ns);
                            $children = $level3->xpath("//a:" . $element);
                            foreach($children as $child){
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])){
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($level3, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level4 = $level3->addChild($element, $value, $ns);
                                } else {
                                    $level4 = $level3->addChild($element, null, $ns);
                                }
                                $level4->addAttribute('a:'.$attrName, $attrVal, $ns);
                            } else {
                                $level3->registerXPathNamespace('a', $ns);
                                $temp = $level3->xpath("//a:" . $element . $attr);
                                $level4 = $temp[0];
                            }
                        } elseif ($level == 4) {
                            $level4->registerXPathNamespace('a', $ns);
                            $children = $level4->xpath("//a:" . $element);
                            foreach($children as $child){
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])){
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($level4, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level5 = $level4->addChild($element, $value, $ns);
                                } else {
                                    $level5 = $level4->addChild($element, null, $ns);
                                }
                                $level5->addAttribute('a:'.$attrName, $attrVal, $ns);
                            } else {
                                $level4->registerXPathNamespace('a', $ns);
                                $temp = $level4->xpath("//a:" . $element . $attr);
                                $level5 = $temp[0];
                            }
                        } elseif ($level == 5) {
                            $level5->registerXPathNamespace('a', $ns);
                            $children = $level5->xpath("//a:" . $element);
                            foreach($children as $child){
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])){
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($level5, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level6 = $level5->addChild($element, $value, $ns);
                                } else {
                                    $level6 = $level5->addChild($element, null, $ns);
                                }
                                $level6->addAttribute('a:'.$attrName, $attrVal, $ns);
                            } else {
                                $level5->registerXPathNamespace('a', $ns);
                                $temp = $level5->xpath("//a:" . $element . $attr);
                                $level6 = $temp[0];
                            }
                        } elseif ($level == 6) {
                            $level6->registerXPathNamespace('a', $ns);
                            $children = $level6->xpath("//a:" . $element);
                            foreach($children as $child){
                                $attrs = $child->attributes();
                                if (isset($attrs[$attrName])){
                                    $isAttr = "yes";
                                }
                            }
                            if (!property_exists($level6, $element) || $isAttr == "no") {
                                if($level==$count) {
                                    $level7 = $level6->addChild($element, $value, $ns);
                                } else {
                                    $level7 = $level6->addChild($element, null, $ns);
                                }
                                $level7->addAttribute('a:'.$attrName, $attrVal, $ns);
                                //echo "<pre>";print_r($level7);echo "</pre>";
                            } else {
                                $level6->registerXPathNamespace('a', $ns);
                                $temp = $level6->xpath("//a:" . $element . $attr);
                                $level7 = $temp[0];
                            }
                        }
                        $level++;
                    }
                }
            }
            // Using this to stop the loop for debug
            //if($ldr=="RESOLUTION") { echo $animl->asXML();exit; }
            if($ldr=="YUNITS") { echo "<pre>";print_r($animl);echo "</pre>";exit; }
        }
        return;
    }

}
?>