<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataseries
 * Dataseries model
 */
class Datapoint extends AppModel
{
    public $hasMany = [
        'Condition'=> [
            'foreignKey' => 'datapoint_id',
            'dependent' => true,
        ],
        'Data'=> [
            'foreignKey' => 'datapoint_id',
            'dependent' => true,
        ]];

    public $belongsTo = ['Dataseries','Dataset'];
}