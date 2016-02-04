<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Source
 * Source model
 * Sources are where the spectral files come from
 */
class Source extends AppModel
{
    public $hasMany = ['File'];

    /**
     * General function to add a new source
     * @param array $data
     * @return array
     */
    public function add($data)
    {
        $model='Source';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}