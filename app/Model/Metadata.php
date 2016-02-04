<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Metadata
 * Metadata model
 * Metadata (in this context) are name value pairs used in the annotation
 * of different parts of the data model (see Annotation model)
 */
class Metadata extends AppModel
{
    public $belongsTo = ['Annotation'];

    /**
     * General function to add a new metadata
     * @param array $data
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