<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataseries
 * Dataseries model
 */
class Dataseries extends AppModel
{
    public $hasMany = [
        'Condition'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ],
        'Data'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ],
        'Datapoint'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ]];

    public $belongsTo = ['Dataset'];

    /**
     * General function to add a new dataseries
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Dataseries';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}