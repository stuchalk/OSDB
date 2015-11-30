<?php
App::uses('Component','Controller');

/**
 * Class ExportComponent
 * Functions for exporting data
 */
class ExportComponent extends Component
{
    /**
     * Export the data as XML
     * This function is not the best way to do this but it works
     * Maximum number of nested levels is five - after that it will create bad XML
     * http://www.w3.org/XML/
     * @param string $name
     * @param string $table
     * @param array $data
     */
    public function xml($name="",$table="",$data=[])
    {
        if(empty($data) || $table=="") { return; }
        if(empty($name)) { $name=$table; }
        $e='item';
        $output="<?xml version='1.0' encoding='UTF-8' ?>\n";
        $output.="<".$table.">\n";
        //debug($data);exit;
        foreach($data as $k1=>$v1)
        {
            if(is_numeric($k1)):    $output.="<".$e." id='".$k1."'>";
            else:                   $output.="<".$k1.">";
            endif;
            if(is_array($v1))
            {
                foreach($v1 as $k2=>$v2)
                {
                    if(is_numeric($k2)):    $output.="<".$e." id='".$k2."'>";
                    else:                   $output.="<".$k2.">";
                    endif;
                    if(is_array($v2))
                    {
                        foreach($v2 as $k3=>$v3)
                        {
                            if(is_numeric($k3)):    $output.="<".$e." id='".$k3."'>";
                            else:                   $output.="<".$k3.">";
                            endif;
                            if(is_array($v3))
                            {
                                foreach ($v3 as $k4=>$v4)
                                {
                                    if(is_numeric($k4)):    $output.="<".$e." id='".$k4."'>";
                                    else:                   $output.="<".$k4.">";
                                    endif;
                                    if(is_array($v4))
                                    {
                                        foreach($v4 as $k5=>$v5)
                                        {
                                            if(is_numeric($k5)):    $output.="<".$e." id='".$k5."'>";
                                            else:                   $output.="<".$k5.">";
                                            endif;
                                            if(is_array($v5))
                                            {
                                                foreach ($v5 as $k6 => $v6)
                                                {
                                                    if (is_numeric($k6)): $output .= "<".$e." id='".$k6."'>";
                                                    else:                 $output .= "<".$k6.">";
                                                    endif;
                                                    if(is_array($v6))
                                                    {
                                                        foreach ($v6 as $k7 => $v7)
                                                        {
                                                            if (is_numeric($k7)): $output .= "<".$e." id='".$k7."'>";
                                                            else:                 $output .= "<".$k7.">";
                                                            endif;
                                                            if(is_array($v7))
                                                            {
                                                                foreach ($v7 as $k8 => $v8)
                                                                {
                                                                    if (is_numeric($k8)): $output .= "<".$e." id='".$k8."'>";
                                                                    else:                 $output .= "<".$k8.">".$v8."</".$k8.">";
                                                                    endif;
                                                                }
                                                            }
                                                            else
                                                            {
                                                                $output.=$v7;
                                                            }
                                                            if(is_numeric($k7)):    $output.="</".$e.">";
                                                            else:                   $output.="</".$k7.">";
                                                            endif;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $output.=$v6;
                                                    }
                                                    if(is_numeric($k6)):    $output.="</".$e.">";
                                                    else:                   $output.="</".$k6.">";
                                                    endif;
                                                }
                                            }
                                            else
                                            {
                                                $output.=$v5;
                                            }
                                            if(is_numeric($k5)):    $output.="</".$e.">";
                                            else:                   $output.="</".$k5.">";
                                            endif;
                                        }
                                    }
                                    else
                                    {
                                        $output.=$v4;
                                    }
                                    if(is_numeric($k4)):    $output.="</".$e.">";
                                    else:                   $output.="</".$k4.">";
                                    endif;
                                }
                            }
                            else
                            {
                                $output.=$v3;
                            }
                            if(is_numeric($k3)):    $output.="</".$e.">";
                            else:                   $output.="</".$k3.">";
                            endif;
                        }
                    }
                    else
                    {
                        $output.=$v2;
                    }
                    if(is_numeric($k2)):    $output.="</".$e.">";
                    else:                   $output.="</".$k2.">";
                    endif;
                }
            }
            else
            {
                $output.=$v1;
            }
            if(is_numeric($k1)):    $output.="</".$e.">";
            else:                   $output.="</".$k1.">";
            endif;
        }
        $output.="</".$table.">";
        // Encode & as entity
        $output=str_replace("&","&amp;",$output);
        // Output
        header('Content-type: text/xml');
        header('Content-Disposition: inline; filename="'.strtolower($name).'.xml"');
        echo $output;exit;
    }

    /**
     * Export the data as JSON
     * http://www.json.org/
     * @param string $name
     * @param string $table
     * @param array $data
     */
    public function json($name="",$table="",$data=[]) {
        if(empty($data)) { return; }
        if(empty($name)) { $name=$table; }
        $output=json_encode($data);
        // Output
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="'.strtolower($name).'.json"');
        echo $output;exit;
    }

    /**
     * Export the data as JSON-LD
     * http://www.w3.org/TR/json-ld/
     * @param string $name
     * @param string $table
     * @param array $data
     * @param array $context
     */
    public function jsonld($name="",$table="",$data=[],$context=[])
    {
        if(empty($data) || empty($context)) { return; }
        if(empty($name)) { $name=$table; }
        $input=['@context'=>$context]+$data;
        $output=json_encode($input);
        // Output
        header('Content-type: application/ld+json');
        header('Content-Disposition: inline; filename="'.strtolower($name).'.jsonld"');
        echo $output;exit;

    }
}