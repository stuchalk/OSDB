<?php

App::uses('AppModel', 'Model');

/**
 * Class Methodology
 */
class Methodology extends AppModel {

    public $hasOne=['Measurement'];

}
?>