<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Publication
 * Publication model
 * A published work that contains the report of a dataset
 */
class Publication extends AppModel
{

    public $hasMany = ['Report'];

    public $hasAndBelongsToMany = ['Propertygroup'];

    /**
     * General function to add a new publication
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Publication';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}