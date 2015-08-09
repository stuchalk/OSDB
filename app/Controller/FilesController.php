<?php

/**
 * Class FilesController
 */
class FilesController extends AppController {

    public $uses = ['File','Dataset','Substance','Methodology','Measurement','Setting','Jcampldr','Propertytype',
                    'Sample','SubstancesSystem', 'Report','System','Identifier','Technique','Pubchem.Chemical','Unit',
                    'Activity','Animl.Jcamp','Dataseries','Annotation','Metadata','Datapoint','Data','Condition','Property'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles');
    }

    /**
     * Add a file
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            $data=$this->request->data;
            $data['name']=$data['File']['file']['name'];
            $data['size']=$data['File']['file']['size'];
            $data['type']=$data['File']['file']['type'];
            $tmpname=$data['File']['file']['tmp_name'];
            unset($data['File']['file']);

            // TODO: Mime type not correct for .jdx files

            // Create data array
            $file2=file($tmpname, FILE_IGNORE_NEW_LINES);
            $jarray=$this->Jcamp->convert($file2);

            debug($jarray);exit;

            // Create technique related variables from data in jarray
            $techstr="an unknown";$tech=$techprop=$techid=$techcode="";
            if(isset($jarray['PARAMS']['OBSERVENUCLEUS'])) {
                $jarray['PARAMS']['OBSERVENUCLEUS']=str_replace("^","",$jarray['PARAMS']['OBSERVENUCLEUS']);
                $tech="NMR";
                $techprop="Nuclear Magnetic Resonance";
                $techstr=$jarray['PARAMS']['OBSERVENUCLEUS']." NMR";
                $techid="NMRSAMPLE";
                $techcode=$jarray['PARAMS']['OBSERVENUCLEUS']."NMR";
                if(strtolower($jarray['XUNITS'])=="hz") {
                    $xlabel="Radiofrequency";
                    $xpropid=$this->Property->field('id',['name'=>"Radiofrequency"]);
                    $xunitid=$this->Unit->field('id',['symbol'=>"Hz"]);
                }
                if(strtolower($jarray['YUNITS'])=="arbitrary units") {
                    $ylabel="Signal (Arbitrary Units)";
                    $ypropid=$this->Property->field('id',['name'=>"Signal Strength"]);
                    $yunitid=$this->Unit->field('id',['symbol'=>""]);
                }
            }

            // Define contents first

            // Get main substance_id
            if($data['File']['substance_id']=='') {
                $chm=$data['File']['substance'];
                $sid1=$this->File->getChem($chm); // Adds substance if it does not exist
                $data['File']['substance_id']=$sid1;
            } else {
                $sid1=$data['File']['substance_id'];
            }

            echo "SID1: ";debug($sid1);


            // Add solvent into substances if there is one
            $sid2="";
            if(isset($jarray['PARAMS']['SOLVENTNAME'])) {
                $sol=$jarray['PARAMS']['SOLVENTNAME'];
                $sub2=$this->Identifier->find('first',['conditions'=>['value like'=>'%'.$sol.'%']]);
                if(empty($sub2)) {
                    $sid2=$this->File->getChem($sol); // Adds substance if it does not exist
                } else {
                    $sid2=$sub2['Identifier']['substance_id'];
                }
            }

            echo "SID2: ";debug($sid2);

            // Add system
            // Get system_id if the system already exists
            $sysid=$this->SubstancesSystem->findUnique([$sid1,$sid2]);
            echo "SYSID: ";debug($sysid);
            if(is_null($sysid)) {
                $names=$this->Substance->find('list',['fields'=>['id','name'],'conditions'=>['id in'=>[$sid1,$sid2]]]);
                if($sid2!="") {
                    $sys['name']=$names[$sid1]." and ".$names[$sid2];
                    $sys['description']="A mixture of two organic compounds";
                } else {
                    $sys['name']=$names[$sid1];
                    $sys['description']="A pure organic compound";
                }
                $sys['type']="Single phase liquid"; // TODO add MAS check
                $system=$this->System->add($sys);
                $sysid=$system['id'];
                // Add substances_systems entries
                $ss1=$this->SubstancesSystem->add(['substance_id'=>$sid1,'system_id'=>$sysid]);
                if($sid2!="") {
                    $ss2=$this->SubstancesSystem->add(['substance_id'=>$sid2,'system_id'=>$sysid]);
                }
            }

            // Add report - the representation of the data on the website
            $rpt['user_id']=$this->Auth->user('id'); // TODO add check for other sources (publication or website)
            $rpt['title']=$jarray['TITLE'];
            $rpt['description']=$tech." spectrum of ".$jarray['TITLE'];
            $rpt['comment']="Upload of a JCAMP file by ".$this->Auth->user('fullname').". ORIGIN: ".$jarray['ORIGIN'].". OWNER: ".$jarray['OWNER'];
            $report=$this->Report->add($rpt);
            $rptid=$report['id'];

            debug($report);

            // PropertyType is the type of spectrum it is, look up in table to get id
            $pro=$this->Propertytype->find('first',['conditions'=>['code'=>$techcode]]);
            $proid=$pro['Propertytype']['id'];

            // Add the file information

            $file=$this->File->add($data);
            $filid=$file['id'];
            $pathid=str_pad($filid,9,"0",STR_PAD_LEFT);


            // Move file to storage location (based on extension)
            $ext = pathinfo($data['name'], PATHINFO_EXTENSION);
            if($ext=="jdx"||$ext=="dx") {
                $path="files".DS."jdx";
                $folder = new Folder(WWW_ROOT.$path,true,0777);
                $path.=DS.$pathid.".jdx";
                move_uploaded_file($tmpname,WWW_ROOT.$path);
                $this->File->save(['id'=>$filid,'path'=>"/".$path]);

                // Get version of format and technique type and add to file data
                $this->File->saveField('version',$jarray['JCAMPDX']); // JCAMP version
                $tech = $this->Technique->find('first',['conditions'=>['matchstr'=>$techcode]]);
                $file=$this->File->save(['id'=>$filid,'technique_id'=>$tech['Technique']['id']]); // JCAMP Data Type

                // Save XML
                $path="files".DS."xml";
                $folder = new Folder(WWW_ROOT.$path,true,0777);
                $path.=DS.$pathid.'.xml';
                $fp=fopen(WWW_ROOT.$path,'w');
                fwrite($fp,$this->Jcamp->makexml($jarray));
                fclose($fp);
            }

            debug($file);

            // Add a Reference? TODO ?

            // Add Dataset
            $set=['report_id'=>$rptid,'file_id'=>$filid,'propertytype_id'=>$proid];
            $set['setType']="property_value";
            $set['property']=$techprop;
            if(isset($jarray['XYDATA'])) {
                $set['format']=$jarray['XYDATA'];
            } elseif (isset($jarray['XYPOINTS'])) {
                $set['format']=$jarray['XYPOINTS'];
            } elseif (isset($jarray['NTUPLES'])) {
                $set['format']=$jarray['NTUPLES'];
            } else {
                $set['format']="unknown";
            }
            $set['format']=strtolower($set['format']);
            $set['kind']="spectrum";
            $dataset=$this->Dataset->add($set);
            $setid=$dataset['id'];

            debug($dataset);

            // Add sample
            $sam['identifier']=$techid;
            $sam['description']="Solution prepared for ".$techstr." analysis";
            $sam['dataset_id']=$setid;
            $sam['system_id']=$sysid;
            $sample=$this->Sample->add($sam);

            debug($sample);

            // Add methodology
            $met=['dataset_id'=>$setid,'evaluation'=>"experimental",'aspects'=>'measurement'];
            $methodology=$this->Methodology->add($met);
            $metid=$methodology['id'];

            debug($methodology);

            // Add measurement
            $nmr['1H']=["250"=>"38.376","300"=>"46.051","400"=>"61.401","500"=>"76.753"];     // NMR larmor frequency => Tesla
            $nmr['13H']=["250"=>"62.860","300"=>"75.432","400"=>"100.576","500"=>"125.721"];  // NMR larmor frequency => Tesla
            $mea['methodology_id']=$metid;
            $mea['techniqueType']="spectroscopic";
            $mea['technique']=$techprop;
            if(isset($jarray['PARAMS']['OBSERVEFREQUENCY'])) {
                $frq=round($jarray['PARAMS']['OBSERVEFREQUENCY'],0);
            } elseif(isset($jarray['PARAMS']['FIELD'])) {
                $field=round($jarray['PARAMS']['FIELD'],0);
                foreach($nmr[$jarray['PARAMS']['OBSERVENUCLEUS']] as $freq=>$mf) {
                    if($field==round($mf)) {
                        $frq=$freq;
                        break;
                    }
                }
            }
            $mea['instrumentType']=$frq." MHz NMR";
            $mea['instrument']=$jarray['SPECTROMETERDATASYSTEM'];
            if(isset($jarray['DATAPROCESSING'])) { } // TODO
            $measurement=$this->Measurement->add($mea);
            $meaid=$measurement['id'];

            debug($measurement);

            // Add settings
            // Settings are from the PARAMS and BRUKER arrays
            $contain=['Property'=>['fields'=>['Property.name'],'Quantity'=>['fields'=>['Quantity.name','Quantity.si_unit'],'Unit'=>['fields'=>['Unit.symbol']]]]];
            $settings=$this->Jcampldr->find('all',['fields'=>['Jcampldr.arrayname','Jcampldr.unit'],'conditions'=>['scidata'=>'settings'],'contain'=>$contain]);
            $params=array_merge($jarray['PARAMS'],$jarray['BRUKER']);
            foreach($settings as $s) {
                if(isset($params[$s['Jcampldr']['arrayname']])) {
                    // Need methodology, property_id, number, unit_id, accuracy
                    $propid=$s['Property']['id'];
                    $unit=$s['Jcampldr']['unit'];
                    if($unit!="") {
                        $unitid = null;
                        $units = $s['Property']['Quantity']['Unit'];
                        for ($x = 0; $x < count($units); $x++) {
                            if ($units[$x]['symbol'] == $unit) {
                                $unitid = $units[$x]['id'];
                            }
                        }
                        $number=$params[$s['Jcampldr']['arrayname']];
                        $acc=strlen(str_replace(".","",$number));
                        $setn=['measurement_id'=>$meaid,'property_id'=>$propid,'unit_id'=>$unitid,'number'=>$number,'accuracy'=>$acc];
                    } else {
                        $txt=$params[$s['Jcampldr']['arrayname']];
                        $setn=['measurement_id'=>$meaid,'property_id'=>$propid,'text'=>$txt];
                    }
                    $setting=$this->Setting->add($setn);
                    debug($setting);
                }
            }
            // TODO make settings more sophisticated to be able to handles lrds that are text based arrays

            foreach($jarray['DATA'] as $darray) {

                // Add dataseries
                $ser=['dataset_id'=>$set,'type'=>'spectrum','format'=>$set['format'],'level'=>'processed'];
                $dataseries=$this->Dataseries->add(['Dataseries'=>$ser]);
                $serid=$dataseries['id'];

                debug($dataseries);

                // Add annotation on dataseries or 'origin' class of metadata
                $ann=['dataseries_id'=>$serid,'class'=>'origin','comment'=>'Origin metadata is about the original file'];
                $annotation=$this->Annotation->add($ann);
                $annid=$annotation['id'];

                debug($annotation);

                // Add the origin metadata
                $metaarray=['filename:text'=>$data['name'],'filetype:text'=>'jcamp','version:text'=>$jarray['JCAMPDX'],
                        'date:text'=>$jarray['DATETIME'],'owner:text'=>$jarray['OWNER'],'asdfType:text'=>$darray['asdftype'],
                        'fileComments:json'=>json_encode($jarray['COMMENTS']),'conversionErrors:json'=>json_encode($jarray['ERRORS'])];
                foreach($metaarray as $key=>$val) {
                    list($field,$format)=explode(":",$key);
                    $meta=$this->Metadata->add(['annotation_id'=>$annid,'field'=>$field,'value'=>$val,'format'=>$format]);
                    debug($meta);
                }

                // Add descriptors to dataseries
                $descs=['NPOINTS','FIRSTX','LASTX','MAXX','MINX','MAXY','MINY','XFACTOR','YFACTOR','FIRSTY','DELTAX'];
                foreach($desc as $desc) {
                    if(isset($jarray[$desc])) {
                        // Quantity, property, value, unit, dataseries_id, title
                        $descriptor=$this->Descriptor->add($des);
                    }
                }

                // Add datapoint (only one as the two arrays will be stored a single row in table)
                $dpt=['dataseries_id'=>$serid];
                $point=$this->Datapoint->add($dpt);
                $dptid=$point['id'];

                debug($point);

                // Split out the x and y values into separate arrays
                $x=$y=[];
                foreach($darray['pro'] as $x1=>$y1) { $x[]=$x1;$y[]=$y1; }


                // Add condition to datapoint - this is the x axis data (independent)
                $con=['datapoint_id'=>$dptid,'number'=>json_encode($x),'datatype'=>'array',
                        'property_id'=>$xpropid,'title'=>$xlabel,'unit_id'=>$xunitid];
                $condition=$this->Condition->add($con);

                debug($condition);


                // Add data to datapoint - this is the y axis data (dependent) as an array
                $dat=['datapoint_id'=>$dptid,'number'=>json_encode($y),'datatype'=>'array',
                    'property_id'=>$ypropid,'title'=>$ylabel,'unit_id'=>$yunitid];
                $datarow=$this->Data->add($dat);
                debug($datarow);

                exit;
            }


            exit;
            // Add activity
            $act=['user_id'=>$this->Auth->user('id'),'file_id'=>$this->File->id,'step_num'=>1,'type'=>'upload'];
            $activity=$this->Activity->add($act);
            $actid=$activity['id'];



            // Redirect to view
            return $this->redirect('/files/view/' . $this->File->id);
        }
    }

    /**
     * Add a whole bunch of files
     */
    public function massUpload(){
        if($this->request->is('post')) {
            $zip = new ZipArchive;
            $res = $zip->open($this->request->data['File']['file']['tmp_name']);
            if ($res === TRUE) {
                $zip->extractTo(WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name']);
                $zip->close();
                $dir=WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name'];
                $files=scandir($dir);
                foreach($files as $file){
                    if(pathinfo($file, PATHINFO_EXTENSION) == "pdf") {
                        $requestFile['File']=array();
                        $requestFile['File']['file']['name']=pathinfo($file, PATHINFO_FILENAME).".pdf";;
                        $requestFile['File']['file']['tmp_name']=WWW_ROOT.'temp'.DS.$this->request->data['File']['file']['name'].DS.$requestFile['File']['file']['name'];
                        $requestFile['File']['file']['size']=filesize($requestFile['File']['file']['tmp_name']);
                        $requestFile['File']['publication_id']=$this->request->data['File']['publication_id'];
                        $requestFile['File']['num_systems']=$this->request->data['File']['num_systems'];
                        $response=$this->requestAction('/Files/add',$requestFile);
                        if($response!==true){
                            echo "Failed :".$requestFile['File']['file']['name']."(".$response.")<br>";
                        }else{
                            echo "Added :".$requestFile['File']['file']['name']."<br>";
                        }

                        //unlink($requestFile['File']['file']['tmp_name']);
                    }
                }
                if($dir!==""&&$dir!==Null) {
                    $files = array_diff(scandir($dir), array('.', '..'));
                    foreach ($files as $file) {
                        unlink($dir . DS . $file);
                    }
                    rmdir($dir);
                }
                return $this->redirect('/Files/');
            } else {
                return $this->redirect('/Files/massUpload'.$this->File->id);
            }
        }else{
            $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
            $this->set('pubs',$pubs);
        }
    }

    /**
     * View a file
     * @param $id
     */
    public function view($id)
    {
        $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
        $this->set('data',$data);
    }

    /**
     * Update a file
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            //echo "<pre>";print_r($this->request->data);echo "</pre>";exit;
            $this->File->id=$id;
            $this->File->save($this->request->data);
            $this->redirect(['action' => 'index']);
        } else {
            $data=$this->File->find('first',['conditions'=>['File.id'=>$id]]);
            $this->set('data',$data);
            $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
            $this->set('pubs',$pubs);
            $this->set('id',$id);
        }
    }

    /**
     * Delete a file
     * @param $id
     */
    public function delete($id)
    {
        $this->File->delete($id);
        return $this->redirect(['action' => 'index']);

    }

    /**
     * List the files
     */
    public function index()
    {
        $data=$this->File->find('list',['fields'=>['id','filename','publication_id'],'order'=>['id','publication_id']]);
        $this->set('data',$data);
        $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
        $this->set('pubs',$pubs);
    }

    /**
     * Count the files
     * @return mixed
     */
    public function totalfiles()
    {
        $data=$this->File->find('count');
        return $data;
    }

    /**
     * Processing
     */
    public function processing()
    {
        $files=$this->File->find('list',['fields'=>['id','filename','publication_id'],'order'=>['id','filename']]);
        $this->set('files',$files);
        $textfile=$this->TextFile->find('list',['fields'=>['id','file_id'],'order'=>['id','file_id']]);
        $this->set('textfile',$textfile);
        $pubs=$this->Publication->find('list',['fields'=>['id','title']]);
        $this->set('pubs',$pubs);
    }

}
?>