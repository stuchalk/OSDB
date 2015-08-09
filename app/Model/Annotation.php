<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Sample
 * System model
 */
class Annotation extends AppModel
{
    public $belongsTo = ['Dataset','Dataseries','Report','System','Methodology','Context'];

    /**
     * General function to add a new annotation
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Annotation';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}