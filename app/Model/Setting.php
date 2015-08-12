<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Setting
 * Setting model
 * Settings are values of parameters relative to instrument properties
 */
class Setting extends AppModel
{

    public $belongsTo = ['Measurement','Property','Unit'];

    /**
     * General function to add a new setting
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Setting';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}