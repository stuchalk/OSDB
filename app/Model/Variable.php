<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Variable
 * Variable model
 * Variables are a representation of those things that are uncontrolled in an experiment
 * They are used here to describe propertytypes - the general representation of a type
 * of experimental approach (see propertytype and propertygroup)
 */
class Variable extends AppModel
{
    public $belongsTo = ['Propertytype', 'Property'];

    public $hasAndBelongsToMany = ['Unit'];

    /**
     * General function to add a new variable
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Variable';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}