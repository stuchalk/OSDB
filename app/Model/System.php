<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class System
 * System model
 * A system is the aggregate of a number of substances (the components) together
 * It is an abstract concept and samples are physical instances of a system
 */
class System extends AppModel
{
    public $hasAndBelongsToMany = ['Substance','Context'];

    public $hasMany=['Annotation'=>['foreignKey'=>'system_id','dependent'=>true]];

    /**
     * General function to add a new system
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='System';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}