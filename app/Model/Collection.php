<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Collection
 * Collection model
 * Collections are aggregations of spectra from a specific resource
 */
class Collection extends AppModel
{
    public $belongsTo = ['User'];

    public $hasAndBelongsToMany = ['Report'];

    public $virtualFields=['first'=>'substr(name,1,1)'];

    /**
     * General function to add a new collection
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Collection';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}