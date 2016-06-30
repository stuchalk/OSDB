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
        $this->Auth->allow('view','scidata','recent','latest','plot','index','test','splash','splashes');
    }

    /**
     * List the reports
     * @param integer $offset
     * @param integer $limit
     * @param string $search
     */
    public function index($offset=0,$limit=6,$search="%")
    {
        //debug($this->request);exit;
        if(isset($this->request->data['Report']['search'])) {
            $search=$this->request->data['Report']['search'];
        }
        //debug($search);exit;
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
        $error = "";
        if(stristr($id,'splash')) {
            // Access a spectrum using its splash code
            $report=$this->Report->find('first',['conditions'=>['splash'=>$id]]);
            $id=$report['Report']['id'];
        }
        if(stristr($id,"@")) {
            // Get the report id is one exists for this chemical and technique
            list($id,$tech)=explode("@",$id);
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

        // Process
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
        $this->set('splash',$rpt['splash']);
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
                'Context'=>[
                    'System'=>[
                        'Substance'=>[
                            'Identifier'=>['conditions'=>['type'=>['inchikey','inchi']],'order'=>'type']]]],
                'Methodology'=>['Measurement']]];
        $r=$this->Report->find('first',['order'=>['Report.updated'=>'desc'],'limit'=>1,'contain'=>$c]);
        //debug($r);exit;
        // For jmol: name, inchikey
        $data=[];
        $data['jmol']['name']=$r['Dataset']['Context']['System'][0]['Substance'][0]['name'];
        $data['jmol']['inchi']=$r['Dataset']['Context']['System'][0]['Substance'][0]['Identifier'][0]['value'];
        $data['jmol']['inchikey']=$r['Dataset']['Context']['System'][0]['Substance'][0]['Identifier'][1]['value'];
        // For flot
        $data['flot']['id']=$r['Report']['id'];
        $this->set('data',$data);
        if($this->request->params['requested']) { return $data; }
    }

    /**
     * Generates the data in SciData JSON-LD
     * @param $id
     * @param $down
     */
    public function scidata($id,$down="")
    {
        $data=$this->Report->scidata($id);
        $id=str_pad($id,9,"0",STR_PAD_LEFT);
        $rpt=$data['Report'];
        $set=$data['Dataset'];
        //$file=$set['File'];
        //$usr=$data['User'];
        $met=$set['Methodology'];
        $con=$set['Context'];
        //$sam=$set['Sample'];
        $ser=$set['Dataseries'];
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
        $base="http://osdb.info/spectra/scidata/".$id."/";

        // Build the PHP array that will then be converted to JSON
        $json['@context']=['https://stuchalk.github.io/scidata/contexts/scidata.jsonld',
                            ['sci'=>'http://stuchalk.github.io/scidata/ontology/scidata.owl#',
                                'meas'=>'http://stuchalk.github.io/scidata/ontology/scidata_measurement.owl#',
                                'qudt'=>'http://www.qudt.org/qudt/owl/1.0.0/unit.owl#',
                                'dc'=>'http://purl.org/dc/terms/',
                                'ss'=>'http://www.semanticweb.org/ontologies/cheminf.owl#',
                                'xsd'=>'http://www.w3.org/2001/XMLSchema#'],
                            ['@base'=>$base]];

        // Main metadata
        $json['@id']="";
        $json['uid']="osdb:spectra:".$id;
        $json['title']=$rpt['title'];
        $json['author']=['@id'=>'author','@type'=>'dc:creator','name'=>$rpt['author']];
        $json['description']=$rpt['description'];
        $json['publisher']=$rpt['author'];
        $json['version']=1;
        $json['startdate']=$date;
        $json['permalink']="http://osdb.info/spectra/view/".$id;
        $json['toc']=['@id'=>'toc','@type'=>'dc:tableOfContents','sections'=>[]];

        // Dataset
        $setj['@id']="scidata";
        $setj['@type']="sci:scientificData";
        $opts=['type'=>'setType','property'=>'property','kind'=>'kind'];
        foreach($opts as $field=>$opt) {
            if(isset($set[$opt])&&$set[$opt]!="") {
                $setj[$field]=$set[$opt];
            }
        }
        $json['scidata']=$setj;

        // Methodology sections
        if(is_array($met)&&!empty($met)) {
            $json['toc']['sections'][]="methodology";
        }
        $metj['@id']='methodology';
        $metj['@type']='sci:methodology';
        if(isset($met['evaluation'])&&$met['evaluation']!="") { $metj['evaluation']=$met['evaluation']; }
        $metj['aspects']=[];

        // Measurement
        if(isset($met['Measurement'])&&!empty($met['Measurement'])) {
            $mea=$met['Measurement'];
            $meaj=[];
            $meaj['@id']='measurement/1';
            $meaj['@type']='meas:measurement';
            $json['toc']['sections'][]=$meaj['@id'];
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
                    $setgj['@type']="sci:setting";
                    $setgj['quantity']=strtolower($s['Property']['Quantity']['name']);
                    $setgj['property']=$s['Property']['name'];
                    $v=[];
                    $v['@id']="setting/".($x+1)."/value";
                    $v['@type']="sci:value";
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
        $json['scidata']['methodology']=$metj;

        // System sections
        if(is_array($con)&&!empty($con)) {
            $json['toc']['sections'][]="system";
        }
        $conj['@id']='system';
        $conj['@type']='sci:system';
        $opts=['discipline','subdiscipline'];
        foreach($opts as $opt) {
            if(isset($con[$opt])&&$con[$opt]!="") {
                $conj[$opt]=strtolower($con[$opt]);
            }
        }
        $conj['facets']=[];

        //debug($con['System']);

        // System
        if(isset($con['System'])) {
            for($i=0;$i<count($con['System']);$i++) {
                if (count($con['System'][$i]['Substance']) == 1) {
                    $type = "substance";
                } else {
                    $type = "mixture";
                }
                $sid = $type . "/" . ($i + 1);
                $json['toc']['sections'][] = $sid;
                $sys = $con['System'][$i];
                $sysj['@id'] = $sid;
                $sysj['@type'] = "sci:" . $type;
                $opts = ['name', 'description', 'type'];
                foreach ($opts as $opt) {
                    if (isset($sys[$opt]) && $sys[$opt] != "") {
                        $sysj[$opt] = $sys[$opt];
                    }
                }
                if (isset($sys['Substance'])) {
                    for ($j = 0; $j < count($sys['Substance']); $j++) {
                        // Components
                        $subj['@id'] = $sid . "/component/" . ($j + 1);
                        $subj['@type'] = "sci:chemical";
                        $subj['source'] = "compound/" . ($j + 1);
                        if ($j == 0) {
                            $subj['role'] = 'sci:solute';
                        } else {
                            $subj['role'] = 'sci:solvent';
                        }
                        $sysj['components'][] = $subj;
                        // Chemicals
                        $sub = $sys['Substance'][$j];
                        $chmj['@id'] = "compound/" . ($j + 1);
                        $json['toc']['sections'][] = $chmj['@id'];
                        $chmj['@type'] = "sci:compound";
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
        $json['scidata']['system']=$conj;

        // Dataset section
        $json['toc']['sections'][]="dataset";
        $resj['@id']='dataset';
        $resj['@type']='sci:dataset';
        $resj['source']='measurement/1';
        $resj['scope']='mixture/1';
        $resj['datagroup']=[];
        for($k=0;$k<count($ser);$k++) {
            $dat=$ser[$k];
            $json['toc']['sections'][]="datagroup/".($k+1);

            $datj=[];
            $datj['@id']="datagroup/".($k+1);
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
                                $annj[]=[$meta['field']=>$meta['value']];
                            } else {
                                if($meta['value']=="[]") { break; } // JSON array
                                $annj[]=[$meta['field']=>json_decode($meta['value'],true)];
                            }
                        }
                    } else {
                        break; // Get out of annotation if no metadata in class
                    }
                    //$datj[$ann['class']]=$annj;
                }
            }

            // Descripters
            if(isset($dat['Descriptor'])&&!empty($dat['Descriptor'])) {
                for($l=0;$l<count($dat['Descriptor']);$l++) {
                    $desj=[];
                    $des=$dat['Descriptor'][$l];
                    $desj['@id']="attribute/".($l+1);
                    $desj['@type']="sci:attribute";
                    $desj['quantity']=strtolower($des['Property']['Quantity']['name']);
                    $desj['property']=$des['title'];
                    $v=[];
                    $v['@id']="attribute/".($l+1)."/value";
                    $v['@type']="sci:value";
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
                for($m=0;$m<count($dat['Datapoint']);$m++) {
                    // Independent axis
                    $cond=$dat['Datapoint'][$m]['Condition'][0];
                    if(is_array($cond)&&!empty($cond)) {
                        $json['toc']['sections'][]="dataseries/1";
                    }
                    $condj=[];
                    $condj['@id']="dataseries/1";
                    $condj['@type']="sci:x-axis";
                    $condj['label']=ucfirst($cond['title']);
                    if(isset($cond['Unit']['symbol'])&&$cond['Unit']['symbol']!="") {
                        $condj['label'].=" (".$cond['Unit']['symbol'].")";
                    }
                    $condj['axis']="independent";
                    // Parameter
                    $paramj=[];
                    $paramj['@id']="dataseries/1/parameter";
                    $paramj['@type']="sci:parameter";
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
                            $v['@id']="dataseries/1/parameter/value";
                            $v['@type']="sci:value";
                            $v['datatype']="decimal";
                            $v['number']=$cond['number'];
                            if($unit!="") { $v['unitref']=$unit; }
                            $paramj['value']=$v;
                        } else {
                            $v['@id']="dataseries/1/parameter/valuearray";
                            $v['@type']="sci:valuearray";
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
                    if(is_array($data)&&!empty($data)) {
                        $json['toc']['sections'][]="dataseries/2";
                    }
                    $dataj=[];
                    $dataj['@id']="dataseries/2";
                    $dataj['@type']="sci:y-axis";
                    $dataj['label']=ucfirst($data['title']);
                    if(isset($data['Unit']['symbol'])&&$data['Unit']['symbol']!="") {
                        $dataj['label'].=" (".$data['Unit']['symbol'].")";
                    }
                    $dataj['axis']="dependent";
                    // Parameter
                    $paramj=[];
                    $paramj['@id']="dataseries/2/parameter";
                    $paramj['@type']="sci:parameter";
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
                            $v['@id']="dataseries/2/parameter/value";
                            $v['@type']="sci:value";
                            $v['datatype']="decimal";
                            $v['number']=$data['number'];
                            if($unit!="") { $v['unitref']=$unit; }
                            $paramj['value']=$v;
                        } else {
                            $v['@id']="dataseries/2/parameter/valuearray";
                            $v['@type']="sci:valuearray";
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
        $json['scidata']['dataset']=$resj;

        //debug($json);exit;

        // Source
        $json['reference']=['@id'=>'reference','@type'=>'dc:source'];
        $json['reference']['citation']='The Open Spectral Database - http://osdb.info';
        $json['reference']['url']=$base;

        // Rights
        $json['rights']=['@id'=>'rights','@type'=>'dc:rights'];
        $json['rights']['holder']='Chalk Group, Department of Chemistry, University of North Florida';
        $json['rights']['license']='http://creativecommons.org/publicdomain/zero/1.0/';
        //debug($json);exit;

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
     */
    public function splashes()
    {
        $spls=$this->Report->find('list',['fields'=>['id','splash'],'conditions'=>['not'=>['splash'=>'null']]]);
        sort($spls);
        $json=json_encode($spls);
        header("Content-Type: application/ld+json");
        echo '{ "site": "http://osdb.info","timestamp": "'.date(DATE_ATOM).'","count": '.count($spls).',"splashes": '.$json.' }';
        exit;
    }
}
