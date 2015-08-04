<?php
/**
 * Created by PhpStorm.
 * User: n00002621
 * Date: 5/28/15
 * Time: 10:03 AM
 */

class Datarectification extends AppModel {

    public function checkAndAddSubstances(&$chemicals){
        $Substance=ClassRegistry::init('Substance'); //load the Substance model
        $Identifier=ClassRegistry::init('Identifier');//load the Identifier model
        foreach($chemicals as &$chemical){
            $ident=$Identifier->find('all',['conditions' => ['Identifier.value'=>$chemical['cas']]]);
            if(empty($ident)){ //we don't see this one in the database by CAS number

                $Substance->create(); //create and save new substance
                $chemInfo=['Substance'=>['name'=>$chemical['name'],'formula'=>$chemical['formula']]];
                $Substance->save($chemInfo);

                $Identifier->create(); //create and save the CAS identifier
                $identInfo=['Identifier'=>['type'=>'casrn','value'=>$chemical['cas'],'substance_id'=>$Substance->id]];
                $Identifier->save($identInfo);
                $chemical['id']=$Substance->id; //save substance id by reference
            }else{
                $ident[0]['Substance']['name']=$chemical['name'];
                $Identifier->save($ident[0]);
                $chemical['id']=$ident[0]['Substance']['id']; //we already have it in the database save id by reference
            }
        }

    }
    public function checkAndAddSystem(&$chemicals){
        $System=ClassRegistry::init('System');//load the System model
        $substances_systems=ClassRegistry::init('substances_systems');//load the join model
        $constArray=array();
        foreach($chemicals as &$chemical){
            $constArray[]['SubstanceSystem.substance_id']=$chemical['id'];
        }
        $joins = array(
            array(
                'table' => 'substances_systems',
                'alias' => 'SubstanceSystem',
                'type' => 'inner',
                'conditions' => array(
                    'System.id = SubstanceSystem.system_id',
                )
            )
        );
        $result=$System->find('all',[
                'conditions' => ['OR'=>$constArray] //,'Systems.total'=>count($constArray)
                ,
                'joins' => $joins,
                'fields' => ['System.id', 'COUNT(System.id) as Total'], //
                'group' => 'System.id HAVING Total = '.count($constArray),
               ]
        );

        if(empty($result)) {
            $System->create();
            $name="";
            $newChem=$chemicals;
            foreach($newChem as $i=>$chem){
                if($i==count($newChem)-1){
                    $name.="and ";
                }
                $name.=$chem['name']." ";
            }
            $data=['System'=>['name'=>$name,'description'=>'','type'=>'','identifier'=>'']];
            $System->save($data);
            foreach($newChem as $chem){
                $substances_systems->create();
                $data=['substances_systems'=>['substance_id'=>$chem['id'],'system_id'=>$System->id]];
                $substances_systems->save($data);
            }
            return $System->id;


        }else{
            $name="";
            $newChem=$chemicals;
            foreach($newChem as $i=>$chem){
                if($i==count($newChem)-1){
                    $name.="and ";
                }
                $name.=$chem['name']." ";
            }
            $result[0]['System']['name']=$name;
            $System->save($result[0]);
            return $result[0]['System']['id'];
        }
    }
    public function checkAndAddDataSetAndReport(&$data){
        $System=ClassRegistry::init('System'); //load the Substance model
        $Dataset=ClassRegistry::init('Dataset');//load the Dataset model
        $Report=ClassRegistry::init('Report');//load the Report model
        $Reference=ClassRegistry::init('Reference');//load the Reference model
        $refs=$this->getReference($data['file_id']);
        $dataset=$Dataset->find('all',['conditions' => ['Dataset.system_id'=>$data['systemID'],'Dataset.file_id'=>$data['file_id']]]);
        if($refs) {
            $result=$Reference->find('all',['conditions' => ['title'=>$refs['Reference']['title'],'year'=>$refs['Reference']['year']]]);
            $match=false;
            if(!empty($Reference)){
                foreach($result as $ref){ //check all the references that we got back and see if all of their values match all the values we retrieved from the file
                    $match=true;
                    foreach($ref['Reference'] as $key=>$value){
                        if(isset($refs['Reference'][$key])&&$refs['Reference'][$key]!==$value){
                            $match=false;
                            continue 2;
                        }
                    }
                    if($match===true){
                        $Reference->id=$ref['Reference']['id'];
                        break;
                    }
                }
            }
            if($match===false) {
                $Reference->create();
                $Reference->save($refs);
            }
        }else{
            $Reference->id=0;
        }
        $system=$System->find('list',['conditions' => ['id'=>$data['systemID']],'fields'=>['id','name']]);
        $title=substr($data['File']['Publication']['title'],0,strpos($data['File']['Publication']['title'],":"));
        $title.=" : ".array_pop($system);
        $reportArray=["Report"=>['title'=>$title,"file_code"=>$data['fileNum'],'publication_id'=>$data['File']['Publication']['id']]];
        if(!empty($dataset)) { //we don't see this one in the database by CAS number
            foreach($dataset as $set){
                $Report->delete($set['Dataset']['report_id']);
                $Dataset->delete($set['Dataset']['id'],true);
            }
        }
        $Report->create();
        $Report->save($reportArray);
        $dataArray=['Dataset'=>['file_id'=>$data['file_id'],'propertytype_id'=>$data['propertytype_id'],'system_id'=>$data['systemID'],'reference_id'=>$Reference->id,'report_id'=>$Report->id]];
        $Dataset->create();
        $Dataset->save($dataArray);
        $data['dataset_id']=$Dataset->id;


    }
    private function getReference($fileID){
        $File=ClassRegistry::init('File');//load the Identifier model
        $file=$File->find('first',['conditions'=>['File.id'=>$fileID],'recursive'=>3]); //get the file of interest
        $file['File']['filename']=substr($file['File']['filename'],0,strpos($file['File']['filename'],"."));
        $fileToExtract=WWW_ROOT.'files'.DS.'refs'.DS.$file['File']['publication_id'].DS.$file['File']['filename'].".xml";// find the path to the file name
        if(!function_exists('simplexml_load_file')) {
            return false;
        }
        if(!file_exists($fileToExtract)){
            return false;
        }
        $xml=simplexml_load_file($fileToExtract);
        if(!$xml){
            return null;
        }
        $ref=$xml->Series->Book->Chapter->ChapterBackmatter->Bibliography->Citation->BibArticle; //get to the citation
        $reference=array('Reference'=>array());
        $reference['Reference']['journal']=(string)$ref->JournalTitle;
        $reference['Reference']['title']=(string)$ref->ArticleTitle;
        $reference['Reference']['year']=(string)$ref->Year;
        $reference['Reference']['volume']=(string)$ref->VolumeID;
        $reference['Reference']['startpage']=(string)$ref->FirstPage;
        $reference['Reference']['endpage']=(string)$ref->LastPage;
        $reference['Reference']['issue']="";
        if(isset($ref->Issue)){
            $reference['Reference']['issue']=(string)$ref->Issue;
        }
        $reference['Reference']['authors']="";
        foreach($ref->BibAuthorName as $author){
            $reference['Reference']['authors'].=(string)$author->Initials;
            $reference['Reference']['authors'].=" ".(string)$author->FamilyName.", ";
        }
        $reference['Reference']['authors']=substr($reference['Reference']['authors'],0,strlen($reference['Reference']['authors'])-2); //remove trailing comma
        return $reference;
    }
    public function addDataAndConditions(&$data,$propertyType){
        $Dataseries=ClassRegistry::init('Dataseries');//load the Dataseries model
        $Condition=ClassRegistry::init('Condition');//load the Condition model
        $Datapoint=ClassRegistry::init('Datapoint');//load the Condition model
        $Data=ClassRegistry::init('Data'); //load the Data model


        $dataSeries=array();
        $dataSeriesNum=count($data['Data'])/count($propertyType['Variable']); //calculate the number of data series needed for this data
        $propertiesCount=count($propertyType['Variable']);
        if(!is_int($dataSeriesNum)){
            // Set a session flash message and redirect.
            trigger_error('Column count not a multiple of variables types',E_USER_ERROR);
        }


        foreach($data['DataUnits'] as &$unit){ //cleanup for multiple units per column
            if(is_array($unit)){
                $unit=$unit[0];
            }
        }
        $remove=array();
        if($data['File']['File']['format']==='0'&&count($data['Parameters'])!==$dataSeriesNum){ //cases where its one series pretending to be two
            echo "MISMATCH";
            foreach($data['Data'] as $i=>$value){
                if(($i+1)>$propertiesCount){
                    $mod=$i%$propertiesCount;
                    $data['Data'][$mod]=array_merge($data['Data'][$mod],$data['Data'][$i]);
                    $remove[]=$i;

                }
            }
            $dataSeriesNum=1;
            foreach($remove as $id){
                unset($data['Data'][$id]);
            }
        }
        for($i=0;$i<$dataSeriesNum;$i++) { //loop through number of data series
            if( isset($data['Parameters'])&&
                isset($data['Parameters'][$i])) {
                if(is_array($data['Parameters'][$i])) { //if is an array we need to loop it
                    if($data['File']['File']['format']==='0'){ //if format is 0 then we need just add params
                        foreach ($data['Parameters'][$i] as $q => $param) {
                            $dataSeries[$i]['Parameters'][] = $param;
                        }
                    }else {
                        foreach ($data['Parameters'][$i] as $q => $param) { //format is not 0, loop and get error
                            if(strpos($data['ParametersUnit'][$i][$q], "E") !== 0){
                                $dataSeries[$i]['Parameters'][]=$param;
                                if (isset($data['ParametersUnit'][$i + 1][$q]) && strpos($data['ParametersUnit'][$i + 1][$q], "E") === 0) {
                                    $dataSeries[$i]['ParametersError'][]=$data['Parameters'][$i + 1][$q];
                                }
                            }
                        }
                    }
                }else{//if we only have one parameter its going to look like a string
                    $dataSeries[$i]['Parameters'][] = $data['Parameters'][$i];
                }
            }
            for($n=0;$n<count($propertyType['Variable']);$n++){ //loop through the columns in this data series
                foreach($data['Data'][$n+($i*$propertiesCount)] as $index=>$value){ //get the correct column from the data table
                    foreach($propertyType['Variable'] as $propVar) {
                        $mod=$n+1-((int)$data['File']['File']['format']===1&&$n>0?1:0);
                        if ($propVar['column #'] == $mod) {
                            if ($propVar['identifier'] == "Data") {
                                $dataSeries[$i]['Data'][] = $data['Data'][$n+($i*$propertiesCount)];

                                //get uncertainty
                                $dataSeries[$i]['DataProp'][]=$propVar['Property']['id'];
                                if($data['File']['File']['format']==='0') {
                                    $dataSeries[$i]['DataUncertainty'][] = $data['uncertainty'][$n];
                                }elseif($data['File']['File']['format']==='1') {
                                    $dataSeries[$i]['DataUncertainty'][] = $data['Data'][$n+($i*$propertiesCount)+1];
                                }


                                if(count($propVar['Unit'])===1) {
                                    $dataSeries[$i]['DataUnit'][] = $propVar['Unit'][0]['id'];
                                }elseif(count($propVar['Unit'])>1) {
                                    $found=false;
                                    foreach($propVar['Unit'] as $unit) {
                                        if ($unit['UnitsVariable']['header'] == $data['DataUnits'][$n + ($i * $propertiesCount)]) {
                                            $dataSeries[$i]['DataUnit'][] = $unit['id'];
                                            $found=true;
                                        }
                                    }
                                    if(!$found){
                                        trigger_error('Units not found for all data points '.$data['DataUnits'][$n + ($i * $propertiesCount)],E_USER_ERROR);
                                    }
                                }else{
                                    trigger_error('Units not found for all data points '.$data['DataUnits'][$n + ($i * $propertiesCount)],E_USER_ERROR);
                                }
                                break 2;
                            } else if ($propVar['identifier'] == "Condition") {
                                $dataSeries[$i]['Conditions'][] = $data['Data'][$n + ($i * $propertiesCount)];

                                //get uncertainty
                                $dataSeries[$i]['ConditionProp'][]=$propVar['Property']['id'];
                                if($data['File']['File']['format']==='0') {
                                    $dataSeries[$i]['ConditionUncertainty'][] = $data['uncertainty'][$n];
                                }elseif($data['File']['File']['format']==='1'){
                                    $dataSeries[$i]['ConditionUncertainty'][] = $data['Data'][$n+($i*$propertiesCount)+1];
                                }
                                //find correct unit
                                if(count($propVar['Unit'])===1) {
                                    $dataSeries[$i]['ConditionUnit'][] = $propVar['Unit'][0]['id'];
                                }elseif(count($propVar['Unit'])>1) {
                                    $found=false;
                                    foreach($propVar['Unit'] as $unit) {
                                        var_dump($unit['UnitsVariable']['header'] == $data['DataUnits'][$n + ($i * $propertiesCount)]);
                                        echo $unit['UnitsVariable']['header']." == ".$data['DataUnits'][$n + ($i * $propertiesCount)]."<br>";
                                        if ($unit['UnitsVariable']['header'] == $data['DataUnits'][$n + ($i * $propertiesCount)]) {
                                            $dataSeries[$i]['ConditionUnit'][] = $unit['id'];
                                            $found=true;
                                        }
                                    }
                                    if(!$found){
                                        trigger_error('Units not found for all data points '.$data['DataUnits'][$n + ($i * $propertiesCount)],E_USER_ERROR);
                                    }
                                }else{
                                    trigger_error('Units not found for all data points '.$data['DataUnits'][$n + ($i * $propertiesCount)],E_USER_ERROR);
                                }
                                break 2;
                            }
                        }
                    }
                }
            }
            if(isset($dataSeries[$i]['Parameters'])) {
                foreach ($dataSeries[$i]['Parameters'] as $index => $params) {
                    foreach ($propertyType['Parameter'] as $propParam) { //get the unit for this parameters
                        if ($propParam['parameter_num'] == $index + 1) {
                            if(count($propParam['Unit'])===1) {
                                $dataSeries[$i]['ParametersUnit'][] = $propParam['Unit'][0]['id'];
                            }elseif(count($propParam['Unit'])>1) {
                                $found=false;
                                foreach($propParam['Unit'] as $unit) {
                                    if(is_array($data['ParametersUnit'][$i])){
                                        if ($unit['ParametersUnit']['header'] == $data['ParametersUnit'][$i][$index]) {
                                            $dataSeries[$i]['ParametersUnit'][] = $unit['id'];
                                            $found = true;
                                        }
                                    }else {
                                        if ($unit['ParametersUnit']['header'] == $data['ParametersUnit'][$index]) {
                                            $dataSeries[$i]['ParametersUnit'][] = $unit['id'];
                                            $found = true;
                                        }
                                    }
                                }
                                if(!$found){
                                    trigger_error('Units not found for all parameters '.$data['ParametersUnit'][$i][$index],E_USER_ERROR);
                                }
                            }else{
                                trigger_error('Units not found for all parameters '.$data['ParametersUnit'][$i][$index],E_USER_ERROR);
                            }
                            $dataSeries[$i]['ParametersProp'][$index] = $propParam['Property']['id'];
                        }
                    }
                }
            }

        }
        foreach($dataSeries as $i=>$series){
            $Dataseries->create();
            $dataArray=['Dataseries'=>['dataset_id'=>$data['dataset_id'],'type'=>'independent set']]; //create and save a new data series
            $Dataseries->save($dataArray);
            if(isset($series['Parameters'])) {
                foreach ($series['Parameters'] as $index => $parameter) { //loop through the parameters for this dataseries and save them as conditons
                    $Condition->create();
                    $conditionArray = ['Condition' => ['dataseries_id' => $Dataseries->id,
                        'datatype' => 'datum',]];
                    $conditionArray['Condition']['unit_id'] = $series['ParametersUnit'][$index];
                    $conditionArray['Condition']['property_id'] = $series['ParametersProp'][$index];
                    $conditionArray['Condition']['number'] = $this->exponentialGen($parameter);
                    if (isset($series['ParametersError'][$index])) {
                        $error=$series['ParametersError'][$index];
                        if(strpos($error,"+")!==false){
                            $error=explode("+",$error);
                            $error=(float)$error[0]+abs((float)$parameter)*$error[1];
                        }
                        $conditionArray['Condition']['error'] = $this->exponentialGen($error);
                        $conditionArray['Condition']['accuracy'] = $this->calculateAccuracy($parameter,$error);
                    } else {
                        $conditionArray['Condition']['error'] = 0;
                        $conditionArray['Condition']['accuracy'] = $this->calculateAccuracy($parameter);
                    }
                    $conditionArray['Condition']['error_type'] = "relative";
                    $conditionArray['Condition']['exact'] = 0;
                    $conditionArray['Condition']['accuracy'] = $this->calculateAccuracy($parameter);
                    $Condition->save($conditionArray);
                }
            }

            foreach($series['Data'][0] as $q=>$values){ //loop through the columns in this data series
                $Datapoint->create();
                $Datapoint->save(['Datapoint'=>['dataseries_id'=>$Dataseries->id,'row_index'=>$q]]);
                for($index=0;$index<count($series['Data']);$index++) {
                    $dataArray = ['datapoint_id' => $Datapoint->id, 'datatype' => 'datum'];
                    $error = 0;
                    if (is_array($series['DataUncertainty'][$index])) {
                        $error = $series['DataUncertainty'][$index][$q];
                        if (strpos($error, "+") !== false) {
                            $error = explode("+", $error);
                            $error = (float)$error[0] + abs((float)$series['Data'][$index][$q]) * $error[1];
                        }
                        $dataArray['unit_id'] = $series['DataUnit'][$index][$q]; //get unit for value
                        $dataArray['property_id'] = $series['DataProp'][$index][$q];
                    } else {
                        $error = $series['DataUncertainty'][$index];
                        if (strpos($error, "+") !== false) {
                            $error = explode("+", $error);
                            $error = (float)$error[0] + abs((float)$series['Data'][$index][$q]) * $error[1];
                        }
                        $dataArray['unit_id'] = $series['DataUnit'][$index]; //get unit for value
                        $dataArray['property_id'] = $series['DataProp'][$index];
                    }


                    $dataArray['number'] = $this->exponentialGen($series['Data'][$index][$q]);
                    $dataArray['error'] = $this->exponentialGen($error);
                    $dataArray['error_type'] = "relative";
                    $dataArray['exact'] = 0;
                    $dataArray['accuracy'] = $this->calculateAccuracy($series['Data'][$index][$q], $error);
                    $Data->create();
                    $Data->save(['Data' => $dataArray]);
                }
                for($index=0;$index<count($series['Conditions']);$index++){

                    $conArray = ['datapoint_id'=>$Datapoint->id, 'datatype' => 'datum'];


                    $error=0; //determine if we have a universal error or a per point error
                    if(is_array($series['ConditionUncertainty'][$index])) {
                        $error = $series['ConditionUncertainty'][$index][$q];
                        if(strpos($error,"+")!==false){
                            $error=explode("+",$error);

                            $error=(float)$error[0]+abs((float)$series['Conditions'][$index][$q])*$error[1];
                        }
                        $conArray['unit_id'] = $series['ConditionUnit'][$index][$q];
                        $conArray['property_id'] = $series['ConditionProp'][$index][$q];
                    }else{
                        $error = $series['ConditionUncertainty'][$index];
                        if(strpos($error,"+")!==false){
                            $error=explode("+",$error);
                            $error=(float)$error[0]+abs((float)$series['Conditions'])*$error[1];
                        }
                        $conArray['unit_id'] = $series['ConditionUnit'][$index];
                        $conArray['property_id'] = $series['ConditionProp'][$index];
                    }
                    $conArray['number']=$this->exponentialGen($series['Conditions'][$index][$q]);
                    $conArray['error'] = $this->exponentialGen($error);
                    $conArray['error_type'] = "relative";
                    $conArray['exact'] = 0;
                    $conArray['accuracy'] = $this->calculateAccuracy($series['Conditions'][$index][$q],$error);
                    $Condition->create();
                    $Condition->save(['Condition'=>$conArray]);

                }
            }
        }
    }


    private function exponentialGen($string){// Generates a exponential number removing any zeros at the end not needed
        $length=0;
        $string=str_replace(",","",$string);
        $num=explode(".",$string);
        if($num[0]!=""&&$num[0]!=0){ //if the number before the decimal is not zero, add its length to length of exponential
            $length+=strlen($num[0]);
        }
        if(isset($num[1])){ //if we have something after the decimal
            $count=false; //do we start counting these numbers yet?
            $start=false;// Should we start checking numbers yet ? only useful to ignore numbers before E in expontent
            if(strpos(strtolower($num[1]),"e")===false){ //if were not an expontentiall assume all these numbers should be counted
                $start=true;
            }
            $num[1]=str_split($num[1]); //split it into array for reading
            $num[1]=array_reverse($num[1]); //reverse so that we start at the end and count up to prevent truncating important numbers
            foreach($num[1] as $char) { //loop through reversed decimal and look for first non zero, then accept all numbers after that
                if(is_numeric($char)&&($char!=0||$count)&&$start){
                    $length++;
                    $count=true;
                }
                if(strtolower($char)=="e"){
                    $start=true;
                }
            }
        }
        if($length==0){
            $length=1;
        }
        return sprintf("%." .($length-1). "e", $string);
    }

    private function calculateAccuracy($float,$error=0){
        $accuracy=0;
        $errorAcc=0;

        $float=str_replace(",","",$float);
        $errorArr = explode(".", (float)$error);
        $decimals = 0;
        if(isset($errorArr[1])) {
            $errorArr[1] = str_split($errorArr[1]);
            $decimals = 0;
            foreach ($errorArr[1] as $p => $char) { //calculate the correct number of decimal places
                if ($char != "0") {
                    $decimals = $p + 1;
                    break;
                }
            }
        }
        if($error) {
            $num = explode(".", number_format($float, $decimals));
        }else{
            $num = explode(".", number_format($float,10));
        }
        if($num[0]!=0){
            $num[0]=str_split($num[0]);
            foreach($num[0] as $char){
                if(is_numeric($char)){
                    $accuracy++;
                }
            }
        }
        if(isset($num[1])){
            $num[1]=str_split($num[1]);
            if($error==0){
                $num[1]=array_reverse($num[1]);
                $count=false;
                foreach($num[1] as $char) {
                    if(is_numeric($char)&&($char!=0||$count)){
                        $accuracy++;
                        $count=true;
                    }
                }

            }else {
                foreach ($num[1] as $char) {
                    if (is_numeric($char)) {
                        $accuracy++;
                    }
                }
            }
        }
        if($decimals==0&&$error!=0) {
            $errorAcc = $this->calculateAccuracy($error);
        }
        $accuracy-=$errorAcc;
        if($accuracy<1)
            $accuracy=1;
        return $accuracy;
    }
}
