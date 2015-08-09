<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataset
 * Parameter model
 */
class Dataset extends AppModel
{
    public $hasOne=['Methodology'];

    public $hasMany = [
        'Dataseries'=> [
            'foreignKey' => 'dataset_id',
            'dependent' => true,
        ]
        ,'Data'=> [
            'foreignKey' => 'dataset_id',
            'dependent' => true,
        ]];

    public $belongsTo = ['Propertytype','System','Reference','File','Report'];
}