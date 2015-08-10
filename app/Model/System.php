<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class System
 * System model
 */
class System extends AppModel
{
    public $hasAndBelongsToMany = ['Substance','Context'];

    public $hasMany=['Annotation'];

    /**
     * General function to add a new system
     * @param $data
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