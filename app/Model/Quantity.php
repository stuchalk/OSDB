<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Quantity
 * Quantity model
 */
class Quantity extends AppModel
{

    public $hasAndBelongsToMany = ['Unit'];

}