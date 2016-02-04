<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Sample
 * Sample model
 * A sample is a particular instance of a system, solution, or material
 */
class Sample extends AppModel
{
    public $hasOne = ['Dataset','System'];

    public $hasMany = ['Annotation'=>['foreignKey'=>'sample_id','dependent'=>true]];

    /**
     * General function to add a new sample
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Sample';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}