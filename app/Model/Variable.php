<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Variable
 * Variable model
 */
class Variable extends AppModel
{
    public $belongsTo = ['Propertytype', 'Property'];

    public $hasAndBelongsToMany = ['Unit'];
}