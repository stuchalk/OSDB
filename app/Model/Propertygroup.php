<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Propertygroup
 * Propertygroup model Testing
 */
class Propertygroup extends AppModel
{
    public $hasMany = ['Propertytype'];

    public $hasAndBelongsToMany = ['Publication'];

}
