<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Condition
 * Condition model
 */
class Condition extends AppModel
{
    public $belongsTo = ['Data','Dataseries','Unit','Property','Datapoint'];
}