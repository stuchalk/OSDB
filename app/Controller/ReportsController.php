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
        $this->Auth->allow('view','scidata','recent','latest','plot','index','test','splash','splashes','search');
    }

    /**
     * List the reports
     * @param integer $offset
     * @param integer $limit
     * @param string $search
     */
    public function index($offset=0,$limit=6,$search="%")
    {
        if(isset($this->request->data['Report']['search'])) {
            $search=$this->request->data['Report']['search'];
        }
        $data=$this->Report->bySubstance('sub',$search);
        // Limit the amount of data return base on offset and limit (needs to be redone using paginator)
        $slice=array_slice($data,$offset,$limit);
        $this->set('count',count($data));
        $this->set('search',$search);
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
     * @param mixed $id  (either spectral id, chemical name:technique, or splash
     * @param string $format
     */
    public function view($id,$format="")
    {
        // Accept uid
        $id=str_replace("osdb:spectrum:","",$id);
        // Find spectrum via splash or compound_name|techcode
        $error = "";
        if(stristr($id,'splash')) {
            // Access a spectrum using its splash code
            $report=$this->Report->find('first',['conditions'=>['splash'=>$id]]);
            $id=$report['Report']['id'];
        } elseif(stristr($id,"|")) {
            // Get the report id if one exists for this chemical and technique
            list($id,$tech)=explode("|",$id);
            $sid = $tid = $error = "";
            // Get the sid if there is one
            $data = $this->Substance->find('first', ['conditions' => ['name like ' => '%'.$id.'%'], 'recursive' => -1]);
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

        // Show error if we can't find the spectrum via splash or compound_name@techcode
        if($error!="") {
            if(strtolower($format)=="xml") {
                $this->Export->xml('osdb','spectra',['error'=>$error]);
            } elseif(strtolower($format)=="json"||strtolower($format)=="jsonld") {
                $this->Export->json('osdb','spectra',['error'=>$error]);
            } else {
                header("Content-Type: application/json");
                echo '{ "error": "An unknown error occurred" }';
                exit;
            }
        }

        // Check the download formats
        $osdbpath=Configure::read('url');
        if($format==""||strtolower($format)=="html") {
            // Process
            $data=$this->Report->scidata($id);
            //debug($data);exit;
            if(empty($data)) {
                throw new NotFoundException('Their is no spectrum with OSDB ID '.$id);
            }
            $rpt=$data['Report'];
            $set=$data['Dataset'];
            $file=$set['File'];
            $met=$set['Methodology'];
            $mea=$met['Measurement'];
            $sam=$set['Sample'];
            $con=$set['Context'];
            $ref=$set['Reference'];
            $sub=$con['System'][0]['Substance'][0];
            $ser=$set['Dataseries'];
            // Send on only the data needed for the view not the flot plot which is done separately via an element
            // Measurement data...
            $minfo=[];
            $minfo[]="Instrument Type: ".$mea['instrumentType'];
            $meta=['instrument','vendor','processing'];
            foreach($meta as $m) {
                if(!empty(str_replace("?","",$mea[$m]))) {
                    if(stristr($mea[$m],";")) {
                        $minfo[]=ucfirst($m).': '.str_replace(";",";\n",$mea[$m]);
                    } else {
                        $minfo[]=ucfirst($m).': '.$mea[$m];
                    }
                }
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
            $finfo=[];$dinfo=[];$cinfo=[];
            if(count($ser)==1) {
                $scale = 1;
                $spec = $ser[0];
                if (isset($spec['Annotation'])) {
                    foreach ($spec['Annotation'] as $ann) {
                        if ($ann['class'] == 'origin') {
                            foreach ($ann['Metadata'] as $m) {
                                if(!empty($m['value'])) {
                                    if ($m['field'] == "fileComments") {
                                        $cinfo['comments'] = $m['value'];
                                    } elseif ($m['field'] == "conversionErrors") {
                                        $cinfo['errors'] = $m['value'];
                                    } elseif ($m['field'] == "sourceReference") {
                                        $finfo[] = ucfirst($m['field']) . ": " . $m['value'];
                                    } elseif ($m['field'] == "date") {
                                        $finfo[] = ucfirst($m['field']) . ": " . date("M j, Y", strtotime($m['value']));
                                    } else {
                                        $finfo[] = ucfirst($m['field']) . ": " . $m['value'];
                                    }
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
                        (empty($d['text'])) ? $value = (float)$d['number'] : $value = $d['text']; // So that zeroes are not lost
                        if (stristr($d['title'], "maximum x")) {
                            $value = number_format(round($value / $scale));
                        } elseif (stristr($d['title'], "minimum x")) {
                            $value = number_format(round($value / $scale));
                        } elseif (stristr($d['title'], "increment")) {
                            $value = number_format($value / $scale, 5);
                        } elseif (stristr($d['title'], "first x")) {
                            $value = number_format($value / $scale);
                        } elseif (stristr($d['title'], "last x")) {
                            $value = number_format($value / $scale);
                        } elseif (stristr($d['title'], "first y")) {
                            $value = number_format($value);
                        } elseif (stristr($d['title'], "maximum y")) {
                            $value = number_format($value);
                        } elseif (stristr($d['title'], "minimum y")) {
                            $value = number_format($value);
                        }
                        $dinfo[]=ucfirst($d['title']) . ": " . $value;
                    }
                }
            }
            $this->set('desc',$rpt['description']);
            $this->set('jdxurl',$rpt['url']);
            $this->set('sub',$sub);
            $this->set('minfo',$minfo);
            $this->set('sinfo',$sinfo);
            $this->set('finfo',$finfo);
            $this->set('dinfo',$dinfo);
            $this->set('cinfo',$cinfo);
            $this->set('rinfo',$ref);
            $this->set('fileid',$file['id']);
            $this->set('splash',$rpt['splash']);
            $this->set('id',$id);
        } elseif(strtolower($format)=="jcamp") {
            header("Location: ".$osdbpath."/download/jdx/".str_pad($id,9,0,STR_PAD_LEFT).".jdx");exit;
        } elseif(strtolower($format)=="xml") {
            header("Location: ".$osdbpath."/download/xml/".str_pad($id,9,0,STR_PAD_LEFT).".xml");exit;
        } elseif(strtolower($format)=="json"||strtolower($format)=="jsonld") {
            header("Location: ".$osdbpath."/spectra/scidata/".str_pad($id,9,0,STR_PAD_LEFT));exit;
        } else {
            header("Content-Type: application/json");
            echo '{ "error": "Invalid request (\''.$format.'\' is not an acceptable value)" }';
            exit;
        }

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
        if(stristr($id,'splash')) {
            // Access a spectrum using its splash code
            $report=$this->Report->find('first',['conditions'=>['splash'=>$id]]);
            if(!empty($report)) {
                $id=$report['Report']['id'];
            } else {
                $error='Invalid OSDB Splash code ('.$id.')';
            }

        } elseif(!is_numeric($id)) {
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

        // Show error if we can't find the spectrum via splash or compound identifier and technique code
        if($error!="") {
            header("Content-Type: application/json");
            echo '{ "error": "'.$error.'" }';
            exit;
        }

        $data=$this->Report->scidata($id);
        $set=$data['Dataset'];
        $met=$set['Methodology'];
        $mea=$met['Measurement'];
        $ser=$set['Dataseries'];

        // Plot (flot) data...
        $flot=[];
        if($mea['technique']=="Mass Spectrometry") {
            $flot['tech']="ms";
        } elseif($mea['technique']=="Nuclear Magnetic Resonance") {
            $flot['tech']='nmr';
        } elseif($mea['technique']=="Infrared Spectroscopy") {
            $flot['tech']='ir';
        } elseif($mea['technique']=="UVVis Spectrophotometry") {
            $flot['tech']='uv';
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
            $spec = $ser[0];$flot['scale'] = 1;
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
                } elseif ($spec['level'] == 'processed' && $spec['processedType'] == "log epsilon") {
                    $flot['scale'] = 1;
                    $flot['ylabel'] = "Log (&epsilon;)";
                }
                foreach ($spec['Descriptor'] as $d) {
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
        $data=$this->Report->find('list',['order'=>['added'=>'desc'],'limit'=>10]);
        $this->set('data',$data);
        if($this->request->params['requested']) { return $data; }
    }

    /**
     * Get the latest uploaded spectrum (report)
     * @return mixed
     */
    public function latest()
    {
        $c=['Dataset'=>[
                'Dataseries'=>
                    ['Datapoint'=>['Data','Condition'],'Descriptor'],
                'Context'=>[
                    'System'=>[
                        'Substance'=>[
                            'Identifier'=>['conditions'=>['type'=>['inchikey','inchi']],'order'=>'type']]]],
                'Methodology'=>['Measurement']]];
        $r=$this->Report->find('first',['order'=>['Report.added'=>'desc'],'limit'=>1,'contain'=>$c]);
        // For jmol: name, inchikey
        $data=[];
        $a=$r['Dataset']['Context']['System'][0]['Substance'][0]; // First compound is always the analyte
        $data['jmol']['id']=$a['id'];
        $data['jmol']['name']=$a['name'];
        $data['jmol']['inchi']=$a['Identifier'][0]['value'];
        $data['jmol']['inchikey']=$a['Identifier'][1]['value'];
        // For flot
        $data['flot']['id']=$r['Report']['id'];
        $this->set('data',$data);
        if(isset($this->request->params['requested'])) { return $data; }
    }

    /**
     * Generates the data in SciData JSON-LD
     * @param $id
     * @param $down
     */
    public function scidata($id,$down="")
    {
        $data=$this->Report->scidata($id);
        //debug($data);exit;
        $id=str_pad($id,9,"0",STR_PAD_LEFT);
        $rpt=$data['Report'];
        $cols=$data['Collection'];
        $set=$data['Dataset'];
        $met=$set['Methodology'];
        $con=$set['Context'];
        $ser=$set['Dataseries'];
        $ref=$set['Reference'];
        $filemeta=$ser[0]['Annotation'][0]['Metadata'];
        $date="";
        foreach($filemeta as $meta) {
            if($meta['field']=="date") {
                $date=$meta['value'];
                break;
            } else {
                $date=$rpt['updated'];
            }
        }
        $base="https://osdb.stuchalk.domains.unf.edu/spectra/".$id."/";

        // Build the PHP array that will then be converted to JSON
        // metadata
        $json['@context']=[
            'https://stuchalk.github.io/scidata/contexts/scidata.jsonld',
            ['sdo'=>'http://stuchalk.github.io/scidata/ontology/scidata.owl#',
                'sub'=>'http://stuchalk.github.io/scidata/ontology/substance.owl#',
                'w3i'=>'https://w3id.org/skgo/modsci#',
                'qudt'=>'http://qudt.org/vocab/unit/',
                'obo'=>'http://purl.obolibrary.org/obo/',
                'dc'=>'http://purl.org/dc/terms/',
                'ss'=>'http://www.semanticweb.org/ontologies/cheminf.owl#',
                'xsd'=>'http://www.w3.org/2001/XMLSchema#'],
            ['@base'=>$base]];
        $json['@id']=$base;
        $json['generatedAt']=date(DATE_ATOM);
        $json['version']=1;


        // Main graph
        $graph['@id']=$base;
        $graph['@type']="sdo:scidataFramework";
        $graph['uid']="osdb:spectrum:".$id;
        $graph['title']=$rpt['title'];
        if($rpt['author']!="") {
            $graph['author']=['@id'=>'author','@type'=>'dc:creator','name'=>$rpt['author']];
        }
        $graph['description']=$rpt['description'];
        if($rpt['author']=="") { $rpt['author']="No publisher given in JCAMP file"; }
        $graph['publisher']=$rpt['author'];
        $graph['starttime']=$date;
        $graph['permalink']="https://osdb.stuchalk.domains.unf.edu/spectra/scidata/".$id;
        $graph['toc']=[];

        // Dataset
        $setj['@id']="scidata/";
        $setj['@type']="sdo:scientificData";
        $opts=['type'=>'setType','property'=>'property','kind'=>'kind'];
        foreach($opts as $field=>$opt) {
            if(isset($set[$opt])&&$set[$opt]!="") {
                $setj[$field]=$set[$opt];
            }
        }
        $graph['toc'][]="obo:CHMO_0000800";
        $graph['scidata']=$setj;

        // Methodology sections
        if(is_array($met)&&!empty($met)) {
            $graph['toc'][]="sdo:methodology";
        }
        $metj['@id']='methodology/';
        $metj['@type']='sdo:methodology';
        if(isset($met['evaluation'])&&$met['evaluation']!="") { $metj['evaluation']=$met['evaluation']; }
        $metj['aspects']=[];

        // Measurement
        if(isset($met['Measurement'])&&!empty($met['Measurement'])) {
            $mea=$met['Measurement'];
            $meaj=[];
            $meaj['@id']='measurement/1/';
            $meaj['@type']='sdo:measurement';
            $graph['toc'][]='sdo:measurement';
            $opts=['techniqueType','technique','instrumentType','instrument','vendor','processing'];
            foreach($opts as $opt) {
                if(isset($mea[$opt])&&$mea[$opt]!="") {
                    $meaj[$opt]=$mea[$opt];
                }
            }
            if(isset($mea['Setting'])&&!empty($mea['Setting'])) {
                $meaj['settings']=[];
                $settings=$mea['Setting'];
                $graph['toc'][]='sdo:setting';
                for($x=0;$x<count($settings);$x++) {
                    //debug($mea['Setting']);exit;
                    $s=$mea['Setting'][$x];
                    $setgj=[];
                    $setgj['@id']="setting/".($x+1);
                    $setgj['@type']="sdo:setting";
                    if(isset($s['Property']['Quantity']['name'])) {
                        $setgj['quantity']=strtolower($s['Property']['Quantity']['name']);
                    }
                    if(isset($s['Property']['name'])) {
                        $setgj['property'] = $s['Property']['name'];
                    }
                    $v=[];
                    $v['@id']="setting/".($x+1)."/value/";
                    $v['@type']="sdo:value";
                    if(!is_null($s['number'])) {
                        $v['number']=$s['number'];
                        if(isset($s['Unit']['symbol'])&&!empty($s['Unit']['symbol'])) {
                            $v['unitref']=$this->Report->qudt($s['Unit']['symbol']);
                        }
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
        $graph['scidata']['methodology']=$metj;

        // System sections
        if(is_array($con)&&!empty($con)) {
            $graph['toc'][]="sdo:system";
        }
        $conj['@id']='system/';
        $conj['@type']='sdo:system';
        $opts=['discipline','subdiscipline'];
        foreach($opts as $opt) {
            if(isset($con[$opt])&&$con[$opt]!="") {
                if(strtolower($con[$opt])=='chemistry') {
                    $conj[$opt]='w3i:Chemistry';
                } elseif(strtolower($con[$opt])=='analytical chemistry') {
                    $conj[$opt]='w3i:AnalyticalChemistry';
                }
            }
        }
        $conj['facets']=[];

        // System
        $type = null;
        if(isset($con['System'])) {
            for($i=0;$i<count($con['System']);$i++) {
                if (count($con['System'][$i]['Substance']) == 1) {
                    $type = "chemical";
                    $graph['toc'][] = "sdo:chemical";
                } else {
                    $type = "substance";
                    $graph['toc'][] = "sdo:substance";
                }
                $sid = $type . "/" . ($i + 1).'/';
                $sys = $con['System'][$i];
                $sysj['@id'] = $sid;
                $sysj['@type'] = "sdo:" . $type;
                $opts = ['name', 'description', 'type'];
                foreach ($opts as $opt) {
                    if (isset($sys[$opt]) && $sys[$opt] != "") {
                        $sysj[$opt] = $sys[$opt];
                    }
                }
                if (isset($sys['Substance'])) {
                    for ($j = 0; $j < count($sys['Substance']); $j++) {
                        // Components
                        $subj['@id'] = $sid . "constituent/" . ($j + 1).'/';
                        $subj['@type'] = "sdo:chemical";
                        $subj['source'] = "compound/" . ($j + 1).'/';
                        if(count($sys['Substance'])>1) {
                            if ($j == 0) {
                                $subj['role'] = 'sub:solute';
                            } else {
                                $subj['role'] = 'sub:solvent';
                            }
                        } else {
                            if ($j == 0) {
                                $subj['role'] = 'obo:CHMO_0002467';
                            }
                        }

                        $sysj['constituents'][] = $subj;
                        // Chemicals
                        $sub = $sys['Substance'][$j];
                        $chmj['@id'] = "compound/" . ($j + 1).'/';
                        $chmj['@type'] = "sdo:compound";
                        $graph['toc'][] = "sdo:compound";
                        $opts = ['name', 'formula', 'molweight'];
                        foreach ($opts as $opt) {
                            if (isset($sub[$opt]) && $sub[$opt] != "") {
                                $chmj[$opt] = $sub[$opt];
                            }
                        }
                        if (isset($sub['Identifier'])) {
                            $opts = ['inchi', 'inchikey', 'iupacname'];
                            foreach ($sub['Identifier'] as $idn) {
                                foreach ($opts as $opt) {
                                    if ($idn['type'] == $opt) {
                                        $chmj[$opt] = $idn['value'];
                                    }
                                }
                            }
                        }
                        $conj['facets'][] = $chmj;
                    }
                }
                $conj['facets'][] = $sysj;
            }
        }

        // Add context section to main array
        $graph['scidata']['system']=$conj;

        // Dataset section
        $graph['toc'][]="sdo:dataset";
        $resj['@id']='dataset/';
        $resj['@type']='sdo:dataset';
        $resj['source']='measurement/1/';
        $resj['scope']=$type.'/1/';
        $resj['datagroup']=[];
        if(!empty($ser)) {
            if(count($ser)>1) { $graph['toc'][]="sdo:datagroup"; }
            $graph['toc'][]="sdo:dataseries";
            for($k=0;$k<count($ser);$k++) {
                $dat=$ser[$k];

                $datj=[];
                $datj['@id']="datagroup/".($k+1).'/';
                $datj['@type']="sdo:datagroup";
                $opts=['type','format','level'];
                foreach($opts as $opt) {
                    if(isset($dat[$opt])&&$dat[$opt]!="") {
                        $datj[$opt]=strtolower($dat[$opt]);
                    }
                }

                // Source
                $datj['source']='http://osdb.info/spectra/view/'.$id.'/JCAMP';

                // Annontations
                if(isset($dat['Annotation'])&&!empty($dat['Annotation'])) {
                    foreach($dat['Annotation'] as $ann) {
                        $annj=[];
                        if(isset($ann['Metadata'])&&!empty($ann['Metadata'])) {
                            foreach($ann['Metadata'] as $meta) {
                                if($meta['format']=="text") {
                                    if($meta['value']=="") { break; } // Empty
                                    $annj[$meta['field']]=$meta['value'];
                                } else {
                                    if($meta['value']=="[]") { break; } // JSON array
                                    $annj[]=[$meta['field']=>json_decode($meta['value'],true)];
                                }
                            }
                        } else {
                            break; // Get out of annotation if no metadata in class
                        }
                        //$datj[$ann['class']]=$annj;  needs to be implemented semantically - will take time
                    }
                }

                // Descripters
                if(isset($dat['Descriptor'])&&!empty($dat['Descriptor'])) {
                    for($l=0;$l<count($dat['Descriptor']);$l++) {
                        $desj=[];
                        $des=$dat['Descriptor'][$l];
                        $desj['@id']="attribute/".($l+1).'/';
                        $desj['@type']="sdo:attribute";
                        $desj['quantity']=strtolower($des['Property']['Quantity']['name']);
                        $desj['property']=$des['title'];
                        $v=[];
                        $v['@id']="attribute/".($l+1)."/value/";
                        $v['@type']="sdo:value";
                        if(!is_null($des['number'])) {
                            $v['number']=$des['number'];
                            if(isset($des['Unit']['symbol'])&&!empty($des['Unit']['symbol'])) {
                                $v['unitref']=$this->Report->qudt($des['Unit']['symbol']);
                            }
                        } else {
                            $v['text']=$des['text'];
                        }
                        $desj['value']=$v;
                        $datj['attributes'][]=$desj;
                    }
                }

                // Datapoints
                if(isset($dat['Datapoint'])) {
                    $graph['toc'][]="sdo:datapoint";
                    $graph['toc'][]="sdo:datum";
                    $graph['toc'][]="sdo:property";
                    for($m=0;$m<count($dat['Datapoint']);$m++) {
                        // Independent axis
                        $cond=$dat['Datapoint'][$m]['Condition'][0];
                        $condj=[];
                        $condj['@id']="dataseries/1/";
                        $condj['@type']="sdo:x-axis";
                        $condj['label']=ucfirst($cond['title']);
                        if(isset($cond['Unit']['symbol'])&&$cond['Unit']['symbol']!="") {
                            $condj['label'].=" (".$cond['Unit']['symbol'].")";
                        }
                        $condj['axis']="independent";
                        // Parameter
                        $paramj=[];
                        $paramj['@id']="dataseries/1/parameter/";
                        $paramj['@type']="sdo:parameter";
                        $paramj['quantity']=strtolower($cond['Property']['Quantity']['name']);
                        $paramj['property']=$cond['title'];
                        // Value
                        $v=[];
                        if(!is_null($cond['number'])) {
                            $unit="";
                            if(isset($cond['Unit']['symbol'])&&!empty($cond['Unit']['symbol'])) {
                                $unit=$this->Report->qudt($cond['Unit']['symbol']);
                            }
                            if($cond['datatype']=="datum") {
                                $v['@id']="dataseries/1/parameter/value/";
                                $v['@type']="sdo:value";
                                $v['datatype']="decimal";
                                $v['number']=$cond['number'];
                                if($unit!="") { $v['unitref']=$unit; }
                                $paramj['value']=$v;
                            } else {
                                $v['@id']="dataseries/1/parameter/valuearray/";
                                $v['@type']="sdo:valuearray";
                                $v['datatype']="decimal";
                                $v['numberarray']=json_decode($cond['number'],true);
                                if($unit!="") { $v['unitref']=$unit; }
                                $paramj['valuearray']=$v;
                            }
                            $condj['parameter']=$paramj;
                        }
                        $datj['dataseries'][]=$condj;

                        // Dependent axis
                        $data=$dat['Datapoint'][$m]['Data'][0];
                        $dataj=[];
                        $dataj['@id']="dataseries/2/";
                        $dataj['@type']="sdo:y-axis";
                        $dataj['label']=ucfirst($data['title']);
                        if(isset($data['Unit']['symbol'])&&$data['Unit']['symbol']!="") {
                            $dataj['label'].=" (".$data['Unit']['symbol'].")";
                        }
                        $dataj['axis']="dependent";
                        // Parameter
                        $paramj=[];
                        $paramj['@id']="dataseries/2/parameter/";
                        $paramj['@type']="sdo:parameter";
                        $paramj['quantity']=strtolower($data['Property']['Quantity']['name']);
                        $paramj['property']=$data['title'];
                        // Value
                        $v=[];
                        if(!is_null($data['number'])) {
                            $unit="";
                            if(isset($data['Unit']['symbol'])&&!empty($data['Unit']['symbol'])) {
                                $unit=$this->Report->qudt($data['Unit']['symbol']);
                            }
                            if($cond['datatype']=="datum") {
                                $v['@id']="dataseries/2/parameter/value/";
                                $v['@type']="sdo:value";
                                $v['datatype']="decimal";
                                $v['number']=$data['number'];
                                if($unit!="") { $v['unitref']=$unit; }
                                $paramj['value']=$v;
                            } else {
                                $v['@id']="dataseries/2/parameter/valuearray/";
                                $v['@type']="sdo:valuearray";
                                $v['datatype']="decimal";
                                $v['numberarray']=json_decode($data['number'],true);
                                if($unit!="") { $v['unitref']=$unit; }
                                $paramj['valuearray']=$v;
                            }
                            $dataj['parameter']=$paramj;
                        }
                        $datj['dataseries'][]=$dataj;
                    }
                }

                $resj['datagroup']=$datj;
            }
        }
        $graph['scidata']['dataset']=$resj;

        // Source
        $ri=0;
        $graph['sources'][$ri]=['@id'=>'source/1/','@type'=>'dc:source'];
        $graph['sources'][$ri]['citation']=$rpt['title'].' - The Open Spectral Database, http://osdb.info';
        $graph['sources'][$ri]['url']=$base;
        if(!empty($ref)) {
            $ri++;
            $graph['sources'][$ri]=['@id'=>'source/2/','@type'=>'dc:source'];
            $graph['sources'][$ri]['citation']=$ref['citation'];
            if(!is_null($ref['doi'])) {
                $graph['sources'][$ri]['url']="http://dx.doi.org/".$ref['doi'];
            } elseif(!is_null($ref['url'])) {
                $graph['sources'][$ri]['url']=$ref['url'];
            }
        }
        if(!empty($cols)) {
            foreach ($cols as $col) {
                $ri++;
                $graph['sources'][$ri]=['@id'=>'source/'.($ri+1).'/','@type'=>'dc:source'];
                $graph['sources'][$ri]['citation']="Part of the ".$col['name']." Collection - ".$col['url'];
                $graph['sources'][$ri]['url']=$col['url'];
            }
        }

        // Rights
        $graph['rights']=['@id'=>'rights/','@type'=>'dc:rights'];
        $graph['rights']['license']='http://creativecommons.org/publicdomain/zero/1.0/';

        // add to graph
        $json['@graph']=$graph;

        // OK turn it back into JSON-LD
        header("Content-Type: application/ld+json");
        if($down=="download") { header('Content-Disposition: attachment; filename="'.$id.'.jsonld"'); }
        echo json_encode($json,JSON_UNESCAPED_UNICODE);exit;

    }

    /**
     * Delete a report (and all associated data)
     * @param $id
     */
    public function delete($id)
    {
        $this->Report->delete($id);
        $this->redirect('/users/admin');
    }

    /**
     * Used by the splash site to find out if a splash exists
     * @param string $spl
     */
    public function splash($spl)
    {
        $res=$this->Report->find('first',['conditions'=>['splash'=>$spl]]);
        if(empty($res)) {
            echo "false";
        } else {
            echo "true";
        }
        exit;
    }

    /**
     * JSON array of all splashes
     * @param string $inc
     */
    public function splashes($inc=null)
    {
		$c=[
			'Dataset'=>['fields'=>['setType','property','kind'],
				'Context'=>['fields'=>['discipline','subdiscipline'],
					'System'=>['fields'=>['id','name','description','type'],
						'Substance'=>['fields'=>['name','formula','molweight'],
							'Identifier'=>['fields'=>['type','value'],'conditions'=>['type'=>['inchikey']]]]]],
				]];
        $spls=$this->Report->find('all',['fields'=>['id','splash'],'conditions'=>['not'=>['splash'=>'null']],'order'=>'Report.id','contain'=>$c,'recursive'=>-1]);
        $temp=$spls;$spls=[];$inchis=[];
        foreach($temp as $spl) {
        	$inchi=$spl['Dataset']['Context']['System'][0]['Substance'][0]['Identifier'][0]['value'];
        	$spls[$spl['Report']['id']]=$spl['Report']['splash'];
			$inchis[$spl['Report']['id']]=$inchi;
		}
        //debug($spls);debug($inchis);exit;
        $osdbpath=Configure::read('url');
        if($inc=='links') {
            $out=[];
            foreach($spls as $id=>$spl) {
                $out[]=['url'=>$osdbpath."/spectra/view/".$id,'splash'=>$spl];
            }
            //debug($spls);exit;
            $json=json_encode($out);
            header("Content-Type: application/json");
            header('Content-Disposition: inline; filename="splashes_and_links.json"');
            echo '{ "site": "'.$osdbpath.'","accessed": "'.date(DATE_ATOM).'","url": "'.$osdbpath.'/splashes/links","count": '.count($spls).',"splashes": '.$json.' }';
        } elseif($inc=="inchis") {
			$out=[];
			foreach($spls as $id=>$spl) {
				$out[]=['inchi'=>$inchis[$id],'splash'=>$spl];
			}
			$json=json_encode($out);
			header("Content-Type: application/json");
			header('Content-Disposition: inline; filename="splashes.json"');
			echo '{ "site": "'.$osdbpath.'","accessed": "'.date(DATE_ATOM).'","url": "'.$osdbpath.'/splashes","count": '.count($spls).',"splashes": '.$json.' }';
		} elseif(is_null($inc)) {
            sort($spls);
            $json=json_encode($spls);
            header("Content-Type: application/json");
            header('Content-Disposition: inline; filename="splashes.json"');
            echo '{ "site": "'.$osdbpath.'","accessed": "'.date(DATE_ATOM).'","url": "'.$osdbpath.'/splashes","count": '.count($spls).',"splashes": '.$json.' }';
        } else {
            header("Content-Type: application/json");
            header('Content-Disposition: inline; filename="error.json"');
            echo '{ "error": "Invalid request (\''.$inc.'\' is not acceptable value)" }';
        }
        exit;
    }
    
	/**
	 * Search by splash
	 * @param $splash
	 */
	public function search($splash)
	{
		if($repid=$this->Report->search($splash)) {
			$this->redirect('/spectra/view/'.$repid);
		} else {
			$this->redirect('/spectra/index/');
		}
	}
	
}
