<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Isdbset
 */
class Isdbset extends AppModel
{
    public $useDbConfig = 'isdb';
    public $useTable = 'datasets';

    public $belongsTo=[
        'Isdbsample'=>[
            'className' => 'Isdbsample',
            'foreignKey' => 'sample_id'
        ]
    ];
}