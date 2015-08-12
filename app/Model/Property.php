<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Property
 * Property model
 * Properties are specific metrics that can be determined about matter
 */
class Property extends AppModel
{

    public $belongsTo = ['Quantity'];

    /**
     * General function to add a new property
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Parameter';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}