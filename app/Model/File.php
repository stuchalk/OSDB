<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class File
 * File model
 * The representation of a file that contained the data
 */
class File extends AppModel
{
    public $format=0;

    public $belongsTo = ['Substance','Technique'];

    public $hasOne = ['Dataset'=>['foreignKey'=>'file_id','dependent'=>true]];

    public $hasMany = ['Activity'=>['foreignKey'=>'file_id','dependent'=>true]];

    /**
     * Convert JCAMP file and add to database
     * @param array $data: Uploaded file and metadata
     * @return integer $propertyID: returned the found property id if it exist.
     */
    public function convert($data=[])
    {
        // Links to other models
        $Act = ClassRegistry::init('Activity');
        $Ann = ClassRegistry::init('Annotation');
        $Coll = ClassRegistry::init('Collection');
        $Cond = ClassRegistry::init('Condition');
        $Ctx = ClassRegistry::init('Context');
        $CtxSys = ClassRegistry::init('ContextsSystem');
        $Data = ClassRegistry::init('Data');
        $Desc = ClassRegistry::init('Descriptor');
        $Iden = ClassRegistry::init('Identifier');
        $Jcamp = ClassRegistry::init('Jcamp.Jcamp');
        $Jcampldr = ClassRegistry::init('Jcampldr');
        $Meta = ClassRegistry::init('Metadata');
        $Meth = ClassRegistry::init('Methodology');
        $Meas = ClassRegistry::init('Measurement');
        $Point = ClassRegistry::init('Datapoint');
        $Prop = ClassRegistry::init('Property');
        $Proptype = ClassRegistry::init('Propertytype');
        $Rep = ClassRegistry::init('Report');
        $Samp = ClassRegistry::init('Sample');
        $Series = ClassRegistry::init('Dataseries');
        $Set = ClassRegistry::init('Dataset');
        $Sett = ClassRegistry::init('Setting');
        $Sub = ClassRegistry::init('Substance');
        $SubSys = ClassRegistry::init('SubstancesSystem');
        $Sys = ClassRegistry::init('System');
        $Tech = ClassRegistry::init('Technique');
        $Unit = ClassRegistry::init('Unit');

        $data['name']=$data['File']['file']['name'];
        $data['size']=$data['File']['file']['size'];
        $data['type']=$data['File']['file']['type'];
        if(isset($data['File']['source_id'])) {
            $source=$data['File']['source_id'];
        } else {
            $source=0;
        }
        $tmpname=$data['File']['file']['tmp_name'];
        $uid=$data['File']['user_id'];
        unset($data['File']['file']);

        // Create data array
        $file2=file($tmpname, FILE_IGNORE_NEW_LINES);
        $jarray=$Jcamp->convert($file2);

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
                $xpropid=$Prop->field('id',['name'=>"Radiofrequency"]);
                $xunitid=$Unit->field('id',['symbol'=>$xunit]);
                $level="processed";$processed="frequency";
            }
            if(strtolower($jarray['YUNITS'])=="arbitrary units") {
                $ylabel="Signal (Arbitrary Units)";
                $ypropid=$Prop->field('id',['name'=>"Signal Strength"]);
                $yunitid=$Unit->field('id',['symbol'=>""]);
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
                $xpropid=$Prop->field('id',['name'=>"Mass-to-Charge Ratio"]);
                $xunitid=$Unit->field('id',['symbol'=>$xunit]);
            }
            if(strtolower($jarray['YUNITS'])=="relative abundance") {
                $ylabel="Signal (Arbitrary Units)";
                $ypropid=$Prop->field('id',['name'=>"Relative Abundance"]);
                $yunitid=$Unit->field('id',['symbol'=>""]);
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
                $xpropid=$Prop->field('id',['name'=>"Wavenumber"]);
                $xunitid=$Unit->field('id',['symbol'=>$xunit]);
            }
            if(strtolower($jarray['YUNITS'])=="transmittance") {
                $ylabel="Transmittance";
                $ypropid=$Prop->field('id',['name'=>"Transmittance"]);
                $yunitid=$Unit->field('id',['symbol'=>"%"]);
                $level="processed";$processed="transmittance";
            }
        }
        $tec=$Tech->find('first',['conditions'=>['matchstr'=>$techcode]]);
        $tecid=$tec['Technique']['id']; // id of technique in table

        // Define contents first

        // Get main substance_id
        if($data['File']['substance_id']=='') {
            $chm=$data['File']['substance'];
            $sid1=$this->getChem($chm); // Adds substance if it does not exist
            $data['File']['substance_id']=$sid1;
        } else {
            $sid1=$data['File']['substance_id'];
        }
        $analid=$sid1;

        // Add solvent into substances if there is one
        $sid2="";
        if(isset($jarray['PARAMS']['SOLVENTNAME'])) {
            $sol=$jarray['PARAMS']['SOLVENTNAME'];
            $sub2=$Iden->find('first',['conditions'=>['value like'=>'%'.$sol.'%']]);
            if(empty($sub2)) {
                $sid2=$this->getChem($sol); // Adds substance if it does not exist
            } else {
                $sid2=$sub2['Identifier']['substance_id'];
            }
        }
        $solvid=$sid2;

        // Add system
        // Get system_id if the system already exists
        $sarray=[$sid1];
        if($sid2!="") { $sarray[]=$sid2; }
        $sysid=$SubSys->findUnique($sarray);
        if(count($sarray)==1) {
            $names=$Sub->find('list',['fields'=>['id','name'],'conditions'=>['id'=>$sid1]]);
        } else {
            $names=$Sub->find('list',['fields'=>['id','name'],'conditions'=>['id in'=>$sarray]]);
        }
        if(is_null($sysid)) {
            if($sid2!="") {
                $sys['name']=$names[$sid1]." in ".$names[$sid2];
                $sys['description']="A mixture of two organic compounds";
            } else {
                $sys['name']=$names[$sid1];
                $sys['description']="A pure organic compound";
            }
            $sys['type']="Single phase liquid";
            $system=$Sys->add($sys);
            $sysid=$system['id'];
            // Add substances_systems entries
            $SubSys->add(['substance_id'=>$sid1,'system_id'=>$sysid]);
            if($sid2!="") {
                $SubSys->add(['substance_id'=>$sid2,'system_id'=>$sysid]);
            }
        }

        // Add report - the representation of the data on the website
        $rpt['user_id']=$uid;
        $rpt['analyte_id']=$analid;
        $rpt['technique_id']=$tecid;
        $rpt['title']=$names[$sid1]." (".$techstr.")";
        $rpt['description']=$tech." spectrum of ".$names[$sid1];
        $rpt['author']=$jarray['ORIGIN'];
        if(AuthComponent::user('id')) {
            $fullname=AuthComponent::user('fullname');
        } else {
            $fullname="Anonymous"; // If anonymous upload...
        }
        $rpt['comment']="Upload of a JCAMP file by ".$fullname.". ORIGIN: ".$jarray['ORIGIN'].". OWNER: ".$jarray['OWNER'];
        $report=$Rep->add($rpt);
        $rptid=$report['id'];

        //debug($report);

        // PropertyType is the type of spectrum it is, look up in table to get id
        $pro=$Proptype->find('first',['conditions'=>['code'=>$techcode]]);
        $proid=$pro['Propertytype']['id'];

        // Add the file information
        $data['user_id']=$uid;
        $data['substance_id']=$sid1;
        $data['report_id']=$rptid;
        $data['source_id']=$source;
        $file=$this->add($data);
        $filid=$file['id'];
        $pathid=str_pad($filid,9,"0",STR_PAD_LEFT);

        // Move file to storage location (based on extension)
        $ext = pathinfo($data['name'], PATHINFO_EXTENSION);
        if($ext=="jdx"||$ext=="dx") {
            $path="download".DS."jdx";
            new Folder(WWW_ROOT.$path,true,0777);
            $path.=DS.$pathid.".jdx";
            move_uploaded_file($tmpname,WWW_ROOT.$path);
            $this->save(['id'=>$filid,'path'=>"/".$path]);

            // Get version of format and technique type and add to file data
            $this->saveField('version',$jarray['JCAMPDX']); // JCAMP version
            $this->save(['id'=>$filid,'technique_id'=>$tecid]); // JCAMP Data Type (using $techid from above)

            // Save XML
            $path="download".DS."xml";
            new Folder(WWW_ROOT.$path,true,0777);
            $path.=DS.$pathid.'.xml';
            $fp=fopen(WWW_ROOT.$path,'w');
            fwrite($fp,$Jcamp->makexml($jarray));
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
        $dataset=$Set->add($set);
        $setid=$dataset['id'];

        //debug($dataset);

        // Add context
        $con=['dataset_id'=>$setid,'discipline'=>'Chemistry','subdiscipline'=>'Analytical Chemistry','aspects'=>'system'];
        $context=$Ctx->add($con);
        $conid=$context['id'];

        // Add system to context
        $CtxSys->add(['context_id'=>$conid,'system_id'=>$sysid]);

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
        $sample=$Samp->add($sam);
        $samid=$sample['id'];

        //debug($sample);

        // Add annotation to sample if needed
        $smeta=[];$anns=$Jcampldr->find('list',['fields'=>['id','arrayname'],'conditions'=>['scidata'=>'sample']]);
        foreach($anns as $ann) { if(isset($jarray[$ann])) { $smeta[]=$ann; } }
        if(!empty($smeta)) {
            $samann=['sample_id'=>$samid,'class'=>'sample'];
            $annotation=$Ann->add($samann);
            $annid=$annotation['id'];
            foreach($smeta as $ann) {
                $field=$Jcampldr->field('title',['arrayname'=>$ann]);
                $unit=$Jcampldr->field('unit',['arrayname'=>$ann]);
                $value=$jarray[$ann];
                if(!empty($unit)) { $value.=" ".$unit; }
                $sammet=['annotation_id'=>$annid,'field'=>$field,'value'=>$value];
                $Meta->add($sammet);
            }
        }

        // Add methodology
        $met=['dataset_id'=>$setid,'evaluation'=>"experimental",'aspects'=>'measurement'];
        $methodology=$Meth->add($met);
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

        if(isset($jarray['BRUKER'])) {
            $mea['vendor']="Bruker";
        }
        if(isset($jarray['SPECTROMETERDATASYSTEM'])) {
            if(stristr($jarray['SPECTROMETERDATASYSTEM'],"Perkin-Elmer")||stristr($jarray['SPECTROMETERDATASYSTEM'],"PerkinElmer")) {
                $mea['vendor']="PerkinElmer";
                $jarray['SPECTROMETERDATASYSTEM']=trim(str_ireplace(['Perkin-Elmer','PerkinElmer'],"",$jarray['SPECTROMETERDATASYSTEM']));
            } elseif (stristr($jarray['SPECTROMETERDATASYSTEM'],"Bruker")) {
                $mea['vendor']="Bruker";
                $jarray['SPECTROMETERDATASYSTEM']=trim(str_ireplace(['Bruker'],"",$jarray['SPECTROMETERDATASYSTEM']));
            }
        }
        if(isset($jarray['ORIGIN'])) {
            if(stristr($jarray['SPECTROMETERDATASYSTEM'],"Perkin-Elmer")||stristr($jarray['SPECTROMETERDATASYSTEM'],"PerkinElmer")) {
                $mea['vendor']="PerkinElmer";
            } elseif (stristr($jarray['SPECTROMETERDATASYSTEM'],"Bruker")) {
                $mea['vendor']="Bruker";
            }
        }
        if(isset($jarray['SPECTROMETERDATASYSTEM'])) {
            $mea['instrument']=$jarray['SPECTROMETERDATASYSTEM'];
        }
        if(isset($jarray['DATAPROCESSING'])) {
            $mea['processing']=$jarray['DATAPROCESSING'];
        }

        $measurement=$Meas->add($mea);
        $meaid=$measurement['id'];

        //debug($measurement);

        // Add settings
        // Settings are from the PARAMS and BRUKER arrays
        $contain=['Property'=>['fields'=>['Property.name'],'Quantity'=>['fields'=>['Quantity.name','Quantity.si_unit'],'Unit'=>['fields'=>['Unit.symbol']]]]];
        $settings=$Jcampldr->find('all',['fields'=>['Jcampldr.arrayname','Jcampldr.unit'],'conditions'=>['scidata'=>'settings'],'contain'=>$contain]);
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
                $Sett->add($setn);
                //debug($setting);
            }
        }

        // Add data
        foreach($jarray['DATA'] as $darray) {

            // Add dataseries
            $ser=['dataset_id'=>$setid,'type'=>'spectrum','format'=>$set['format'],'level'=>$level,'processedType'=>$processed];
            $dataseries=$Series->add($ser);
            $serid=$dataseries['id'];

            //debug($dataseries);

            // Add conditions on the dataseries if present
            $conopts=['PATHLENGTH','PRESSURE','TEMPERATURE'];
            foreach($conopts as $opt) {
                if(isset($jarray[$opt])) {
                    $con=['dataseries_id'=>$serid,'number'=>$jarray[$opt]];
                    $con['title']=$Jcampldr->field('title',['arrayname'=>$opt]);
                    $con['property_id']=$Jcampldr->field('property_id',['arrayname'=>$opt]);
                    $con['unit_id']=$Jcampldr->field('unit_id',['arrayname'=>$opt]);
                    $Cond->add($con);
                }
            }

            // Add annotation on dataseries or 'origin' class of metadata
            $ann=['dataseries_id'=>$serid,'class'=>'origin','comment'=>'Origin metadata is about the original file'];
            $annotation=$Ann->add($ann);
            $annid=$annotation['id'];

            //debug($annotation);

            // Add the origin metadata
            $metaarray=['filename:text'=>$data['name'],'filetype:text'=>'jcamp','version:text'=>$jarray['JCAMPDX'],
                'date:text'=>$jarray['DATETIME'],'owner:text'=>$jarray['OWNER'],'asdfType:text'=>$darray['asdftype'],
                'fileComments:json'=>json_encode($jarray['COMMENTS']),'conversionErrors:json'=>json_encode($jarray['ERRORS'])];
            foreach($metaarray as $key=>$val) {
                list($field,$format)=explode(":",$key);
                $Meta->add(['annotation_id'=>$annid,'field'=>$field,'value'=>$val,'format'=>$format]);
                //debug($meta);
            }

            // Add descriptors to dataseries
            $descs=['NPOINTS','FIRSTX','LASTX','MAXX','MINX','MAXY','MINY','XFACTOR','YFACTOR','FIRSTY','DELTAX','RESOLUTION'];
            foreach($descs as $desc) {
                if(isset($jarray[$desc])) {
                    $jarray[$desc]=(float) $jarray[$desc]; // Cast string to float (removes trailing zeros)
                    // property_id, value, unit, dataseries_id, title
                    $des['property_id']=$Jcampldr->field('property_id',['ldr'=>$desc]);
                    $des['title']=$Jcampldr->field('title',['ldr'=>$desc]);
                    $des['dataseries_id']=$serid;
                    $des['number']=$jarray[$desc];
                    if(in_array($desc,['FIRSTX','LASTX','MAXX','MINX','XFACTOR','DELTAX'])) {
                        $des['unit_id']=$xunitid;
                    } elseif(in_array($desc,['MAXY','MINY','YFACTOR'])) {
                        $des['unit_id']=$yunitid;
                    }
                    $Desc->add($des);
                }
            }

            // Add datapoint (only one as the two arrays will be stored a single row in table)
            $dpt=['dataseries_id'=>$serid,'row_index'=>1];
            $point=$Point->add($dpt);
            $dptid=$point['id'];

            //debug($point);

            // Split out the x and y values into separate arrays
            $x=$y=[];
            foreach($darray['pro'] as $x1=>$y1) { $x[]=(float) $x1;$y[]=(float) $y1; }

            // Add condition to datapoint - this is the x axis data (independent)
            $con=['datapoint_id'=>$dptid,'number'=>json_encode($x),'datatype'=>'json',
                'property_id'=>$xpropid,'title'=>$xlabel,'unit_id'=>$xunitid];
            $Cond->add($con);

            // Add data to datapoint - this is the y axis data (dependent) as an array
            $dat=['datapoint_id'=>$dptid,'number'=>json_encode($y),'datatype'=>'json',
                'property_id'=>$ypropid,'title'=>$ylabel,'unit_id'=>$yunitid];
            $Data->add($dat);
        }

        // Add activity
        $act=['user_id'=>$uid,'file_id'=>$this->id,'step_num'=>1,'type'=>'upload'];
        $Act->add($act);

        // Return report id
        return $rptid;
    }

    /**
     * Using Pubchem, get the metadata about the compounds
     * @param $name
     * @return mixed
     */
    public function getChem($name)
    {
        $Chm = ClassRegistry::init('Pubchem.Chemical');
        $Sub = ClassRegistry::init('Substance');
        $Idn = ClassRegistry::init('Identifier');

        $nih=$Chm->check($name);
        preg_match('/[a-z]/i', $name, $match, PREG_OFFSET_CAPTURE);
        $first=strtoupper($name[$match[0][1]]); // Gets the first alphanumeric char in name
        $sub=$Sub->add(['name'=>ucfirst(strtolower($name)),'formula'=>$nih['MolecularFormula'],'molweight'=>$nih['MolecularWeight'],'first'=>$first]);
        $sid=$sub['id'];
        $iarray=['CID'=>'pubchemId','CanonicalSMILES'=>'smiles','InChI'=>'inchi','InChIKey'=>'inchikey','IUPACName'=>'iupacname'];
        foreach($iarray as $field=>$type) {
            $Idn->add(['substance_id'=>$sid,'type'=>$type,'value'=>$nih[$field]]);
        }
        // Get synonyms
        $syns=$Chm->synonyms($nih['CID']);
        foreach($syns as $syn) {
            $Idn->add(['substance_id'=>$sid,'type'=>'name','value'=>$syn]);
       }
        return str_pad($sid,5,"0",STR_PAD_LEFT);
        //debug($sub);exit;
    }

    /**
     * General function to add a new file
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='File';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}