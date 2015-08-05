<?php

class Tech extends AnimlAppModel
{
    public $useTable = false;
    public $actsAs=array('Animl.Schema');

    /**
     * Creates a new TechSimple.php file from the simpleType elements found in AnIML technique schema
     * @param $simples contains all the simpleType elements in the schema
     */
    public function createSimple($simples)
    {
        $output="";
        $output=$this->startFile('techSimple');

        // Create simpleType functions
        foreach($simples as $simple)
        {
            $output=$this->annotation($simple,$output);
            $output=$this->publicFunc($simple,$output);

            // Restrictions?
            if(isset($simple['restriction'])) {
                if(!isset($simple['restriction'][0])) { $simple['restriction']=array($simple['restriction']); }
                foreach($simple['restriction'] as $idx=>$res)
                {
                    $output = $this->restriction($idx, $res, $output,"sim");
                }
            }

            // Unions?
            if(isset($simple['union'])) {
                if(!isset($simple['union'][0])) { $simple['union']=array($simple['union']); }
                foreach($simple['union'] as $idx=>$unn)
                {
                    $output = $this->union($idx, $unn, $output,"sim");
                }
            }

            $output[]="return \$sim;";
            $output=$this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"TechSimple");
        return;
    }

    /**
     * Creates a new TechComplex.php file from the element elements found in AnIML technique schema
     * @param $complexes contains all the element elements in the schema
     */
    public function createComplex($complexes)
    {
        $output="";
        $output=$this->startFile('techComplex');

        // Create complexType functions
        foreach($complexes as $complex) {
            $output = $this->annotation($complex, $output);
            $output = $this->publicFunc($complex, $output);

            // Sequences?
            if(isset($complex['sequence'])) {
                if (!isset($complex['sequence'][0])) { $complex['sequence'] = array($complex['sequence']); }
                foreach ($complex['sequence'] as $idx=>$seq) {
                    $output = $this->sequence($idx, $seq, $output,"com");
                }
            }

            // SimpleContent?
            if(isset($complex['simpleContent']))
            {
                if (!isset($complex['simpleContent'][0])) { $complex['simpleContent'] = array($complex['simpleContent']); }
                foreach ($complex['simpleContent'] as $idx=>$sim) {
                    $output = $this->simpleContent($idx, $sim, $output,"com");
                }
            }

            // ComplexContent?
            if(isset($complex['complexContent']))
            {
                if (!isset($complex['complexContent'][0])) { $complex['complexContent'] = array($complex['complexContent']); }
                foreach ($complex['complexContent'] as $idx=>$con) {
                    $output = $this->complexContent($idx, $con, $output,"com");
                }
            }

            // Attributes?
            if(isset($complex['attribute'])) {
                if (!isset($complex['attribute'][0])) { $complex['attribute'] = array($complex['attribute']); }
                foreach ($complex['attribute'] as $idx=>$att) {
                    $output = $this->attribute($idx, $att, $output,"com");
                }
            }

            // AttributeGroups?
            if(isset($complex['attributeGroup']))
            {
                if (!isset($complex['attributeGroup'][0])) { $complex['attributeGroup'] = array($complex['attributeGroup']); }
                foreach ($complex['attributeGroup'] as $idx=>$grp) {
                    $output = $this->attributeGroup($idx, $grp, $output,"com");
                }
            }

            $output[] = "return \$com;";
            $output = $this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"TechComplex");
        return;
    }

    /**
     * Creates a new TechElement.php file from the element elements found in AnIML technique schema
     * @param $elements contains all the element elements in the schema
     */
    public function createElement($elements)
    {
        $output="";
        $output=$this->startFile('techElement');

        // Create attributeGroup functions
        foreach($elements as $element)
        {
            $output=$this->annotation($element,$output);
            $output=$this->publicFunc($element,$output);
            $output[]="\$ele['type']='".$element['@type']."';";

            // Keys?
            if(isset($element['key']))
            {
                if(!isset($element['key'][0])) { $element['key']=array($element['key']); }
                foreach($element['key'] as $idx=>$key) {
                    $output = $this->key($idx, $key, $output, "ele");
                }
            }

            // Keyrefs?
            if(isset($element['keyref']))
            {
                if(!isset($element['keyref'][0])) { $element['keyref']=array($element['keyref']); }
                foreach($element['keyref'] as $idx=>$ref) {
                    $output = $this->keyref($idx, $ref, $output, "ele");
                }
            }

            // Unique?
            if(isset($element['unique']))
            {
                if(!isset($element['unique'][0])) { $element['unique']=array($element['unique']); }
                foreach($element['unique'] as $idx=>$uni) {
                    $output = $this->unique($idx, $uni, $output,"ele");
                }
            }

            $output[]="return \$ele;";
            $output=$this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"TechElement");
        return;
    }

    /**
     * function writeFile
     * @param $output
     * @param $type
     */
    public function writeFile($output,$type)
    {
        $filepath=APP."Plugin".DS."Animl".DS."Model".DS.$type.".php";
        $fp = fopen($filepath,"w");
        fwrite($fp,"<?php\n\n".implode("\n",$output)."?>");
        fclose($fp);
        chmod($filepath, 0777);
        return;
    }

}
