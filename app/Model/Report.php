<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Report
 * Report model
 */
class Report extends AppModel
{
    public $hasOne = ['Dataset'=> ['dependent' => true]];

    public $belongsTo = ['Publication','User'];

    public $hasMany = ['Annotation'];

    /**
     * General function to add a new report
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Report';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}