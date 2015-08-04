<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Parameter
 * Parameter model
 */
class Parameter extends AppModel
{
    public $belongsTo = ['Propertytype','Property'];

    public $hasAndBelongsToMany = ['Unit'];
}

?>