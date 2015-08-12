<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Context
 * Context model
 * Contexts are the description of the conditions (from any perspective) under which a measurement is made
 * These are discipline/experiment specific
 */
class Context extends AppModel
{

    public $belongsTo = ['Dataset'];

    public $hasAndBelongsToMany=['System'];
    //public $hasAndBelongsToMany=['System','Material','Computer'];

    public $hasMany=['Annotation'];

    /**
     * General function to add a new context
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Context';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}