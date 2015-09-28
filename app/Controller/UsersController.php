<?php

/**
 * Class UsersController
 */
class UsersController extends AppController {

    public $uses=['User','Report'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('login','register','logout');
    }

    /**
     * beforeSave function
     * @param array $options
     * @return bool
     */
    public function beforeSave() {
        if (isset($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash(
                $this->data[$this->alias]['password']
            );
        }
        return true;
    }

    /**
     * User login
     */
    public function login()
    {
        if($this->request->is('post'))
        {
            if($this->Auth->login()) {
                $this->Session->setFlash('Welcome, '. $this->Auth->user('username'));
                $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Session->setFlash('Invalid username or password, try again.');
            }
        }
    }

    /**
     * User logout
     */
    public function logout()
    {
        $this->redirect($this->Auth->logout());
    }

    /**
     * Add new user
     */
    public function register()
    {
        if($this->request->is('post')) {
            $this->User->create();
            $data=$this->request->data;
            if($this->User->save($data)) {
                $this->Session->setFlash('User has been created');
                $this->redirect(['action'=>'login']);
            } else {
                $this->Session->setFlash('User could not be created.');
            }
        }
    }

    /**
     * View user information
     * @param null $id
     */
    public function view($id=null)
    {
        $this->User->id=$id;
        if(!$this->User->exists()) {
            throw new NotFoundException(_('Invalid user'));
        }
        $this->set('user',$this->User->read(null,$id));
    }

    /**
     * Delete users
     * @param null $id
     */
    public function delete($id=null)
    {
        $this->request->allowMethod('post');
        $this->User->id=$id;
        if(!$this->User->exists()) {
            throw new NotFoundException(_('Invalid user'));
        }
        if($this->User->delete()) {
            throw new NotFoundException(_('Invalid user'));
        }
        $this->Session->setFlash(_('User was not deleted'));
        $this->redirect(['action'=>'index']);
    }

    /**
     * Update user's information
     * @param null $id
     */
    public function update($id=null)
    {
        $this->User->id=$id;
        if(!$this->User->exist()) {
            throw new NotFoundException(_('Invalid user'));
        }
        if($this->request->is('post') || $this->request->is('put')) {
            if($this->User->save($this->request->data))
            {
                $this->Session->setFlash(_('User has been updated'));
                $this->redirect(['action'=>'index']);
            }
            $this->Session->setFlash(_('User could not be updated, please try again.'));
        } else {
            $this->request->data=$this->User->read(null,$id);
            unset($this->request->data['User']['password']);
        }
    }

    /**
     * User dashboard
     */
    public function dashboard()
    {
        $uid=$this->Auth->user('id');
        $data=$this->User->find('first',['conditions'=>['id'=>$uid]]);
        $this->set('data',$data);
        $reps=$this->Report->bySubstance('user',$uid);
        $this->set('reps',$reps);
    }
}
