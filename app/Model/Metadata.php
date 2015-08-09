<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Metadata
 * System model
 */
class Metadata extends AppModel
{
    public $belongsTo = ['Annotation'];

    /**
     * General function to add a new metadata
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Metadata';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}