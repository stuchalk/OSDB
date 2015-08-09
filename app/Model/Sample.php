<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Sample
 * System model
 */
class Sample extends AppModel
{
    public $hasOne = ['Dataset','System'];

    /**
     * General function to add a new sample
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Sample';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}