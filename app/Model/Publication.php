<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Publication
 * Publication model
 */
class Publication extends AppModel
{

    public $hasMany = ['Report'];
    public $hasAndBelongsToMany = ['Propertygroup'];

}

?>