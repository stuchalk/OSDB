<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

/**
 * Class User
 * User model
 * Person using the software
 */
class User extends AppModel
{

    public $virtualFields=['fullname'=>'CONCAT(firstname," ",lastname)'];

    public $hasMany=['Report'];

    public $validate = [
        'username' => [
            'required' => [
                'rule' => ['notEmpty'],
                'message' => 'A username is required'
            ]
        ],
        'password' => [
            'required' => [
                'rule' => ['notEmpty'],
                'message' => 'A password is required'
            ]
        ]
    ];

    /**
     * beforeSave function
     * @param array $options
     * @return bool
     */
    public function beforeSave($options=[])
    {
        if(isset($this->data[$this->alias]['password'])) {
            $passwordHasher = new SimplePasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
        }
        return true;
    }
}
