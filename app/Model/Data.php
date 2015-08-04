<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Data
 * Data model
 */
class Data extends AppModel
{
    public $hasMany = ['Condition'=> [
        'foreignKey' => 'data_id',
        'dependent' => true,
],];

public $belongsTo = ['Dataset','Dataseries','Unit','Datapoint','Property'];
}