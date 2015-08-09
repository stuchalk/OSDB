<?php

App::uses('AppModel', 'Model');

/**
 * Class Measurement
 */
class Measurement extends AppModel {

    public $hasMany=['Setting'];

    public $belongsTo=['Methodology'];
}
?>