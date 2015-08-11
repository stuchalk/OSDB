<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class ContextsSystem
 */
class ContextsSystem extends AppModel
{

    /**
     * General function to add a new context
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='ContextsSystem';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}
