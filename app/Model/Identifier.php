<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Identifier
 * Identifier model
 * Identifiers are metadata that identify substances (specifically)
 */
class Identifier extends AppModel {

    public $belongsTo=['Substance'];

    /**
     * General function to add a new identifier
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Identifier';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}