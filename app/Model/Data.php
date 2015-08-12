<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Data
 * Data model
 * Data are the values obtained by doing an experiment under a certain set of conditions
 */
class Data extends AppModel
{
    public $belongsTo = ['Dataset','Datapoint','Property','Unit'];

    /**
     * General function to add a new data
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Data';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}