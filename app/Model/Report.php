<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Report
 * Report model
 * A report is any publication about a dataset: the original article or a review article
 */
class Report extends AppModel
{
    public $hasOne = [
        'Dataset'=> ['foreignKey'=>'report_id','dependent' => true],
        'File'=> ['foreignKey'=>'report_id','dependent' => true]
    ];

    public $belongsTo = ['User'];

    public $hasMany = [
        'Annotation'=> ['foreignKey'=>'report_id','dependent' => true],
        //'CollectionsReport'=> ['foreignKey'=>'report_id','dependent' => true]
    ];

    public $hasAndBelongsToMany = ['Collection'];

    /**
     * General function to add a new report
     * @param array $data
     * @return integer
	 * @throws
     */
    public function add($data)
    {
        $model='Report';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

    /**
     * Get reports sorted by substance
     * Limit by user, collection
     * @param null $field
     * @param null $id
     * @return array|null
     */
    public function bySubstance($field=null,$id=null)
    {
        // TODO: rework to make is a find method so that it can be used with paginator
        $c=['Dataset'=>['fields'=>['id'],
                    'Context'=>['fields'=>['id'],
                        'System'=>['fields'=>['id'],
                            'Substance'=>['fields'=>['id','name'],
                                'Identifier'=>['fields'=>['id','type','value'],'conditions'=>['type'=>['inchikey','inchi']]]]]]]];
        $f=['Report.id','Report.title'];
        $o=['Report.title'];
        if($field=='user'&&!is_null($id)) {
            $cn=['user_id'=>$id];
            $reps=$this->find('all',['conditions'=>$cn,'fields'=>$f,'contain'=>$c,'order'=>$o,'recursive'=> -1]);
        } else if($field=='col'&&!is_null($id)) {
            $j=[['table'=>'collections_reports','alias'=>'CollectionsReport','type'=>'left','conditions'=>['Report.id = CollectionsReport.report_id'],
                    ['table'=>'collections','alias'=>'Collection','type'=>'left','conditions'=>['CollectionsReport.collection_id = Collection.id']]]];
            $cn=['CollectionsReport.collection_id'=>$id];
            $reps=$this->find('all',['conditions'=>$cn,'fields'=>$f,'contain'=>$c,'order'=>$o,'joins'=>$j,'recursive'=> -1]);
        } else if($field=='tech'&&!is_null($id)) {
            $cn=['technique_id'=>$id];
            $reps=$this->find('all',['conditions'=>$cn,'fields'=>$f,'contain'=>$c,'order'=>$o,'recursive'=> -1]);
        } else if($field=='sub'&&!is_null($id)) {
            $c=['Dataset'=>['fields'=>['id'],
                'Context'=>['fields'=>['id'],
                    'System'=>['fields'=>['id'],
                        'Substance'=>['fields'=>['id','name'],
                            'Identifier'=>['fields'=>['id','type','value'],'conditions'=>['Identifier.value LIKE'=>'%'.$id.'%']]]]]]];
            $reps=$this->find('all',['fields'=>$f,'contain'=>$c,'order'=>$o,'recursive'=> -1]);
        } else {
            $reps=$this->find('all',['fields'=>$f,'contain'=>$c]);
        }
        //debug($c);debug($reps);exit;
        $results=[];
        foreach($reps as $rep) {
            $sub=$rep['Dataset']['Context']['System'][0]['Substance'][0];
            $name=$sub['name'];
            $results[$name]['id']=$sub['id'];
            //debug($sub);
            if(count($sub['Identifier'])>1) {
                foreach($sub['Identifier'] as $ident) {
                    if($ident['type']=="inchikey") {
                        $results[$name]['inchikey']=$ident['value'];
                    } elseif($ident['type']=="inchi") {
                        $results[$name]['inchi']=$ident['value'];
                    }
                }
            } else {
                $results[$name]['inchikey']=$sub['Identifier'][0]['value'];
            }
            preg_match_all("/\(([a-zA-Z0-9\/ ]*)\)/",$rep['Report']['title'],$matches,PREG_SET_ORDER);
            $results[$name]['spectra'][$rep['Report']['id']]=$matches[(count($matches)-1)][1];
        }
        //debug($results);exit;

        ksort($results);
        //debug($results);exit;
        return $results;
    }

    /**
     * Returns DB data so that it can be used to generate scidata json
     * @param $id
     * @return mixed
     */
    public function scidata($id)
    {
        // Note: there is an issue with the retrival of substances under system if id is not requested as a field
        // This is a bug in CakePHP as it works without id if its at the top level...
        $contain=[
            'Collection',
            'User'=>['fields'=>['fullname']],
            'Dataset'=>['fields'=>['setType','property','kind'],
                'File','Reference',
                'Sample'=>['fields'=>['title','description'],
                    'Annotation'=>['Metadata'=>['fields'=>['field','value','format']]]],
                'Methodology'=>['fields'=>['evaluation','aspects'],
                    'Measurement'=>['fields'=>['techniqueType','technique','instrumentType','instrument','vendor','processing'],
                        'Setting'=>['fields'=>['number','text','unit_id'],
                            'Property'=>['fields'=>['name'],
                                'Quantity'=>['fields'=>['name']]],
                            'Unit'=>['fields'=>['name','symbol']]]]],
                'Context'=>['fields'=>['discipline','subdiscipline'],
                    'System'=>['fields'=>['id','name','description','type'],
                        'Substance'=>['fields'=>['name','formula','molweight'],
                            'Identifier'=>['fields'=>['type','value'],'conditions'=>['type'=>['inchi','inchikey','iupacname']]]]]],
                'Dataseries'=>['fields'=>['type','format','level','processedType'],
                    'Descriptor'=>['fields'=>['title','number','text'],
                        'Property'=>['fields'=>['name']],
                        'Unit'=>['fields'=>['name','symbol']]],
                    'Annotation'=>['Metadata'=>['fields'=>['field','value','format']]],
                    'Datapoint'=>[
                        'Data'=>['fields'=>['datatype','text','number','title','id'],
                            'Property'=>['fields'=>['name']],
                            'Unit'=>['fields'=>['name','symbol']]],
                        'Condition'=>['fields'=>['datatype','text','number','title','id'],
                            'Property'=>['fields'=>['name']],
                            'Unit'=>['fields'=>['name','symbol']]]]]]];

        $data=$this->find('first',['conditions'=>['Report.id'=>$id],'contain'=>$contain,'recursive'=> -1]);
        return $data;
    }

    /**
     * Create qudt unit
     * @param $unit
     * @return string
     */
    public function qudt($unit) {
        if($unit=="MHz") {
            $unit="MegaHZ";
        } elseif($unit=="s") {
            $unit="SEC";
        } elseif($unit=="Hz") {
            $unit="HZ";
        } elseif($unit=="%") {
            $unit="PRECENT";
        } elseif($unit=="m/z") {
            $unit="Da-PER-E";
        } elseif($unit=="nm") {
            $unit="NanoM";
        }
        return "qudt:".$unit;
    }
	
	/**
	 * Find spectrum by splash
	 * @param $splash
	 * @param null $return
	 * @return mixed
	 */
    public function search($splash,$return=null)
	{
		$rep=$this->find('first',['conditions'=>['splash'=>$splash],'recursive'=>-1]);
		if(empty($rep)) {
			return false;
		} else {
			return $rep['Report']['id'];
		}
	}
}