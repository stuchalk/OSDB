<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Context
 * TextFile model
 * test
 */
class Context extends AppModel
{

    public $belongsTo = ['Dataset'];

    public $hasAndBelongsToMany=['System'];
    //public $hasAndBelongsToMany=['System','Material','Computer'];

    public $hasMany=['Parameter','Annotation'];

    /**
     * General function to add a new context
     * @param $data
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
