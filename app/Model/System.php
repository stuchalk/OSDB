<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class System
 * System model
 */
class System extends AppModel
{
    public $hasAndBelongsToMany = ['Substance'];

    public $hasMany = ['Dataset'];
}