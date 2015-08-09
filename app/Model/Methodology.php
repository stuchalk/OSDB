<?php

App::uses('AppModel', 'Model');

/**
 * Class Methodology
 */
class Methodology extends AppModel {

    public $hasOne=['Measurement'];

    /**
     * General function to add a new methodology
     * @param $data
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