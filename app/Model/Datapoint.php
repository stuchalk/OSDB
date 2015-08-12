<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Datapoint
 * Datapoint model
 * Datapoints are an abstract representation of the information collected in an experiment
 * From a data model perspective you attach data and conditions to the datapoint to represent it
 * They can be part of a dataset or a dataseries
 */
class Datapoint extends AppModel
{
    public $hasMany = ['Condition'=>['foreignKey'=>'datapoint_id','dependent'=>true],
                        'Data'=>['foreignKey'=>'datapoint_id','dependent'=>true]];

    public $belongsTo = ['Dataseries','Dataset'];

    /**
     * General function to add a new datapoint
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Datapoint';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}