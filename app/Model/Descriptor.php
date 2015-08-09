<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Descriptor
 * Condition model
 */
class Descriptor extends AppModel
{
    public $belongsTo = ['Dataseries'];

    /**
     * General function to add a new descriptor
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Descriptor';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}