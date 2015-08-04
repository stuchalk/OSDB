<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Property
 * Property model
 */
class Property extends AppModel
{

    public $belongsTo = ['Quantity'];

    //public $hasAndBelongsToMany = ['Publication'];
}