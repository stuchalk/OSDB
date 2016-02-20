<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Activity
 * Activity model
 */
class Activity extends AppModel
{
    public $belongsTo = ['User'];

    /**
     * General function to add a new activity
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Activity';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}