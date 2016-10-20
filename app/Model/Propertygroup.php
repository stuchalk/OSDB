<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Propertygroup
 * Propertygroup model
 * Definition of a a generic class of property information that is logically related
 * e.g. Heats of mixing and solution.  There are a variety of property types for a group
 * that measure slightly different properties in different ways
 */
class Propertygroup extends AppModel
{
    public $hasMany = ['Propertytype'];

    /**
     * General function to add a new propertygroup
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Propertygroup';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}