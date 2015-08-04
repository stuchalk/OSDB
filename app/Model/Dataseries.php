<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataseries
 * Dataseries model
 */
class Dataseries extends AppModel
{
    public $hasMany = [
        'Condition'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ],
        'Data'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ],
        'Datapoint'=> [
            'foreignKey' => 'dataseries_id',
            'dependent' => true,
        ]];

    public $belongsTo = ['Dataset'];
}