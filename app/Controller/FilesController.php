<?php

/**
 * Class FilesController
 */
class FilesController extends AppController {

    public $uses = ['File','Dataset','Substance','Methodology','Measurement','Setting','Jcampldr','Propertytype',
                    'Descriptor','Sample','SubstancesSystem', 'Report','System','Identifier','Technique',
                    'Pubchem.Chemical','Unit','Activity','Jcamp.Jcamp','Dataseries','Annotation','Metadata',
                    'Datapoint','Data','Condition','Property','Context','ContextsSystem'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles','test');
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

            // Create data array
            $file2=file($tmpname, FILE_IGNORE_NEW_LINES);
            $jarray=$this->Jcamp->convert($file2);

            //debug($jarray);exit;

            // Create technique related variables from data in jarray
            $techstr="an unknown";$xunitid=$yunitid=$xpropid=$ypropid=null;
            $tech=$techprop=$techid=$techcode=$xlabel=$ylabel="";$level="";$processed=null;
            if($jarray['DATATYPE']=="NMR SPECTRUM") {
                $jarray['PARAMS']['OBSERVENUCLEUS']=str_replace("^","",$jarray['PARAMS']['OBSERVENUCLEUS']);
                $tech="NMR";
                $techprop="Nuclear Magnetic Resonance";
                $techstr=$jarray['PARAMS']['OBSERVENUCLEUS']." NMR";
                $techid="NMRSAMPLE";
                $techcode=$jarray['PARAMS']['OBSERVENUCLEUS']."NMR";
                if(strtolower($jarray['XUNITS'])=="hz") {
                    $xunit="Hz";
                    $xlabel="Radiofrequency";
                    $xpropid=$this->Property->field('id',['name'=>"Radiofrequency"]);
                    $xunitid=$this->Unit->field('id',['symbol'=>$xunit]);
                    $level="processed";$processed="frequency";
                }
                if(strtolower($jarray['YUNITS'])=="arbitrary units") {
                    $ylabel="Signal (Arbitrary Units)";
                    $ypropid=$this->Property->field('id',['name'=>"Signal Strength"]);
                    $yunitid=$this->Unit->field('id',['symbol'=>""]);
                }
            } elseif($jarray['DATATYPE']=="MASS SPECTRUM") {
                $tech="MS";
                $techprop="Mass Spectrometry";
                $techstr="MS";
                $techid="MSSAMPLE";
                $techcode="MS";
                if(strtolower($jarray['XUNITS'])=="m/z") {
                    $xunit="m/z";
                    $xlabel="Mass-to-Charge Ratio";
                    $xpropid=$this->Property->field('id',['name'=>"Mass-to-Charge Ratio"]);
                    $xunitid=$this->Unit->field('id',['symbol'=>$xunit]);
                }
                if(strtolower($jarray['YUNITS'])=="relative abundance") {
                    $ylabel="Signal (Arbitrary Units)";
                    $ypropid=$this->Property->field('id',['name'=>"Relative Abundance"]);
                    $yunitid=$this->Unit->field('id',['symbol'=>""]);
                    $level="raw";
                }
            } elseif($jarray['DATATYPE']=="IR SPECTRUM") {
                $tech="IR";
                $techprop="Infrared Spectroscopy";
                $techstr="IR";
                $techid="IRSAMPLE";
                $techcode="IR";
                if(strtolower($jarray['XUNITS'])=="1/cm") {
                    $xunit="1/cm";
                    $xlabel="Wavenumber";
                    $xpropid=$this->Property->field('id',['name'=>"Wavenumber"]);
                    $xunitid=$this->Unit->field('id',['symbol'=>$xunit]);
                }
                if(strtolower($jarray['YUNITS'])=="transmittance") {
                    $ylabel="Transmittance";
                    $ypropid=$this->Property->field('id',['name'=>"Transmittance"]);
                    $yunitid=$this->Unit->field('id',['symbol'=>"%"]);
                    $level="processed";$processed="transmittance";
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

            // Add system
            // Get system_id if the system already exists
            $sarray=[$sid1];
            if($sid2!="") { $sarray[]=$sid2; }
            $sysid=$this->SubstancesSystem->findUnique($sarray);
            if(count($sarray)==1) {
                $names=$this->Substance->find('list',['fields'=>['id','name'],'conditions'=>['id'=>$sid1]]);
            } else {
                $names=$this->Substance->find('list',['fields'=>['id','name'],'conditions'=>['id in'=>$sarray]]);
            }
            if(is_null($sysid)) {
                //echo "In conditional...";
                if($sid2!="") {
                    $sys['name']=$names[$sid1]." and ".$names[$sid2];
                    $sys['description']="A mixture of two organic compounds";
                } else {
                    $sys['name']=$names[$sid1];
                    $sys['description']="A pure organic compound";
                }
                $sys['type']="Single phase liquid";
                $system=$this->System->add($sys);
                $sysid=$system['id'];
                // Add substances_systems entries
                $this->SubstancesSystem->add(['substance_id'=>$sid1,'system_id'=>$sysid]);
                if($sid2!="") {
                    $this->SubstancesSystem->add(['substance_id'=>$sid2,'system_id'=>$sysid]);
                }
            }

            // Add report - the representation of the data on the website
            $rpt['user_id']=$this->Auth->user('id');
            //if($jarray['TITLE']!="") {
            //    $rpt['title']=$jarray['TITLE'];
            //} else {
            //    $rpt['title']=$names[$sid1];
            //}
            $rpt['title']=$names[$sid1]." (".$techstr.")";
            $rpt['description']=$tech." spectrum of ".$names[$sid1];
            $rpt['author']=$jarray['ORIGIN'];
            $rpt['comment']="Upload of a JCAMP file by ".$this->Auth->user('fullname').". ORIGIN: ".$jarray['ORIGIN'].". OWNER: ".$jarray['OWNER'];
            $report=$this->Report->add($rpt);
            $rptid=$report['id'];

            //debug($report);

            // PropertyType is the type of spectrum it is, look up in table to get id
            $pro=$this->Propertytype->find('first',['conditions'=>['code'=>$techcode]]);
            $proid=$pro['Propertytype']['id'];

            // Add the file information
            $data['user_id']=$this->Auth->user('id');
            $data['substance_id']=$sid1;
            $data['report_id']=$rptid;
            $file=$this->File->add($data);
            $filid=$file['id'];
            $pathid=str_pad($filid,9,"0",STR_PAD_LEFT);

            // Move file to storage location (based on extension)
            $ext = pathinfo($data['name'], PATHINFO_EXTENSION);
            if($ext=="jdx"||$ext=="dx") {
                $path="download".DS."jdx";
                new Folder(WWW_ROOT.$path,true,0777);
                $path.=DS.$pathid.".jdx";
                move_uploaded_file($tmpname,WWW_ROOT.$path);
                $this->File->save(['id'=>$filid,'path'=>"/".$path]);

                // Get version of format and technique type and add to file data
                $this->File->saveField('version',$jarray['JCAMPDX']); // JCAMP version
                $tec=$this->Technique->find('first',['conditions'=>['matchstr'=>$techcode]]);
                $this->File->save(['id'=>$filid,'technique_id'=>$tec['Technique']['id']]); // JCAMP Data Type

                // Save XML
                $path="download".DS."xml";
                new Folder(WWW_ROOT.$path,true,0777);
                $path.=DS.$pathid.'.xml';
                $fp=fopen(WWW_ROOT.$path,'w');
                fwrite($fp,$this->Jcamp->makexml($jarray));
                fclose($fp);
            }

            //debug($file);

            // Add a Reference

            // Add Dataset
            $set=['report_id'=>$rptid,'file_id'=>$filid,'propertytype_id'=>$proid];
            $set['setType']="property value";
            $set['property']=$techprop;
            if(isset($jarray['XYDATA'])) {
                $set['format']=$jarray['XYDATA'];
            } elseif (isset($jarray['XYPOINTS'])) {
                $set['format']=$jarray['XYPOINTS'];
            } elseif (isset($jarray['NTUPLES'])) {
                $set['format']=$jarray['NTUPLES'];
            } elseif (isset($jarray['PEAKTABLE'])) {
                $set['format']=$jarray['PEAKTABLE'];
            } else {
                $set['format']="unknown";
            }
            $set['format']=strtolower($set['format']);
            $set['kind']="spectrum";
            $dataset=$this->Dataset->add($set);
            $setid=$dataset['id'];

            //debug($dataset);

            // Add context
            $con=['dataset_id'=>$setid,'discipline'=>'Chemistry','subdiscipline'=>'Analytical Chemistry','aspects'=>'system'];
            $context=$this->Context->add($con);
            $conid=$context['id'];

            // Add system to context
            $this->ContextsSystem->add(['context_id'=>$conid,'system_id'=>$sysid]);

            // Add sample
            $sam['identifier']=$techid;
            if($sid2!="") {
                $sam['title']="Solution of ".$names[$sid1]." in ".$names[$sid2];
            } else {
                $sam['title']="Pure ".$names[$sid1];
            }
            $sam['description']="Prepared for ".$techstr." analysis";
            $sam['dataset_id']=$setid;
            $sam['system_id']=$sysid;
            $sample=$this->Sample->add($sam);
            $samid=$sample['id'];

            //debug($sample);

            // Add annotation to sample if needed
            $smeta=[];$anns=$this->Jcampldr->find('list',['fields'=>['id','arrayname'],'conditions'=>['scidata'=>'sample']]);
            foreach($anns as $ann) { if(isset($jarray[$ann])) { $smeta[]=$ann; } }
            if(!empty($smeta)) {
                $samann=['sample_id'=>$samid,'class'=>'sample'];
                $annotation=$this->Annotation->add($samann);
                $annid=$annotation['id'];
                foreach($smeta as $ann) {
                    $field=$this->Jcampldr->field('title',['arrayname'=>$ann]);
                    $unit=$this->Jcampldr->field('unit',['arrayname'=>$ann]);
                    $value=$jarray[$ann];
                    if(!empty($unit)) { $value.=" ".$unit; }
                    $sammet=['annotation_id'=>$annid,'field'=>$field,'value'=>$value];
                    $this->Metadata->add($sammet);
                }
            }

            // Add methodology
            $met=['dataset_id'=>$setid,'evaluation'=>"experimental",'aspects'=>'measurement'];
            $methodology=$this->Methodology->add($met);
            $metid=$methodology['id'];

            //debug($methodology);

            // Add measurement
            $nmr['1H']=["250"=>"38.376","300"=>"46.051","400"=>"61.401","500"=>"76.753"];     // NMR larmor frequency => Tesla
            $nmr['13H']=["250"=>"62.860","300"=>"75.432","400"=>"100.576","500"=>"125.721"];  // NMR larmor frequency => Tesla
            $mea['methodology_id']=$metid;
            $mea['techniqueType']="spectroscopic";
            $mea['technique']=$techprop;
            if($tech=="NMR") {
                $frq="?";
                if(isset($jarray['PARAMS']['OBSERVEFREQUENCY'])&&!empty($jarray['PARAMS']['OBSERVEFREQUENCY'])) {
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
            } else {
                $mea['instrumentType']=$tech;
            }
            $mea['instrument']="";
            if(isset($jarray['SPECTROMETERDATASYSTEM'])) {
                $mea['instrument']=$jarray['SPECTROMETERDATASYSTEM'];
            }
            if(isset($jarray['DATAPROCESSING'])) { } // Issue 7
            $measurement=$this->Measurement->add($mea);
            $meaid=$measurement['id'];

            //debug($measurement);

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
                    $this->Setting->add($setn);
                    //debug($setting);
                }
            }

            // Add data
            foreach($jarray['DATA'] as $darray) {

                // Add dataseries
                $ser=['dataset_id'=>$setid,'type'=>'spectrum','format'=>$set['format'],'level'=>$level,'processedType'=>$processed];
                $dataseries=$this->Dataseries->add($ser);
                $serid=$dataseries['id'];

                //debug($dataseries);

                // Add conditions on the dataseries if present
                $conopts=['PATHLENGTH','PRESSURE','TEMPERATURE'];
                foreach($conopts as $opt) {
                    if(isset($jarray[$opt])) {
                        $con=['dataseries_id'=>$serid,'number'=>$jarray[$opt]];
                        $con['title']=$this->Jcampldr->field('title',['arrayname'=>$opt]);
                        $con['property_id']=$this->Jcampldr->field('property_id',['arrayname'=>$opt]);
                        $con['unit_id']=$this->Jcampldr->field('unit_id',['arrayname'=>$opt]);
                        $this->$this->Condition->add($con);
                    }
                }

                // Add annotation on dataseries or 'origin' class of metadata
                $ann=['dataseries_id'=>$serid,'class'=>'origin','comment'=>'Origin metadata is about the original file'];
                $annotation=$this->Annotation->add($ann);
                $annid=$annotation['id'];

                //debug($annotation);

                // Add the origin metadata
                $metaarray=['filename:text'=>$data['name'],'filetype:text'=>'jcamp','version:text'=>$jarray['JCAMPDX'],
                        'date:text'=>$jarray['DATETIME'],'owner:text'=>$jarray['OWNER'],'asdfType:text'=>$darray['asdftype'],
                        'fileComments:json'=>json_encode($jarray['COMMENTS']),'conversionErrors:json'=>json_encode($jarray['ERRORS'])];
                foreach($metaarray as $key=>$val) {
                    list($field,$format)=explode(":",$key);
                    $this->Metadata->add(['annotation_id'=>$annid,'field'=>$field,'value'=>$val,'format'=>$format]);
                    //debug($meta);
                }

                // Add descriptors to dataseries
                $descs=['NPOINTS','FIRSTX','LASTX','MAXX','MINX','MAXY','MINY','XFACTOR','YFACTOR','FIRSTY','DELTAX','RESOLUTION'];
                foreach($descs as $desc) {
                    if(isset($jarray[$desc])) {
                        $jarray[$desc]=(float) $jarray[$desc]; // Cast string to float (removes trailing zeros)
                        // property_id, value, unit, dataseries_id, title
                        $des['property_id']=$this->Jcampldr->field('property_id',['ldr'=>$desc]);
                        $des['title']=$this->Jcampldr->field('title',['ldr'=>$desc]);
                        $des['dataseries_id']=$serid;
                        $des['number']=$jarray[$desc];
                        if(in_array($desc,['FIRSTX','LASTX','MAXX','MINX','XFACTOR','DELTAX'])) {
                            $des['unit_id']=$xunitid;
                        } elseif(in_array($desc,['MAXY','MINY','YFACTOR'])) {
                            $des['unit_id']=$yunitid;
                        }
                        $this->Descriptor->add($des);
                    }
                }

                // Add datapoint (only one as the two arrays will be stored a single row in table)
                $dpt=['dataseries_id'=>$serid,'row_index'=>1];
                $point=$this->Datapoint->add($dpt);
                $dptid=$point['id'];

                //debug($point);

                // Split out the x and y values into separate arrays
                $x=$y=[];
                foreach($darray['pro'] as $x1=>$y1) { $x[]=(float) $x1;$y[]=(float) $y1; }

                // Add condition to datapoint - this is the x axis data (independent)
                $con=['datapoint_id'=>$dptid,'number'=>json_encode($x),'datatype'=>'json',
                        'property_id'=>$xpropid,'title'=>$xlabel,'unit_id'=>$xunitid];
                $this->Condition->add($con);

                // Add data to datapoint - this is the y axis data (dependent) as an array
                $dat=['datapoint_id'=>$dptid,'number'=>json_encode($y),'datatype'=>'json',
                    'property_id'=>$ypropid,'title'=>$ylabel,'unit_id'=>$yunitid];
                $this->Data->add($dat);
            }

            // Add activity
            $act=['user_id'=>$this->Auth->user('id'),'file_id'=>$this->File->id,'step_num'=>1,'type'=>'upload'];
            $this->Activity->add($act);

            // Redirect to view
            $this->redirect('/reports/view/' . $rptid);
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
        $this->redirect(['action' => 'index']);
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

    public function test()
    {
        $sarray=["00001","00003"];
        echo "SARRAY: ";debug($sarray);
        $resp=$this->SubstancesSystem->findUnique($sarray);
        echo "RESP: ";debug($resp);exit;
    }
}