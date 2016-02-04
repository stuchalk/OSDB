<?php

class ScidataBehavior extends ModelBehavior
{
    /**
     * Puts a number in scientific notation
     * @param Model $Model
     * @param string $float
     * @param array $params
     * @return array
     */
    public function scinot(Model $Model,$float="",$params=[])
    {
        // Check parameters
        if($float=="") { return 'No number given'; }

        // Convert to exponential notation
        $exponent = floor(abs($float) == 0 ? 0 : log10(abs($float)));
        $mantissa = $float*pow(10,-$exponent);
        if(strstr($float,'e')):		list($man,)=explode("e",$float);
            $sigfigs=strlen(str_replace(".","",$man));
        elseif(strstr($float,'E')):	list($man,)=explode("E",$float);
            $sigfigs=strlen(str_replace(".","",$man));
        elseif(!strstr($float,'.')):	$sigfigs=strlen($float);
        else:						    $sigfigs=strlen(str_replace(".","",$mantissa));
        endif;
        return ['m'=>$mantissa,'e'=>$exponent,'s'=>$sigfigs];
    }

}