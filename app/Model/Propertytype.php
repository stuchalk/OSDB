<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Propertytype
 * Propertytype model
 * A propertytype is a specific property measured under certain conditions
 * where some conditions are kept constant (parameters) and other vary (variables)
 */
class Propertytype extends AppModel
{
    public $belongsTo = ['Property','Propertygroup'];

    public $hasMany = ['Parameter','Variable','Dataset'];

    /**
     * General function to add a new propertytype
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Propertytype';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}