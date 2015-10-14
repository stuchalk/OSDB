<?php

/**
 * Class ReportController
 * Actions related to reports
 * @author Stuart Chalk <schalk@unf.edu>
 */
class ReportsController extends AppController
{
    public $uses=['Report'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('view','scidata');
    }

    /**
     * List the properties
     */
    public function index()
    {
        $data=$this->Report->bySubstance();
        //$data=$this->Report->find('list',['fields'=>['id','title'],'order'=>['title']]);
        $this->set('data',$data);
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
     * View a property
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Report->scidata($id);
        //debug($data);exit;
        $this->set('data',$data);
    }

    /**
     * Update a property
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
        $setj['@id']="data";
        $opts=['setType','property','kind'];
        foreach($opts as $opt) {
            if(isset($set[$opt])&&$set[$opt]!="") {
                $setj[$opt]=$set[$opt];
            }
        }
        $json['data']=$setj;

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
        $json['data']['methodology']=$metj;

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
        $json['data']['context']=$conj;

        // Result section
        $resj['@id']='results';
        $resj['result']=[];
        for($k=0;$k<count($ser);$k++) {
            $dat=$ser[$k];
            $datj=[];
            $datj['@id']="series/".($k+1);
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

            // Results
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
            $resj['result']=$datj;
        }
        $json['data']['results']=$resj;

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
        header('Content-Disposition: attachment; filename="'.$id.'.json"');
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
