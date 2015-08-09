<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Context
 * TextFile model
 * test
 */
class Context extends AppModel
{

    public $belongsTo = ['Dataset'];

    public $hasOne=['System','Material','Computer'];

    public $hasMany=['Condition'];
}
