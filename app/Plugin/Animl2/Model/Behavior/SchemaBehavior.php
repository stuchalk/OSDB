<?php

/**
 * Class SchemaBehavior
 * Creates CakePHP Model files from XML Schema
 */
class SchemaBehavior extends ModelBehavior
{
    // General file functions

    /**
     * function startFile
     * @param Model $model
     * @param $name
     * @return array
     */
    public function startFile(Model $model,$name)
    {
        $out=[];
        $out[]="/**";
        $out[]=" * Class ".ucfirst($name);
        $out[]=" */";
        $out[]="";
        $out[]="class ".ucfirst($name)." extends AnimlAppModel";
        $out[]="{";
        $out[]="public \$useTable = false;";
        $out[]="";
        return $out;
    }

    /**
     * function annotation
     * @param Model $model
     * @param $simple
     * @param $out
     * @return array
     */
    public function annotation(Model $model,$simple,$out)
    {
        $out[]="/**";
        if(isset($simple['annotation']['documentation'])):
            $temp=$simple['annotation']['documentation'];
        else:
            $temp="No documentation provided for simpleType '".$simple['@name']."'";
        endif;
        $out[]=" * ".$temp;
        $out[]=" */";
        return $out;
    }

    /**
     * function publicFunc
     * @param Model $model
     * @param $simple
     * @param $out
     * @return array
     */
    public function publicFunc(Model $model,$simple,$out)
    {
        $out[]="public function ".$simple['@name']."()";
        $out[]="{";
        return $out;
    }

    /**
     * function closeFunc
     * @param Model $model
     * @param $out
     * @return array
     */
    public function closeFunc(Model $model,$out)
    {
        $out[]="}";
        $out[]="";
        return $out;
    }

    // AttributeGroup functions

    /**
     * function attribute (att)
     * @param Model $model
     * @param $idx
     * @param $att
     * @param $out
     * @param $parent
     * @return array
     */
    public function attribute(Model $model,$idx,$att,$out,$parent="")
    {
        ($parent=="") ? $parent="att[".$idx."]" : $parent.="['att'][".$idx."]";
        if(isset($att['annotation'])) { $out[]="// ".$att['annotation']['documentation']; }

        // Attributes of attribute
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['default','fixed','form','id','name','ref','type','use'];
        foreach($types as $type)
        {
            if(isset($att['@'.$type])) { $attrs[]="'".$type."'=>'".$att['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    // SimpleType/complexType functions

    /**
     * function restriction (res)
     * @param Model $model
     * @param $idx
     * @param $res
     * @param $out
     * @param $parent
     * @return array
     */
    public function restriction(Model $model,$idx,$res,$out,$parent="")
    {
        ($parent=="") ? $parent="res[".$idx."]" : $parent.="['res'][".$idx."]";
        if(isset($res['annotation'])) { $out[]="// ".$res['annotation']['documentation']; }

        // Attributes of restriction
        $attrs=[];
        $str="\$".$parent."=["; // No [] as there can only be one restriction element
        $types=['base','id'];
        foreach($types as $type) {
            if(isset($res['@'.$type])) { $attrs[]="'".$type."'=>'".$res['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Enumerations?
        if(isset($res['enumeration'])) {
            if(!isset($res['enumeration'][0])) {
                $res['enumeration'] = [$res['enumeration']];
            }
            foreach ($res['enumeration'] as $idx=>$opt) { // Don't pass $idx as not needed for enumeration
                $out = $this->enumeration($model, $opt, $out, $parent);
            }
        }

        // Sequence?
        if(isset($res['sequence'])) {
            if(!isset($res['sequence'][0])) {
                $res['sequence'] = [$res['sequence']];
            }
            foreach ($res['sequence'] as $idx=>$seq) {
                $out = $this->sequence($model, $idx, $seq, $out, $parent);
            }
        }

        // MinLength?
        if(isset($res['minLength'])) {
            if(!isset($res['minLength'][0])) {
                $res['minLength'] = [$res['minLength']];
            }
            foreach ($res['minLength'] as $idx=>$mil) {
                $out = $this->minLength($model, $idx, $mil, $out, $parent);
            }
        }

        // MaxLength?
        if(isset($res['maxLength'])) {
            if(!isset($res['maxLength'][0])) {
                $res['maxLength'] = [$res['maxLength']];
            }
            foreach ($res['maxLength'] as $idx=>$mxl) {
                $out = $this->maxLength($model, $idx, $mxl, $out, $parent);
            }
        }

        // MinInclusive?
        if(isset($res['minInclusive'])) {
            if(!isset($res['minInclusive'][0])) {
                $res['minInclusive'] = [$res['minInclusive']];
            }
            foreach ($res['minInclusive'] as $idx=>$mii) {
                $out = $this->minInclusive($model, $idx, $mii, $out, $parent);
            }
        }

        return $out;
    }

    /**
     * function enumeration (opt)
     * @param Model $model
     * @param $opt
     * @param $out
     * @param $parent
     * @return mixed
     */
    public function enumeration(Model $model,$opt,$out,$parent="")
    {
        // Not using $idx in here becuase the array just needs to be the values
        ($parent=="") ? $parent="opt" : $parent.="['opt']";
        if(isset($opt['annotation'])) {
            $out[]="// ".$opt['annotation']['documentation'];
        }

        // Attributes of enumeration
        $attrs=[];
        $str="\$".$parent."[]=[";
        $types=['id','value'];
        foreach($types as $type) {
            if(isset($opt['@'.$type])) { $attrs[]="'".$opt['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function union (unn)
     * @param Model $model
     * @param $idx
     * @param $unn
     * @param $out
     * @param string $parent
     * @return array
     */
    public function union(Model $model,$idx,$unn,$out,$parent="")
    {
        ($parent=="") ? $parent="unn[".$idx."]" : $parent.="['unn'][".$idx."]";
        if(isset($unn['annotation'])) {
            $out[]="// ".$unn['annotation']['documentation'];
        }

        // Attributes of enumeration
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','memberTypes'];
        foreach($types as $type) {
            if(isset($unn['@'.$type])) {
                $attrs[]="'".$unn['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function minLength (mil)
     * @param Model $model
     * @param $idx
     * @param $mil
     * @param $out
     * @param string $parent
     * @return array
     */
    public function minLength(Model $model,$idx,$mil,$out,$parent="")
    {
        ($parent=="") ? $parent="mil[".$idx."]" : $parent.="['mil'][".$idx."]";
        if(isset($mil['annotation'])) {
            $out[]="// ".$mil['annotation']['documentation'];
        }

        // Attributes of minLength
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','fixed','value'];
        foreach($types as $type) {
            if(isset($mil['@'.$type])) {
                $attrs[]="'".$mil['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function maxLength (mxl)
     * @param Model $model
     * @param $idx
     * @param $mxl
     * @param $out
     * @param string $parent
     * @return array
     */
    public function maxLength(Model $model,$idx,$mxl,$out,$parent="")
    {
        ($parent=="") ? $parent="mxl[".$idx."]" : $parent.="['mxl'][".$idx."]";
        if(isset($mxl['annotation'])) {
            $out[]="// ".$mxl['annotation']['documentation'];
        }

        // Attributes of maxLength
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','fixed','value'];
        foreach($types as $type) {
            if(isset($mxl['@'.$type])) {
                $attrs[]="'".$mxl['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function minInclusive (mii)
     * minInclusive is an XML schema facet
     * http://www.w3.org/TR/xmlschema-2/#element-minInclusive
     * @param Model $model
     * @param $idx
     * @param $mii
     * @param $out
     * @param string $parent
     * @return array
     */
    public function minInclusive(Model $model,$idx,$mii,$out,$parent="")
    {
        ($parent=="") ? $parent="mii[".$idx."]" : $parent.="['mii'][".$idx."]";
        if(isset($mii['annotation'])) {
            $out[]="// ".$mii['annotation']['documentation'];
        }

        // Attributes of minInclusive
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','fixed','value'];
        foreach($types as $type) {
            if(isset($mii['@'.$type])) {
                $attrs[]="'".$mii['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    // Element functions

    /**
     * function key (key)
     * @param Model $model
     * @param $idx
     * @param $key
     * @param $out
     * @param $parent
     * @return array
     */
    public function key(Model $model,$idx,$key,$out,$parent="")
    {
        ($parent=="") ? $parent="key[".$idx."]" : $parent.="['key'][".$idx."]";
        if(isset($key['annotation'])) {
            $out[]="// ".$key['annotation']['documentation'];
        }

        // Attributes of key
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','name'];
        foreach($types as $type) {
            if(isset($key['@'.$type])) {
                $attrs[]="'".$type."'=>'".$key['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Selector?
        if(isset($seq['selector'])) {
            $out=$this->selector($model,$seq['selector'],$out,$parent);
        }

        // Field?
        if(isset($seq['field'])) {
            $out=$this->field($model,$seq['field'],$out,$parent);
        }

        return $out;
    }

    /**
     * function keyref (ref)
     * @param Model $model
     * @param $idx
     * @param $ref
     * @param $out
     * @param $parent
     * @return array
     */
    public function keyref(Model $model,$idx,$ref,$out,$parent="")
    {
        ($parent=="") ? $parent="ref[".$idx."]" : $parent.="['ref'][".$idx."]";
        if(isset($ref['annotation'])) {
            $out[]="// ".$ref['annotation']['documentation'];
        }

        // Attributes of keyref
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','name','refer'];
        foreach($types as $type) {
            if(isset($ref['@'.$type])) {
                $attrs[]="'".$type."'=>'".$ref['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Selector?
        if(isset($ref['selector'])) {
            $out=$this->selector($model,$ref['selector'],$out,$parent);
        }

        // Field?
        if(isset($ref['field'])) {
            $out=$this->field($model,$ref['field'],$out,$parent);
        }

        return $out;
    }

    /**
     * function unique (uni)
     * @param Model $model
     * @param $idx
     * @param $uni
     * @param $out
     * @param $parent
     * @return array
     */
    public function unique(Model $model,$idx,$uni,$out,$parent="")
    {
        ($parent=="") ? $parent="uni[".$idx."]" : $parent.="['uni'][".$idx."]";
        if(isset($uni['annotation'])) {
            $out[]="// ".$uni['annotation']['documentation'];
        }

        // Attributes of keyref
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','name'];
        foreach($types as $type) {
            if(isset($uni['@'.$type])) {
                $attrs[]="'".$type."'=>'".$uni['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Selector?
        if(isset($uni['selector'])) {
            $out=$this->selector($model,$uni['selector'],$out,$parent);
        }

        // Field?
        if(isset($uni['field'])) {
            $out=$this->field($model,$uni['field'],$out,$parent);
        }

        return $out;
    }

    /**
     * function selector (sel)
     * @param Model $model
     * @param $sel
     * @param $out
     * @param string $parent
     * @return array
     */
    public function selector(Model $model,$sel,$out,$parent="")
    {
        ($parent=="") ? $parent="sel" : $parent.="['sel']";
        if(isset($sel['annotation'])) {
            $out[]="// ".$sel['annotation']['documentation'];
        }

        // Attributes of enumeration
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','xpath'];
        foreach($types as $type) {
            if(isset($sel['@'.$type])) {
                $attrs[]="'".$type."'=>'".$sel['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function field (fld)
     * @param Model $model
     * @param $fld
     * @param $out
     * @param string $parent
     * @return array
     */
    public function field(Model $model,$fld,$out,$parent="")
    {
        ($parent=="") ? $parent="fld" : $parent.="['fld']";
        if(isset($fld['annotation'])) {
            $out[]="// ".$fld['annotation']['documentation'];
        }

        // Attributes of enumeration
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','xpath'];
        foreach($types as $type) {
            if(isset($fld['@'.$type])) {
                $attrs[]="'".$type."'=>'".$fld['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    // ComplexType functions

    /**
     * function sequence (seq)
     * @param Model $model
     * @param $idx
     * @param $seq
     * @param $out
     * @param $parent
     * @return array
     */
    public function sequence(Model $model,$idx,$seq,$out,$parent="")
    {
        ($parent=="") ? $parent="seq[".$idx."]" : $parent.="['seq'][".$idx."]";
        if(isset($seq['annotation'])) {
            $out[]="// ".$seq['annotation']['documentation'];
        }

        // Attributes of sequence
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','minOccurs','maxOccurs'];
        foreach($types as $type) {
            if(isset($seq['@'.$type])) { $attrs[]="'".$type."'=>'".$seq['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Elements?
        if(isset($seq['element'])) {
            if(!isset($seq['element'][0])) {
                $seq['element'] = [$seq['element']];
            }
            foreach ($seq['element'] as $idx=>$ele) {
                $out=$this->element($model,$idx,$ele,$out,$parent);
            }
        }

        // Choice
        if(isset($seq['choice'])) {
            if(!isset($seq['choice'][0])) {
                $seq['choice'] = [$seq['choice']];
            }
            foreach ($seq['choice'] as $idx=>$cho) {
                $out=$this->choice($model,$idx,$cho,$out,$parent);
            }
        }

        return $out;
    }

    /**
     * function choice (cho)
     * @param Model $model
     * @param $idx
     * @param $cho
     * @param $out
     * @param $parent
     * @return array
     */
    public function choice(Model $model,$idx,$cho,$out,$parent="")
    {
        ($parent=="") ? $parent="cho[".$idx."]" : $parent.="['cho'][".$idx."]";
        if(isset($cho['annotation'])) {
            $out[]="// ".$cho['annotation']['documentation'];
        }

        // Attributes of element
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','minOccurs','maxOccurs'];
        foreach($types as $type) {
            if(isset($cho['@'.$type])) {
                $attrs[]="'".$type."'=>'".$cho['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Elements?
        if(isset($cho['element'])) {
            if(!isset($cho['element'][0])) {
                $cho['element'] = [$cho['element']];
            }
            foreach ($cho['element'] as $idx=>$ele) {
                $out=$this->element($model,$idx,$ele,$out,$parent);
            }
        }

        return $out;
    }

    /**
     * function element (ele)
     * @param Model $model
     * @param $idx
     * @param $ele
     * @param $out
     * @param $parent
     * @return array
     */
    public function element(Model $model,$idx,$ele,$out,$parent="")
    {
        ($parent=="") ? $parent="ele[".$idx."]" : $parent.="['ele'][".$idx."]";
        if(isset($ele['annotation'])) {
            $out[]="// ".$ele['annotation']['documentation'];
        }

        // Attributes of element
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['block','default','fixed','form','id','minOccurs','maxOccurs','name','nillable','ref','type'];
        foreach($types as $type) {
            if(isset($ele['@'.$type])) {
                $attrs[]="'".$type."'=>'".$ele['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

    /**
     * function simpleContent (sim)
     * @param Model $model
     * @param $idx
     * @param $sim
     * @param $out
     * @param $parent
     * @return array
     */
    public function simpleContent(Model $model,$idx,$sim,$out,$parent="")
    {
        ($parent=="") ? $parent="sim[".$idx."]" : $parent.="['sim'][".$idx."]";
        if(isset($sim['annotation'])) { $out[]="// ".$sim['annotation']['documentation']; }

        // Attributes of element
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id'];
        foreach($types as $type) {
            if(isset($sim['@'.$type])) {
                $attrs[]="'".$type."'=>'".$sim['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Extension?
        if(isset($sim['extension'])) {
            if(!isset($sim['extension'][0])) { $sim['extension'] = [$sim['extension']]; }
            foreach ($sim['extension'] as $idx=>$ext) {
                $out=$this->extension($model,$idx,$ext,$out,$parent);
            }
        }

        return $out;
    }

    /**
     * function complexContent (con)
     * @param Model $model
     * @param $idx
     * @param $con
     * @param $out
     * @param $parent
     * @return array
     */
    public function complexContent(Model $model,$idx,$con,$out,$parent="")
    {
        ($parent=="") ? $parent="con[".$idx."]" : $parent.="['con'][".$idx."]";
        if(isset($con['annotation'])) {
            $out[]="// ".$con['annotation']['documentation'];
        }

        // Attributes of element
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','mixed'];
        foreach($types as $type)
        {
            if(isset($con['@'.$type])) { $attrs[]="'".$type."'=>'".$con['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // Extension?
        if(isset($con['extension'])) {
            if(!isset($con['extension'][0])) {
                $con['extension'] = [$con['extension']];
            }
            foreach ($con['extension'] as $idx=>$ext) {
                $out=$this->extension($model,$idx,$ext,$out,$parent);
            }
        }

        // Restriction?
        if(isset($con['restriction'])) {
            if(!isset($con['restriction'][0])) {
                $con['restriction'] = [$con['restriction']];
            }
            foreach ($con['restriction'] as $idx=>$res) {
                $out=$this->restriction($model,$idx,$res,$out,$parent);
            }
        }
        return $out;
    }

    /**
     * function extension (ext)
     * @param Model $model
     * @param $idx
     * @param $ext
     * @param $out
     * @param $parent
     * @return array
     */
    public function extension(Model $model,$idx,$ext,$out,$parent="")
    {
        ($parent=="") ? $parent="ext[".$idx."]" : $parent.="['ext'][".$idx."]";
        if(isset($ext['annotation'])) {
            $out[]="// ".$ext['annotation']['documentation'];
        }

        // Attributes of element
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['base','id'];
        foreach($types as $type)
        {
            if(isset($ext['@'.$type])) {
                $attrs[]="'".$type."'=>'".$ext['@'.$type]."'";
            }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        // AttributeGroup?
        if(isset($ext['attributeGroup'])) {
            if(!isset($ext['attributeGroup'][0])) {
                $ext['attributeGroup'] = [$ext['attributeGroup']];
            }
            foreach ($ext['attributeGroup'] as $idx=>$grp) {
                $out=$this->attributeGroup($model,$idx,$grp,$out,$parent);
            }
        }

        return $out;
    }

    /**
     * function attributeGroup (grp)
     * @param Model $model
     * @param $idx
     * @param $grp
     * @param $out
     * @param $parent
     * @return array
     */
    public function attributeGroup(Model $model,$idx,$grp,$out,$parent="")
    {
        ($parent=="") ? $parent="grp[".$idx."]" : $parent.="['grp'][".$idx."]";
        if(isset($grp['annotation'])) {
            $out[]="// ".$grp['annotation']['documentation'];
        }

        // Attributes of attribute
        $attrs=[];
        $str="\$".$parent."=[";
        $types=['id','ref'];
        foreach($types as $type) {
            if(isset($grp['@'.$type])) { $attrs[]="'".$type."'=>'".$grp['@'.$type]."'"; }
        }
        $str.=implode(",",$attrs);
        $str.="];";
        $out[]=$str;

        return $out;
    }

}