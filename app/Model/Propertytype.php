<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Propertytype
 * Propertytype model Testing
 */
class Propertytype extends AppModel
{
    public $belongsTo = ['Property','Propertygroup','Ruleset'];

    public $hasMany = ['Parameter','Variable'];
}
