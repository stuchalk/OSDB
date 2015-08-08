<?php

/**
 * Class FilesController
 */
class FilesController extends AppController {

    public $uses = ['File','Dataset','Substance','Jcamp','Propertytype','SubstancesSystem','Report','System','Identifier','Technique','Pubchem.Chemical','Activity','Animl.Jcamp'];

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
            $data['File']['name']=$data['File']['file']['name'];
            $data['File']['size']=$data['File']['file']['size'];
            $data['File']['type']=$data['File']['file']['type'];
            $tmpname=$data['File']['file']['tmp_name'];
            unset($data['File']['file']);

            // TODO: Mime type not correct for .jdx files

            // Create data array
            $file2=file($tmpname, FILE_IGNORE_NEW_LINES);
            $jarray=$this->Jcamp->convert($file2);

            // Create technique related variables from data in jarray
            $techstr="an unknown";
            if(isset($jarray['PARAMS']['OBSERVENUCLEUS'])) {
                $jarray['PARAMS']['OBSERVENUCLEUS']=str_replace("^","",$jarray['PARAMS']['OBSERVENUCLEUS']);
                $tech="NMR";
                $techprop="Nuclear Magnetic Resonance";
                $techstr=$jarray['PARAMS']['OBSERVENUCLEUS']." NMR";
                $techid="NMRSAMPLE";
                $techcode=$jarray['PARAMS']['OBSERVENUCLEUS']."NMR";
            }

            debug($jarray);exit;

            // Add solvent into substances to then make system
            $sid2="";
            if(isset($jarray['PARAMS']['SOLVENTNAME'])) {
                $sol=$jarray['PARAMS']['SOLVENTNAME'];
                $sub2=$this->Identifier->find('first',['conditions'=>['value like'=>'%'.$sol.'%']]);
                if(empty($sub2)) {
                    $sid2=$this->File->getChem($sol);
                } else {
                    $sid2=$sub2['Identifier']['substance_id'];
                }
            }

            // Add substance_id
            if($data['File']['substance_id']=='') {
                $chm=$data['File']['substance'];
                $sid1=$this->File->getChem($chm);
                $data['File']['substance_id']=$sid1;
            } else {
                $sid1=$data['File']['substance_id'];
            }

            // Add system
            $names=$this->Substance->find('list',['fields'=>['id','name'],'conditions'=>['id in'=>[$sid1,$sid2]]]);
            if($sid2!="") {
                $sys['name']=$names[$sid1]." in ".$names[$sid2];
            } else {
                $sys['name']=$names[$sid1]." in an unknown solvent";
            }
            $sys['identifier']=$techid;
            $sys['description']="Solution prepared for ".$techstr." analysis";
            $sys['type']="Single phase liquid"; // TODO add MAS check
            $this->System->create();
            $this->System->save(['System'=>$sys]);
            $sysid=$this->System->id;
            $this->SubstancesSystem->create();
            $this->SubstancesSystem->save(['SubstancesSystem'=>['substance_id'=>$sid1,'system_id'=>$sysid]]);
            if($sid2!="") {
                $this->SubstancesSystem->clear();
                $this->SubstancesSystem->create();
                $this->SubstancesSystem->save(['SubstancesSystem'=>['substance_id'=>$sid2,'system_id'=>$sysid]]);
            }

            // Add report
            // Report is the representation of the data on the website
            $rpt['user_id']=$this->Auth->user('id');
            $rpt['title']=$jarray['TITLE'];
            $rpt['description']=$tech." spectrum of ".$jarray['TITLE'];
            $rpt['comment']="Upload of a JCAMP file by ".$this->Auth->user('fullname').". ORIGIN: ".$jarray['ORIGIN'].". OWNER:".$jarray['OWNER'];
            $this->Report->create();
            $this->Report->save(['Report'=>$rpt]);
            $rptid=$this->Report->id;

            // PropertyType is the type of spectrum it is, look up in table to get id
            $pro=$this->Propertytype->find('first',['conditions'=>['code'=>$techcode]]);
            $proid=$pro['Propertytype']['id'];

            // Add File
            $this->File->create();
            if(!$this->File->save($data)){
                debug($this->File->validationErrors); die();
            }
            $filid=$this->File->id;
            $pathid=str_pad($this->File->id,9,"0",STR_PAD_LEFT);

            // Move file to storage location (based on extension)
            $ext = pathinfo($data['File']['name'], PATHINFO_EXTENSION);
            $path="files".DS.$ext;
            $folder = new Folder(WWW_ROOT.$path,true,0777);
            $path=$path.DS.$pathid.".".$ext;
            move_uploaded_file($tmpname,WWW_ROOT.$path);
            $this->File->saveField('path',"/".$path);

            // Get version of format
            $file=file_get_contents(WWW_ROOT.$path);
            if($ext=="jdx"||$ext=="dx") {
                // Detect line endings
                if(stristr($file,"\r\n")) {
                    $eol="\r\n";
                } elseif(stristr($file,"\r")) {
                    $eol="\r";
                } elseif(stristr($file,"\n")) {
                    $eol="\r";
                }
                // JCAMP version
                list(,$temp)=explode("JCAMP-DX=",$file,2);
                list($ver,)=explode(" ",$temp,2);
                $this->File->saveField('version',$ver);
                // JCAMP Data Type
                list(,$temp)=explode("DATA TYPE=",$file,2);
                list($type,)=explode($eol,$temp,2); // Must use ' here not "
                if(stristr($type,"NMR")) {
                    // Find nucleus
                    list(, $temp) = explode("OBSERVE NUCLEUS=", $file, 2);
                    list($nuc,) = explode($eol, $temp, 2); // Must use ' here not "
                    $tech = $this->Technique->find('first', ['conditions' => ['matchstr' => str_replace("^", "", $nuc) . "NMR"]]);
                }
                $this->File->saveField('technique_id',$tech['Technique']['id']);
            }

            // Add a Reference? TODO ?

            // Add Dataset
            $set=['report_id'=>$rptid,'file_id'=>$filid,'system_id'=>$sysid,'propertytype_id'=>$proid];
            $this->Dataset->create();
            $this->Dataset->save(['Dataset'=>$set]);
            $setid=$this->Dataset->id;

            // Distribute the data into the DB tables

            // Annotation on dataset
            // Defaults
            $ann['set']['type']="property_value";
            $ann['set']['property']=$techprop;
            if(isset($jarray['XYDATA'])) {
                $ann['set']['format']=$jarray['XYDATA'];
            } elseif (isset($jarray['XYPOINTS'])) {
                $ann['set']['format']=$jarray['XYPOINTS'];
            } elseif (isset($jarray['NTUPLES'])) {
                $ann['set']['format']=$jarray['NTUPLES'];
            } else {
                $ann['set']['format']="unknown";
            }
            $ann['set']['format']=strtolower($ann['set']['format']);
            $ann['set']['kind']="spectrum";

            // Annotation on methodology
            $ann['met']['evaluation']="experimental";

            // Annotation on measurement
            // NMR larmor frequency => Tesla
            $nmr['1H']=["250"=>"38.376","300"=>"46.051","400"=>"61.401","500"=>"76.753"];
            $nmr['13H']=["250"=>"62.860","300"=>"75.432","400"=>"100.576","500"=>"125.721"];

            $ann['mea']['measurementType']="spectroscopic";
            $ann['mea']['measurementMethod']=$techprop;
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
            $ann['mea']['instrumentType']=$frq." MHz NMR";
            $ann['mea']['instrument']=$jarray['SPECTROMETERDATASYSTEM'];
            if(isset($jarray['DATAPROCESSING'])) {
                // TODO
            }

            // Add methodology and then settings
            $this->Methodology->create();
            $this->Methodology->save(['Methodology'=>['dataset_id'=>$setid,'aspects'=>['measurement']]]);

            // Settings are from the PARAMS and BRUKER arrays
            $contain=['Property'=>['fields'=>['Property.name'],'Quantity'=>['fields'=>['Quantity.name','Quantity.si_unit'],'Unit'=>['fields'=>['Unit.symbol']]]]];
            $fields=['Jcamp.arrayname','Jcamp.unit'];
            $conditions=['scidata'=>'settings'];
            $settings=$this->Jcamp->find('all',['fields'=>$fields,'conditions'=>$conditions,'contain'=>$contain]);
            $params=array_merge($jarray['PARAMS'],$jarray['BRUKER']);
            foreach($settings as $s) {
                if(isset($params[$s['Jcamp']['arrayname']])) {
                    // Need property_id, value, unit
                }

            }

            debug($setid);exit;

            // Content of file is a dataset, multiple spectra, peak tables are dataseries
            // Reference is to the original file and user that uploaded it
            // Add a Group



            // Capture activity
            $this->Activity->create();
            $activity=['Activity'=>[
                'user_id'=>$this->Auth->user('id'),
                'file_id'=>$this->File->id,
                'step_num'=>1,
                'type'=>'upload',
                'comment'=>''
            ]];
            $this->Activity->save($activity);


            // Convert file to xml
            // Convert file to jcampxml format (interim format as PHP array)
            if($ext=="jdx"||$ext=="dx")
            {


                // Save XML
                $path="files".DS."jcampxml".DS.str_replace(array('jdx','dx','DX'),'xml',$upload['name']);
                $fp=fopen(WWW_ROOT.$path,'w');
                fwrite($fp,$jcamp->makexml());
                fclose($fp);

                // Show XML
                header("Location: /".$path);
                exit;
                $ldrs=$jcamp->getLdrs();
            }

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