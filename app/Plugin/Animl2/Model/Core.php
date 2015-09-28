<?php

/**
 * Class Core
 * AnIML Core Schema processing
 */
class Core extends AnimlAppModel
{
    public $useTable = false;
    public $actsAs=['Animl.Schema'];

    /**
     * Creates a new CoreSimple.php file from the simpleType elements found in AnIML core schema
     * Contains all the simpleType elements in the schema
     * @param $simples
     */
    public function createSimple($simples)
    {
        $output=$this->startFile('coreSimple');

        // Create simpleType functions
        foreach($simples as $simple)
        {
            $output=$this->annotation($simple,$output);
            $output=$this->publicFunc($simple,$output);

            // Restrictions?
            if(isset($simple['restriction'])) {
                if(!isset($simple['restriction'][0])) { $simple['restriction']=[$simple['restriction']]; }
                foreach($simple['restriction'] as $idx=>$res)
                {
                    $output = $this->restriction($idx, $res, $output,"sim");
                }
            }

            $output[]="return \$sim;";
            $output=$this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"CoreSimple");
        return;
    }

    /**
     * Creates a new CoreAttrgroup.php file from the simpleType elements found in AnIML core schema
     * contains all the attributeGroup elements in the schema
     * @param $groups
     */
    public function createAttrgroup($groups)
    {
        $output=$this->startFile('coreAttrgroup');

        // Create attributeGroup functions
        foreach($groups as $group)
        {
            $output=$this->annotation($group,$output);
            $output=$this->publicFunc($group,$output);

            // Attributes?
            if(isset($group['attribute'])) {
                if(!isset($group['attribute'][0])) { $group['attribute']=[$group['attribute']]; }
                foreach($group['attribute'] as $idx=>$att)
                {
                    $output=$this->attribute($idx, $att, $output, "att");
                }
            }

            $output[]="return \$att;";
            $output=$this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"CoreAttrgroup");
        return;
    }

    /**
     * Creates a new CoreElement.php file from the element elements found in AnIML core schema
     * contains all the element elements in the schema
     * @param $elements
     */
    public function createElement($elements)
    {
        $output=$this->startFile('CoreElement');

        // Create attributeGroup functions
        foreach($elements as $element)
        {
            $output=$this->annotation($element,$output);
            $output=$this->publicFunc($element,$output);
            $output[]="\$ele['type']='".$element['@type']."';";

            // Keys?
            if(isset($element['key']))
            {
                if(!isset($element['key'][0])) { $element['key']=[$element['key']]; }
                foreach($element['key'] as $idx=>$key) {
                    $output = $this->key($idx, $key, $output,"ele");
                }
            }

            // Keyrefs?
            if(isset($element['keyref']))
            {
                if(!isset($element['keyref'][0])) { $element['keyref']=[$element['keyref']]; }
                foreach($element['keyref'] as $idx=>$ref) {
                    $output = $this->keyref($idx, $ref, $output,"ele");
                }
            }

            // Unique?
            if(isset($element['unique']))
            {
                if(!isset($element['unique'][0])) { $element['unique']=[$element['unique']]; }
                foreach($element['unique'] as $idx=>$uni) {
                    $output = $this->unique($idx, $uni, $output,"ele");
                }
            }

            $output[]="return \$ele;";
            $output=$this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"CoreElement");
        return;
    }

    /**
     * Creates a new CoreComplex.php file from the element elements found in AnIML core schema
     * contains all the element elements in the schema
     * @param $complexes
     */
    public function createComplex($complexes)
    {
        $output=$this->startFile('coreComplex');

        // Create complexType functions
        foreach($complexes as $complex) {
            $output = $this->annotation($complex, $output);
            $output = $this->publicFunc($complex, $output);

            // Sequences?
            if(isset($complex['sequence'])) {
                if(!isset($complex['sequence'][0])) { $complex['sequence'] = [$complex['sequence']]; }
                foreach ($complex['sequence'] as $idx=>$seq) {
                    $output = $this->sequence($idx, $seq, $output,"com");
                }
            }

            // SimpleContent?
            if(isset($complex['simpleContent']))
            {
                if(!isset($complex['simpleContent'][0])) { $complex['simpleContent'] = [$complex['simpleContent']]; }
                foreach ($complex['simpleContent'] as $idx=>$con) {
                    $output = $this->simpleContent($idx, $con, $output,"com");
                }
            }

            // ComplexContent?
            if(isset($complex['complexContent']))
            {
                if(!isset($complex['complexContent'][0])) { $complex['complexContent'] = [$complex['complexContent']]; }
                foreach ($complex['complexContent'] as $idx=>$con) {
                    $output = $this->complexContent($idx, $con, $output,"com");
                }
            }

            // Attributes?
            if(isset($complex['attribute'])) {
                if(!isset($complex['attribute'][0])) { $complex['attribute'] = [$complex['attribute']]; }
                foreach ($complex['attribute'] as $idx=>$att) {
                    $output = $this->attribute($idx, $att, $output,"com");
                }
            }

            // AttributeGroups?
            if(isset($complex['attributeGroup']))
            {
                if(!isset($complex['attributeGroup'][0])) { $complex['attributeGroup'] = [$complex['attributeGroup']]; }
                foreach ($complex['attributeGroup'] as $idx=>$grp) {
                    $output = $this->attributeGroup($idx, $grp, $output,"com");
                }
            }

            $output[] = "return \$com;";
            $output = $this->closeFunc($output);
        }
        $output=$this->closeFunc($output);

        // Write file
        $this->writeFile($output,"CoreComplex");
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
?>