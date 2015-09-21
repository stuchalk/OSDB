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
    public $hasOne = ['Dataset'=> ['foreignKey'=>'report_id','dependent' => true],
                        'File'=> ['foreignKey'=>'report_id','dependent' => true]];

    public $belongsTo = ['Publication','User'];

    public $hasMany = ['Annotation'=> ['foreignKey'=>'report_id','dependent' => true]];

    /**
     * General function to add a new report
     * @param array $data
     * @return integer
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
     * Returns DB data so that it can be used to generate scidata json
     * @param $id
     * @return mixed
     */
    public function scidata($id)
    {
        // Note: there is an issue with the retrival of susbtances under system if id is not requested as a field
        // This is a bug in CakePHP as it works without id if its at the top level...
        $contain=['Publication'=>['fields'=>['title']],
            'User'=>['fields'=>['fullname']],
            'Dataset'=>['fields'=>['setType','property','kind'],
                'File'=>['fields'=>['id']],
                'Sample'=>['fields'=>['title','description'],
                    'Annotation'=>['Metadata'=>['fields'=>['field','value','format']]]],
                'Methodology'=>['fields'=>['evaluation','aspects'],
                    'Measurement'=>['fields'=>['techniqueType','technique','instrumentType','instrument','vendor'],
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
}