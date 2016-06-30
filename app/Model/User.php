<?php
App::uses('AppModel', 'Model');
App::uses('Security', 'Utility');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Class User
 * User model
 * Person using the software
 */
class User extends AppModel
{

    public $virtualFields=['fullname'=>'CONCAT(firstname," ",lastname)'];

    public $hasMany=['Report','Collection'];

    public $validate = [
        'username' => [
            'required' => [
                'rule' => ['notBlank'],
                'message' => 'A username is required'
            ]
        ],
        'password' => [
            'required' => [
                'rule' => ['notBlank'],
                'message' => 'A password is required'
            ]
        ]
    ];

    public function beforeSave($options = [])
    {
        if (isset($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash(
                $this->data[$this->alias]['password']
            );

        }
        return true;
    }

}
