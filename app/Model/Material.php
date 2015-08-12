<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Material
 * Material model
 * A material is one made from a variety of substances (known or not) such that it is
 * difficult to describe it by stating those components.  Thus is data is
 * focused more on a description of the properties of the material, determination
 * of its elemental composition, and/or statement of the reactants and conditions used to create it
 */
class Material extends AppModel {
	
	public $useTable=['Material'];

    /**
     * General function to add a new material
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Material';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}