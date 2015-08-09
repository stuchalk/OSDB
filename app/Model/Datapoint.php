<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataseries
 * Dataseries model
 */
class Datapoint extends AppModel
{
    public $hasMany = [
        'Condition'=> [
            'foreignKey' => 'datapoint_id',
            'dependent' => true,
        ],
        'Data'=> [
            'foreignKey' => 'datapoint_id',
            'dependent' => true,
        ]];

    public $belongsTo = ['Dataseries','Dataset'];

    /**
     * General function to add a new datapoint
     * @param $data
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