<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataset
 * Parameter model
 */
class Dataset extends AppModel
{
    public $hasOne=['Methodology','Context','Sample'];

    public $hasMany = [
        'Dataseries'=> [
            'foreignKey' => 'dataset_id',
            'dependent' => true,
        ]
        ,'Data'=> [
            'foreignKey' => 'dataset_id',
            'dependent' => true,
        ],
        'Annotation'];

    public $belongsTo = ['Propertytype','Reference','File','Report'];

    /**
     * General function to add a new system
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Dataset';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}