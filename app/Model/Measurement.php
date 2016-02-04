<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Measurement
 * Measurement model
 * Measurement contains information about the equipment/instrumentation
 * used in the experiment, include the settings for such
 */
class Measurement extends AppModel {

    public $hasMany=['Setting'=>['foreignKey'=>'measurement_id','dependent'=>true],
                     'Annotation'=>['foreignKey'=>'measurement_id','dependent'=>true]];

    public $belongsTo=['Methodology'];

    /**
     * General function to add a new measurement
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Measurement';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}