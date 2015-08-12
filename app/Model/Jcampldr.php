<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Jcampldr
 * Jcampldr model
 * Accesses the JCAMP LDRs efined in the various published IUPAC standards
 */
class Jcampldr extends AppModel {

    public $belongsTo=['Property'];

}