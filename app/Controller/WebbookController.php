<?php

/**
 * Class WebbookController
 * Getting data from the NIST webbook
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class WebbookController extends AppController
{

    public $uses=['Unit','Webbook','Pubchem.Chemical'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Retrieve spectra for the NIST webbook
     * @param $type
     */
    public function ingest($type)
    {
        if($type=='uv') {
            $typestr="UVVis";
        } elseif($type=="ms") {
            $typestr="Mass";
        } elseif($type=="ir") {
            $typestr="IR";
        }
        $cmpds=$this->Webbook->find('list',['fields'=>['id','name'],'conditions'=>[$type=>1]]);

        foreach ($cmpds as $id=>$name) {
            $url = "http://webbook.nist.gov/cgi/cbook.cgi?Name=" . urlencode($name) . "&Units=SI&c".strtoupper($type)."=on";
            //debug($url);
            $page=file_get_contents($url);
            preg_match('/\/cgi\/cbook\.cgi\?JCAMP=[A-Z][0-9]*&amp;Index=[0-9]&amp;Type='.$typestr.'/',$page,$matches);
            $count=$hardcopy=0;
            if(!empty($matches)) {
                foreach($matches as $match) {
                    $count++;
                    preg_match("/Index=([0-9])&amp;/",$match,$index);
                    $filename=$id."_".$type."_".$index[1].".jdx";
                    $path=WWW_ROOT."files".DS.$type.DS.$filename;
                    if(file_exists($path)) {
                        echo "File ".$filename." already saved<br />";
                    } else {
                        $specurl="http://webbook.nist.gov".str_replace("&amp;","&",$match);
                        $text=file_get_contents($specurl);
                        $fh=fopen($path,"w");
                        fwrite($fh,$text);
                        fclose($fh);
                        if(file_exists($path)) {
                            echo "File ".$filename." saved ".filesize($path)." bytes<br />";
                            chmod($path,0777);
                        } else {
                            echo "Error writing file ".$filename."<br />";
                        }
                    }
                }
            } else {
                // There are only multiple IR spectra
                if(stristr($page,'A digitized version of this spectrum is not currently available.')) {
                    $hardcopy++;
                    echo "Only hardcopy IR spectrum for ".$name."<br />";
                } else {
                    preg_match_all('/\/cgi\/cbook\.cgi\?ID=[A-Z][0-9]*&amp;Units=SI&amp;Type=IR-SPEC&amp;Index=[0-9]{1,2}#IR-SPEC/',$page,$matches);
                    foreach($matches[0] as $match) {
                        $url2 = "http://webbook.nist.gov".urldecode($match);
                        $page2=file_get_contents($url2);
                        $regex='/\/cgi\/cbook\.cgi\?JCAMP=[A-Z][0-9]*&amp;Index=[0-9]*&amp;Type='.$typestr.'/';
                        preg_match($regex,$page2,$matches2);
                        if(!empty($matches2)) {
                            foreach($matches2 as $match2) {
                                $count++;
                                preg_match("/Index=([0-9]*)&amp;/",$match2,$index2);
                                $filename=$id."_".$type."_".$index2[1].".jdx";
                                $path=WWW_ROOT."files".DS.$type.DS.$filename;
                                if(file_exists($path)) {
                                    echo "File ".$filename." already saved<br />";
                                } else {
                                    $specurl="http://webbook.nist.gov".str_replace("&amp;","&",$match2);
                                    //debug($specurl);
                                    $text=file_get_contents($specurl);
                                    $fh=fopen($path,"w");
                                    fwrite($fh,$text);
                                    fclose($fh);
                                    if(file_exists($path)) {
                                        echo "File ".$filename." saved ".filesize($path)." bytes<br />";
                                        chmod($path,0777);
                                    } else {
                                        echo "Error writing file ".$filename."<br />";
                                    }
                                }
                            }
                        } else {
                            if(stristr($page2,'A digitized version of this spectrum is not currently available.')) {
                                $hardcopy++;
                                echo "Only hardcopy IR spectrum for ".$name."<br />";
                            } else {
                                echo "Unknown error for ".$name."<br />";exit;
                            }
                        }
                    }
                }
            }
            $this->Webbook->id=$id;
            if($count==0&&$hardcopy==0) {
                $this->Webbook->save(['Webbook'=>['manyir'=>'no','ircount'=>0,'ir'=>0]]);
            } elseif($count==0&&$hardcopy>0) {
                $this->Webbook->save(['Webbook'=>['manyir'=>'hardcopy','ircount'=>$hardcopy,'ir'=>1]]);
            } elseif($count==1) {
                $this->Webbook->save(['Webbook'=>['manyir'=>'no','ircount'=>1,'ir'=>1]]);
            } else {
                $this->Webbook->save(['Webbook'=>['manyir'=>'yes','ircount'=>$count,'ir'=>1]]);
            }
        }
        exit;
    }

    /**
     * List of all entries for each compounds in the table
     */
    public function index()
    {
        $cmpds=$this->Webbook->find('list',['fields'=>['id','name'],'conditions'=>['nistid'=>null,'or'=>[['ir'=>true],['ms'=>true],['uv'=>true]]]]);
        $log = $this->Webbook->getDataSource()->getLog(false, false);
        //debug($log);debug($cmpds);exit;
        foreach ($cmpds as $id=>$name) {
            $url="http://webbook.nist.gov/cgi/cbook.cgi?Name=".urlencode($name)."&Units=SI";
            $test=get_headers($url);$meta=[];
            if(stristr($test[0],"OK")) {
                $page=file_get_contents($url);
                if(preg_match("/GetInChI=[A-Z][0-9]*/",$page,$matches)) {
                    $meta['nistid']=str_replace("GetInChI=","",$matches[0]);
                } elseif(preg_match("/cgi\?ID=[A-Z][0-9]*/",$page,$matches)) {
                    $meta['nistid']=str_replace("cgi?ID=","",$matches[0]);
                } else {
                    $meta['nistid']=null;
                }
                $irstr="Mask=80#IR-Spec";
                if(preg_match("/".$irstr."/",$page,$matches)) {
                    $meta['ir']=1;
                } else {
                    $meta['ir']=0;
                }
                $msstr="Mask=200#Mass-Spec";
                if(preg_match("/".$msstr."/",$page,$matches)) {
                    $meta['ms']=1;
                } else {
                    $meta['ms']=0;
                }
                $uvstr="Mask=400#UV-Vis-Spec";
                if(preg_match("/".$uvstr."/",$page,$matches)) {
                    $meta['uv']=1;
                } else {
                    $meta['uv']=0;
                }
                $meta['checked']='yes';
                $this->Webbook->id=$id;
                $this->Webbook->save(['Webbook'=>$meta]);
                echo "'".$name."' updated<br />";
            } else {
                echo "Page not found for '".$name."'<br />";
            }
        }
        //debug($cmpds);
        exit;
    }

    /**
     * Get cas # for compound
     */
    public function getcas()
    {
        $cond=['cas'=>null,'or'=>[['ir'=>true],['ms'=>true],['uv'=>true]]];
        $cmpds=$this->Webbook->find('list',['fields'=>['id','name'],'conditions'=>$cond]);
        foreach ($cmpds as $id=>$name) {
            $url = "https://cactus.nci.nih.gov/chemical/structure/" . urlencode($name) . "/cas";
            $test = get_headers($url);
            if (stristr($test[0], "OK")) {
                $cas=file_get_contents($url);
                if(!stristr($cas,'not found')) {
                    $this->Webbook->id=$id;
                    $this->Webbook->save(['Webbook'=>['cas'=>$cas,'comments'=>'CAS from CIR']]);
                    echo "'".$name."' updated<br />";
                } else {
                    echo "Page not found for '".$name."'<br />";
                }
                exit;
            } else {
                // Try the name in PubChem
                $cid=$this->Chemical->cid($name);
                if(!$cid) {
                    // Grab formula to use instead
                    $form=$this->Webbook->find('first',['conditions'=>['id'=>$id],'fields'=>['formula']]);
                    $cids=$this->Chemical->formula($form['Webbook']['formula'],true);
                    $cid=$cids[0]; //top hit
                    $comment='CAS from formula (first cid) on PubChem';
                } else {
                    $comment='CAS from name on PubChem';
                }
                // Get synonyms of first compound
                $syns=$this->Chemical->synonyms($cid);
                // Find CAS#
                $cas="";
                foreach($syns as $syn) {
                    if(preg_match("/[0-9]{2,7}-[0-9]{2}-[0-9]/",$syn,$matches)) {
                        $cas=$matches[0];
                        break;
                    }
                }
                if($cas!="") {
                    $this->Webbook->id=$id;
                    $this->Webbook->save(['Webbook'=>['cas'=>$cas,'comments'=>$comment]]);
                    echo "'".$name."' updated<br />";
                } else {
                    $this->Webbook->id=$id;
                    $this->Webbook->save(['Webbook'=>['cas'=>'NA']]);
                    echo "No CAS found for '".$name."'<br />";
                }
                //debug($cas);exit;
            }
        }
        exit;
    }

    /**
     * Add multiple spectra using curl and form
     * @param $type
     * @param $o
     * @param $l
     */
    public function formadd($type="uv",$o=0,$l=5)
    {
        $files=$this->Webbook->find('all',['conditions'=>[$type=>1],'offset'=>$o,'limit'=>$l]);
        //debug($files);exit;
        // Create path to file (filename format is id_uv_0.jdx)
        foreach($files as $file) {
            $f=$file['Webbook'];
            $fpath=WWW_ROOT."files".DS."uv".DS.$f['id']."_uv_0.jdx";
            //debug($fpath);
            $response=[];
            if(file_exists($fpath)) {
                $curl='curl -u stuchalk:calvin42 -F "data[File][user_id]=1" -F "data[File][substance]='.$f['name'].'" -F "data[File][substance_id]=" -F "data[Collection][id]=2" -F "data[File][url]='.$f['url'].'" -F "data[File][file][]=@'.$fpath.'" -k https://sds.coas.unf.edu/osdb/files/add/yes';
                exec($curl,$response,$status);
                //debug($curl);debug($response);debug($status);exit;
                echo $response[0]."<br />";
                //debug($curl);debug($status);
            }
        }
        exit;
    }
}


