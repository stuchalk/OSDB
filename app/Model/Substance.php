<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Substance
 * Substance model
 * A substance is the asbtract representation of a chemical compound
 */
class Substance extends AppModel {

    public $hasMany=['Identifier'];

    public $hasAndBelongsToMany = ['System'];

    /**
     * General function to add a new substance
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Substance';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}