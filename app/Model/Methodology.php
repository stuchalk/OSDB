<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Methodology
 * Methodology Model
 * Methodology is a description of how the experiment/calculation was setup
 * These are discipline/experiment specific
 */
class Methodology extends AppModel {

    public $hasOne=['Measurement'];

    public $hasMany=['Annotation'];

    /**
     * General function to add a new methodology
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Methodology';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}