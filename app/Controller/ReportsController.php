<?php

/**
 * Class ReportController
 * Actions related to reports (spectra)
 * @author Stuart Chalk <schalk@unf.edu>
 */
class ReportsController extends AppController
{
    public $uses=['Report','Substance','Identifier','Technique'];

     /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('view','scidata','recent','latest','plot','index');
    }

    /**
     * List the reports
     * @param integer $offset
     * @param integer $limit
     */
    public function index($offset=0,$limit=6)
    {
        $data=$this->Report->bySubstance(['conditions'=>['count >'=>0]],null,$offset,$limit);
        // Limit the amount of data return base on offset and limit (needs to be redone using paginator)
        $slice=array_slice($data,$offset,$limit);
        $this->set('count',count($data));
        $this->set('offset',$offset);
        $this->set('limit',$limit);
        $this->set('data',$slice);
    }

    /**
     * Add a new report
     */
    public function add()
    {
        if(!empty($this->request->data)) {
            $this->Report->create();
            $this->Report->save($this->request->data);
            $this->redirect('/properties');
        } else {
            $data=$this->Publication->find('list',['fields'=>['id','name'],'order'=>['name']]);
            $this->set('data',$data);
        }
    }

    /**
     * View a report
     * @param mixed $id
     * @param string $tech
     * @param string $format
     */
    public function view($id,$tech="",$format="")
    {
        $error = "";
        if(!is_numeric($id)) {
            // Get the report id is one exists for this chemical and technique
            $sid = $tid = $error = "";
            // Get the sid if there is one
            $data = $this->Substance->find('first', ['conditions' => ['name' => $id], 'recursive' => -1]);
            if (empty($data)) {
                $data = $this->Identifier->find('first', ['conditions' => ['value' => $id], 'recursive' => -1]);
                if (empty($data)) {
                    $error='Compound not found (' . $id . ')';
                } else {
                    $sid = $data['Identifier']['substance_id'];
                }
            } else {
                $sid = $data['Substance']['id'];
            }
            // Get the tid if there is one
            $techs = Configure::read('tech.types');
            if (!in_array($tech, $techs)) {
                $error='Invalid OSDB technique code (' . $tech . ')';
            } else {
                $data = $this->Technique->find('first', ['conditions' => ['matchstr' => $tech]]);
                $tid = $data['Technique']['id'];
            }
            // Find the report if one exists
            if($sid!=""&&$tid!="") {
                $report = $this->Report->find('first', ['conditions' => ['analyte_id' => $sid, 'technique_id' => $tid],'recursive'=>-1]);
                if (empty($report)) {
                    $error='There is no ' . $tech . ' spectrum for ' . $id . ' currently in the OSDB.';
                } else {
                    $id = $report['Report']['id'];
                }
            }
        }
        //echo $sid." : ".$tid." : ".$id;exit;
        if($error!="") {
            if($format==""||$format=="JCAMP") {
                exit($error);
            } elseif($format=="XML") {
                $this->Export->xml('osdb','spectra',['error'=>$error]);
            } elseif($format=="JSON"||$format=="JSONLD") {
                $this->Export->json('osdb','spectra',['error'=>$error]);
            }
        }

        // Check the download formats
        $osdbpath=Configure::read('url');
        if($format=="JCAMP") {
            header("Location: ".$osdbpath."/download/jdx/".str_pad($id,9,0,STR_PAD_LEFT).".jdx");exit;
        } elseif($format=="XML") {
            header("Location: ".$osdbpath."/download/xml/".str_pad($id,9,0,STR_PAD_LEFT).".xml");exit;
        } elseif($format=="JSONLD") {
            header("Location: ".$osdbpath."/spectra/scidata/".str_pad($id,9,0,STR_PAD_LEFT));exit;
        }

        //Process
        $data=$this->Report->scidata($id);
        if(empty($data)) {
            $this->redirect('/pages/error');
        }
        $rpt=$data['Report'];
        $set=$data['Dataset'];
        $file=$set['File'];
        $met=$set['Methodology'];
        $mea=$met['Measurement'];
        $sam=$set['Sample'];
        $ser=$set['Dataseries'];

        // Send on only the data needed for the view not the flot plot which is done separately via an element
        // Measurement data...
        $minfo=[];
        $minfo[]="Instrument Type: ".$mea['instrumentType'];
        $meta=['instrument','vendor'];
        foreach($meta as $m) {
            if(!empty(str_replace("?","",$mea[$m]))) { $minfo[]=ucfirst($m).': '.$mea[$m]; }
        }

        if(!empty($mea['Setting'])) {
            $sets=$mea['Setting'];
            //debug($sets);exit;
            foreach($sets as $set) {
                if(isset($set['Property']['name'])) {
                    (empty($set['text'])) ? $value = $set['number'] : $value = $set['text']; // So that zeroes are not lost
                    $name = $set['Property']['name'];
                    (!empty($set['Unit'])) ? $unit = " " . $set['Unit']['symbol'] : $unit = "";
                    $minfo[] = $name . ": " . $value . $unit;
                }
            }
        }

        // Sample data...
        $sinfo=[];
        if(isset($sam['title'])&&!empty($sam['title'])) {
            $sinfo[]=$sam['title'];
            if(!empty($sam['Annotation'])) {
                $sinfo['comments']=[];
                foreach($sam['Annotation'] as $a) {
                    $sinfo['comments'][]=$a['comment'];
                }
            }
        }

        $freq = 1; // Default...
        if(!empty($mea['Setting'])) {
            $sets = $mea['Setting'];
            foreach ($sets as $set) {
                if(isset($set['Property']['name'])) {
                    (empty($set['text'])) ? $value = $set['number'] : $value = $set['text']; // So that zeroes are not lost
                    if ($set['Property']['name'] == "Observe Frequency") {
                        $freq = $value;
                    }
                }
            }
        }

        // File, Spectral Data, and Conversion data
        if(count($ser)==1) {
            $finfo=[];$dinfo=[];$cinfo=[];$scale = 1;
            $spec = $ser[0];
            if (isset($spec['Annotation'])) {
                foreach ($spec['Annotation'] as $ann) {
                    if ($ann['class'] == 'origin') {
                        foreach ($ann['Metadata'] as $m) {
                            if ($m['field'] == "fileComments") {
                                $cinfo['comments'] = $m['value'];
                            } elseif ($m['field'] == "conversionErrors") {
                                $cinfo['errors'] = $m['value'];
                            } elseif ($m['field'] == "date") {
                                $finfo[] = ucfirst($m['field']) . ": " . date("M j, Y", strtotime($m['value']));
                            } else {
                                $finfo[] = ucfirst($m['field']) . ": " . $m['value'];
                            }
                        }
                    }
                }
            }

            if (!empty($spec['Descriptor'])) {
                if ($spec['level'] == 'processed' && $spec['processedType'] == "frequency") {
                    $scale = $freq;
                } elseif ($spec['level'] == 'processed' && $spec['processedType'] == "chemical shift") {
                    $scale = 1;
                }
                foreach ($spec['Descriptor'] as $d) {
                    $value = 0;
                    (empty($d['text'])) ? $value = (float)$d['number'] : $value = $d['text']; // So that zeroes are not lost
                    if (stristr($d['title'], "maximum x")) {
                        $value = number_format(round($value / $scale), 0);
                    } elseif (stristr($d['title'], "minimum x")) {
                        $value = number_format(round($value / $scale), 0);
                    } elseif (stristr($d['title'], "increment")) {
                        $value = number_format($value / $scale, 5);
                    } elseif (stristr($d['title'], "first x")) {
                        $value = number_format($value / $scale, 0);
                    } elseif (stristr($d['title'], "last x")) {
                        $value = number_format($value / $scale, 0);
                    } elseif (stristr($d['title'], "first y")) {
                        $value = number_format($value, 0);
                    } elseif (stristr($d['title'], "maximum y")) {
                        $value = number_format($value, 0);
                    } elseif (stristr($d['title'], "minimum y")) {
                        $value = number_format($value, 0);
                    }
                    $dinfo[]=ucfirst($d['title']) . ": " . $value;
                }
            }
        }
        $this->set('desc',$rpt['description']);
        $this->set('minfo',$minfo);
        $this->set('sinfo',$sinfo);
        $this->set('finfo',$finfo);
        $this->set('dinfo',$dinfo);
        $this->set('cinfo',$cinfo);
        $this->set('fileid',$file['id']);
        $this->set('id',$id);
    }

    /**
     * View the plot of a spectrum
     * @param mixed $id
     * @param string $tech
     * @param integer $w
     * @param integer $h
     * @param boolean $embed
     */
    public function plot($id,$tech="",$w=720,$h=540,$embed=false)
    {
        $error = "";
        if(!is_numeric($id)) {
            // Get the report id is one exists for this chemical and technique
            $sid = $tid = "";
            // Get the sid if there is one
            $data = $this->Substance->find('first', ['conditions' => ['name' => $id], 'recursive' => -1]);
            if (empty($data)) {
                $data = $this->Identifier->find('first', ['conditions' => ['value' => $id], 'recursive' => -1]);
                if (empty($data)) {
                    $error='Compound not found (' . $id . ')';
                } else {
                    $sid = $data['Identifier']['substance_id'];
                }
            } else {
                $sid = $data['Substance']['id'];
            }
            // Get the tid if there is one
            $techs = Configure::read('tech.types');
            if (!in_array($tech, $techs)) {
                $error='Invalid OSDB technique code (' . $tech . ')';
            } else {
                $data = $this->Technique->find('first', ['conditions' => ['matchstr' => $tech]]);
                $tid = $data['Technique']['id'];
            }
            // Find the report if one exists
            if($sid!=""&&$tid!="") {
                $report = $this->Report->find('first', ['conditions' => ['analyte_id' => $sid, 'technique_id' => $tid],'recursive'=>-1]);
                if (empty($report)) {
                    $error='There is no ' . $tech . ' spectrum for ' . $id . ' currently in the OSDB.';
                } else {
                    $id = $report['Report']['id'];
                }
            }
        }
        //echo $sid." : ".$tid." : ".$id;exit;
        if($error!="") {
            exit($error);
        }

        $data=$this->Report->scidata($id);
        $rpt=$data['Report'];
        $set=$data['Dataset'];
        $file=$set['File'];
        $usr=$data['User'];
        $met=$set['Methodology'];
        $mea=$met['Measurement'];
        $con=$set['Context'];
        $sys=$con['System'];
        $sam=$set['Sample'];
        $ser=$set['Dataseries'];

        // Plot (flot) data...
        $flot=[];
        if($mea['technique']=="Mass Spectrometry") {
            $flot['tech']="ms";
        } elseif($mea['technique']=="Nuclear Magnetic Resonance") {
            $flot['tech']='nmr';
        } elseif($mea['technique']=="Infrared Spectroscopy") {
            $flot['tech']='ir';
        }

        if(!empty($mea['Setting'])) {
            $sets=$mea['Setting'];
            foreach($sets as $set) {
                (empty($set['text'])) ? $value=$set['number'] : $value=$set['text']; // So that zeroes are not lost
                if(!isset($set['Property']['name'])) { continue; }
                if($set['Property']['name']=="Observe Frequency")  { $flot['freq']=$value; }
                if($set['Property']['name']=="Observe Nucleus")    { $flot['nuc']=$value; }
            }
        }

        if(count($ser)==1) {
            $finfo=[];$dinfo=[];$cinfo=[];$spec = $ser[0];$flot['scale'] = 1;
            if (!empty($spec['Descriptor'])) {
                if ($spec['level'] == 'processed' && $spec['processedType'] == "frequency") {
                    $flot['scale'] = $flot['freq'];
                } elseif ($spec['level'] == 'processed' && $spec['processedType'] == "chemical shift") {
                    $flot['scale'] = 1;
                } elseif ($spec['level'] == 'processed' && $spec['processedType'] == "transmittance") {
                    $flot['scale'] = 1;
                    $flot['ylabel'] = "Transmittance (%T)";
                } elseif ($spec['level'] == 'processed' && $spec['processedType'] == "absorbance") {
                    $flot['scale'] = 1;
                    $flot['ylabel'] = "Absorbance";
                }
                foreach ($spec['Descriptor'] as $d) {
                    $value = 0;
                    (empty($d['text'])) ? $value = (float)$d['number'] : $value = $d['text']; // So that zeroes are not lost
                    if (stristr($d['title'], "points")) {
                        $flot['points'] = $value;
                    } elseif (stristr($d['title'], "maximum x")) {
                        $flot['maxx'] = $value / $flot['scale'];
                    } elseif (stristr($d['title'], "minimum x")) {
                        $flot['minx'] = $value / $flot['scale'];
                    } elseif (stristr($d['title'], "minimum y")) {
                        $flot['miny'] = $value;
                    } elseif (stristr($d['title'], "first x")) {
                        $flot['firstx'] = $value / $flot['scale'];
                    } elseif (stristr($d['title'], "last x")) {
                        $flot['lastx'] = $value / $flot['scale'];
                    }
                }
            }
            $flot['xsid']=$spec['Datapoint'][0]['Condition'][0]['id'];
            $flot['ysid']=$spec['Datapoint'][0]['Data'][0]['id'];
        }

        $this->set('flot',$flot);
        $this->set('w',$w);
        $this->set('h',$h);
        if($embed) {
            $this->layout='ajax';
        }
    }

    /**
     * Update a report
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            $this->Report->id=$id;
            $this->Report->save($this->request->data);
            $this->redirect('/properties/view/'.$id);
        } else {
            $data=$this->Report->find('first',['conditions'=>['Report.id'=>$id]]);
            $this->set('data',$data);
        }
    }

    /**
     * Get the five most recently uploaded spectra (reports)
     */
    public function recent()
    {
        $data=$this->Report->find('list',['order'=>['updated'=>'desc'],'limit'=>6]);
        $this->set('data',$data);
        if($this->request->params['requested']) { return $data; }
    }

    /**
     * Get the latest uploaded spectrum (report)
     */
    public function latest()
    {
        $c=['Dataset'=>[
                'Dataseries'=>
                    ['Datapoint'=>['Data','Condition'],'Descriptor'],
                'Context'=>['System'=>['Substance'=>['Identifier'=>['conditions'=>['type'=>'inchikey']]]]],
                'Methodology'=>['Measurement']]];
        $r=$this->Report->find('first',['order'=>['Report.updated'=>'desc'],'limit'=>1,'contain'=>$c]);
        // For jmol: name, inchikey
        $data=[];
        $data['jmol']['name']=$r['Dataset']['Context']['System'][0]['Substance'][0]['name'];
        $data['jmol']['inchikey']=$r['Dataset']['Context']['System'][0]['Substance'][0]['Identifier'][0]['value'];
        // For flot
        $data['flot']['id']=$r['Report']['id'];
        $this->set('data',$data);
        if($this->request->params['requested']) { return $data; }
    }

    /**
     * Generates the data in SciData JSON-LD
     * @param $id
     */
    public function scidata($id)
    {
        $data=$this->Report->scidata($id);
        $id="s".str_pad($id,9,"0",STR_PAD_LEFT);
        $rpt=$data['Report'];
        $set=$data['Dataset'];
        //$file=$set['File'];
        //$usr=$data['User'];
        $met=$set['Methodology'];
        $con=$set['Context'];
        //$sam=$set['Sample'];
        $ser=$set['Dataseries'];
        $base="http://osdb.oinfo/json/".$id;

        // Build the PHP array that will then be converted to JSON
        $json['@context']=['https://chalk.coas.unf.edu/champ/files/contexts/scidata.jsonld',
                            ['@base'=>$base]];

        // Main metadata
        $json['@id']=$id;
        $json['title']=$rpt['title'];
        $json['author']=$rpt['author'];
        $json['description']=$rpt['description'];
        $json['version']=1;
        $json['date']=$rpt['updated'];
        //$json['permalink']="http://hdl.handle.net/osdb/".$id;

        // Dataset
        $setj['@id']="scidata";
        $opts=['setType','property','kind'];
        foreach($opts as $opt) {
            if(isset($set[$opt])&&$set[$opt]!="") {
                $setj[$opt]=$set[$opt];
            }
        }
        $json['scidata']=$setj;

        // Methodology sections
        $metj['@id']='methodology';
        if(isset($met['evaluation'])&&$met['evaluation']!="") { $metj['evaluation']=$met['evaluation']; }
        $metj['aspects']=[];

        // Measurement
        if(isset($met['Measurement'])&&!empty($met['Measurement'])) {
            $mea=$met['Measurement'];
            $meaj=[];
            $meaj['@id']='measurement';
            $opts=['techniqueType','technique','instrumentType','instrument','vendor'];
            foreach($opts as $opt) {
                if(isset($mea[$opt])&&$mea[$opt]!="") {
                    $meaj[$opt]=$mea[$opt];
                }
            }
            if(isset($mea['Setting'])&&!empty($mea['Setting'])) {
                $meaj['settings']=[];
                $settings=$mea['Setting'];
                for($x=0;$x<count($settings);$x++) {
                    $s=$mea['Setting'][$x];
                    $setgj=[];
                    $setgj['@id']="setting/".($x+1);
                    $setgj['quantity']=strtolower($s['Property']['Quantity']['name']);
                    $setgj['property']=$s['Property']['name'];
                    $v=[];
                    $v['@id']="setting/".($x+1)."/value";
                    if(!is_null($s['number'])) {
                        $v['number']=$s['number'];
                        if(isset($s['Unit']['symbol'])) { $v['unit']=$s['Unit']['symbol']; }
                    } else {
                        $v['text']=$s['text'];
                    }
                    $setgj['value']=$v;
                    $meaj['settings'][]=$setgj;
                }
            }
            $metj['aspects'][]=$meaj;
        }

        // Add methodology section to main array
        $json['scidata']['methodology']=$metj;

        // Context sections
        $conj['@id']='context';
        $opts=['discipline','subdiscipline'];
        foreach($opts as $opt) {
            if(isset($con[$opt])&&$con[$opt]!="") {
                $conj[$opt]=strtolower($con[$opt]);
            }
        }
        $conj['aspects']=[];

        // System
        if(isset($con['System'])) {
            for($i=0;$i<count($con['System']);$i++) {
                $sys=$con['System'][$i];
                $sysj['@id'] = 'system/'.($i+1);
                $opts=['name','description','type'];
                foreach($opts as $opt) {
                    if(isset($sys[$opt])&&$sys[$opt]!="") {
                        $sysj[$opt]=$sys[$opt];
                    }
                }
                if(isset($sys['Substance'])) {
                    for ($j = 0; $j < count($sys['Substance']); $j++) {
                        $sub=$sys['Substance'][$j];
                        $subj['@id']="substance/".($j+1);
                        $opts=['name','formula','molweight'];
                        foreach($opts as $opt) {
                            if(isset($sub[$opt])&&$sub[$opt]!="") {
                                $subj[$opt]=$sub[$opt];
                            }
                        }
                        if(isset($sub['Identifier']))
                        {
                            $opts=['inchi','inchikey','iupacname'];
                            foreach($sub['Identifier'] as $idn) {
                                foreach ($opts as $opt) {
                                    if ($idn['type']==$opt) {
                                        $subj[$opt] = $idn['value'];
                                    }
                                }
                            }
                        }
                        $sysj['components'][]=$subj;
                    }
                }
                $conj['aspects'][]=$sysj;
            }
        }

        // Add context section to main array
        $json['scidata']['context']=$conj;

        // Dataset section
        $resj['@id']='dataset';
        $resj['dataseries']=[];
        for($k=0;$k<count($ser);$k++) {
            $dat=$ser[$k];
            $datj=[];
            $datj['@id']="dataseries/".($k+1);
            $opts=['type','format','level'];
            foreach($opts as $opt) {
                if(isset($dat[$opt])&&$dat[$opt]!="") {
                    $datj[$opt]=strtolower($dat[$opt]);
                }
            }

            // Annontations
            if(isset($dat['Annotation'])&&!empty($dat['Annotation'])) {
                foreach($dat['Annotation'] as $ann) {
                    $annj=[];
                    if(isset($ann['Metadata'])&&!empty($ann['Metadata'])) {
                        foreach($ann['Metadata'] as $meta) {
                            if($meta['format']=="text") {
                                if($meta['value']=="") { break; } // Empty
                                $annj[]=[$meta['field']=>$meta['value']];
                            } else {
                                if($meta['value']=="[]") { break; } // JSON array
                                $annj[]=[$meta['field']=>json_decode($meta['value'],true)];
                            }
                        }
                    } else {
                        break; // Get out of annotation if no metadata in class
                    }
                    $datj[$ann['class']]=$annj;
                }
            }

            // Descripters
            if(isset($dat['Descriptor'])&&!empty($dat['Descriptor'])) {
                for($l=0;$l<count($dat['Descriptor']);$l++) {
                    $desj=[];
                    $des=$dat['Descriptor'][$l];
                    $desj['@id']="descriptor/".($l+1);
                    $desj['quantity']=strtolower($des['Property']['Quantity']['name']);
                    $desj['property']=$des['title'];
                    $v=[];
                    $v['@id']="descriptor/".($l+1)."/value";
                    if(!is_null($des['number'])) {
                        $v['number']=$des['number'];
                        if(isset($des['Unit']['symbol'])) { $v['unit']=$des['Unit']['symbol']; }
                    } else {
                        $v['text']=$des['text'];
                    }
                    $desj['value']=$v;
                    $datj['descriptor'][]=$desj;
                }
            }

            // Datapoints
            if(isset($dat['Datapoint'])) {
                for($m=0;$m<count($dat['Datapoint']);$m++) {
                    // Independent axis
                    $cond=$dat['Datapoint'][$m]['Condition'][0];
                    //debug($cond);
                    $condj=[];
                    $condj['@id']="series/".($m+1)."/x";
                    $condj['quantity']=strtolower($cond['Property']['Quantity']['name']);
                    $condj['property']=$cond['title'];
                    $condj['label']=ucfirst($cond['title']);
                    if(isset($cond['Unit']['symbol'])&&$cond['Unit']['symbol']!="") {
                        $condj['label'].=" (".$cond['Unit']['symbol'].")";
                    }
                    $condj['axis']="independent";
                    $v=[];
                    $v['@id']="result/".($m+1)."/x/value";
                    if(!is_null($cond['number'])) {
                        if($cond['datatype']=="datum") {
                            $v['format']="datam";
                            $v['number']=$cond['number'];
                        } else {
                            $v['format']="array";
                            $v['number']=json_decode($cond['number'],true);
                        }
                        if(isset($cond['Unit']['symbol'])) { $v['unit']=$cond['Unit']['symbol']; }
                        $condj['value']=$v;
                    }
                    $datj['values'][]=$condj;

                    // Dependent axis
                    $data=$dat['Datapoint'][$m]['Data'][0];
                    $dataj=[];
                    $dataj['@id']="series/".($m+1)."/y";
                    $dataj['quantity']=strtolower($data['Property']['Quantity']['name']);
                    $dataj['property']=$data['title'];
                    $dataj['label']=ucfirst($data['title']);
                    if(isset($data['Unit']['symbol'])&&$data['Unit']['symbol']!="") {
                        $dataj['label'].=" (".$data['Unit']['symbol'].")";
                    }
                    $dataj['axis']="dependent";
                    $v=[];
                    $v['@id']="result/".($m+1)."/y/value";
                    if(!is_null($data['number'])) {
                        if($data['datatype']=="datum") {
                            $v['format']="datam";
                            $v['number']=$cond['number'];
                        } else {
                            $v['format']="array";
                            $v['number']=json_decode($data['number'],true);
                        }
                        $dataj['value']=$v;
                        if(isset($data['Unit']['symbol'])) { $v['unit']=$data['Unit']['symbol']; }
                    }
                    $datj['values'][]=$dataj;
                }
            }
            $resj['dataseries']=$datj;
        }
        $json['scidata']['dataset']=$resj;

        // Source
        $json['source']=['@id'=>'source'];
        $json['source']['citation']='The Open Spectral Database - http://osdb.oinfo';
        $json['source']['url']=$base;

        // Rights
        $json['rights']=['@id'=>'rights'];
        $json['rights']['license']='http://creativecommons.org/publicdomain/zero/1.0/';
        //debug($json);exit;

        // OK turn it back into JSON-LD
        header("Content-Type: application/ld+json");
        //header('Content-Disposition: attachment; filename="'.$id.'.json"');
        echo json_encode($json,JSON_UNESCAPED_UNICODE);exit;

    }

    /**
     * Delete a property
     * @param $id
     */
    public function delete($id)
    {
        $this->Report->delete($id);
        $this->redirect('/reports');
    }
}
