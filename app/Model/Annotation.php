<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Annotation
 * Annotation model
 * Annotations can be added to any of the models below to augment the metadata
 */
class Annotation extends AppModel
{
    public $belongsTo = ['Dataset','Dataseries','Report','System','Methodology','Measurement','Context','Sample'];

    public $hasMany = ['Metadata'=>['foreignKey'=>'annotation_id','dependent'=>true]];

    /**
     * General function to add a new annotation
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Annotation';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}