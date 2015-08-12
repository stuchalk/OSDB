<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Quantity
 * Quantity model
 * A physical quantity is a physical property of a phenomenon, body, or substance, that can be quantified by measurement.
 * See https://en.wikipedia.org/wiki/Physical_quantity
 * See https://en.wikipedia.org/wiki/List_of_physical_quantities
 */
class Quantity extends AppModel
{

    public $hasMany = ['Unit'];

    /**
     * General function to add a new quantity
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Quantity';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}