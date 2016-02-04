<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class ContextsSystem
 * Join table between contexts and systems
 */
class CollectionsReport extends AppModel
{

    /**
     * General function to add a new context
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='CollectionsReport';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}