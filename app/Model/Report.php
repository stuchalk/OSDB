<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Report
 * Report model
 */
class Report extends AppModel
{
    public $hasOne = ['Dataset'=>
        ['dependent' => true]
    ];

    public $belongsTo = ['Publication'];
}