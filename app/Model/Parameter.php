<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Parameter
 * Parameter model
 * Parameters are a representation of those things that are controlled in an experiment
 * They are used here to describe propertytypes - the general representation of a type
 * of experimental approach (see propertytype and propertygroup)
 */
class Parameter extends AppModel
{

    public $belongsTo = ['Propertytype','Property'];

    public $hasAndBelongsToMany = ['Unit'];

    /**
     * General function to add a new parameter
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Parameter';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}