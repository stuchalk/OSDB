<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Reference
 */
class Reference extends AppModel
{

    /**
     * General function to add a new reference
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Reference';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}