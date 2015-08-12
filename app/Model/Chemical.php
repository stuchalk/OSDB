<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Chemical
 * Chemical model
 * Chemicals are physical amounts of a substance or mixture
 */
class Chemical extends AppModel
{
	
	public $belongsTo = ['Substance'];

    public $virtualFields=['first'=>"UPPER(SUBSTR(TRIM(LEADING '123456789' from Chemical.name),1,1))"];
    // TODO Strip other leading characters

    /**
     * General function to add a new chemical
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Chemical';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}