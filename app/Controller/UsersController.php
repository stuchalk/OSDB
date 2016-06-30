<?php
App::uses('AppController', 'Controller');

/**
 * Class UsersController
 */
class UsersController extends AppController {

    public $uses=['User','Report','File'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('login','register','logout');
    }

    /**
     * User login
     */
    public function login()
    {
        if($this->request->is('post'))
        {
            if($this->Auth->login()) {
                // Forcing the redirectUrl to the dashboard as if not set here trying
                // to add file redirects to collections/add after authentication
                $this->redirect($this->Auth->redirectUrl(['controller'=>'users','action'=>'dashboard']));
            } else {
                $this->Flash->error('Invalid credentials,<br />please try again.');
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
        // Check to make sure this is not a bot using recaptcha
        if(!empty($this->data['g-recaptcha-response'])) {
            if ($this->request->is('post') && $r = $this->Recaptcha->check($this->data['g-recaptcha-response'])) {
                $this->User->create();
                $data = $this->request->data;
                $data['recap_date'] = $r['challenge_ts'];
                $data['recap_ip'] = $r['hostname'];
                //debug($data);exit;
                if ($this->User->save($data)) {
                    $this->Flash->success('User created!<br />Please sign in...');
                    $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('User could not be created.');
                }
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
        $this->Flash->set(_('User was not deleted'));
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
                $this->Flash->set(_('User has been updated'));
                $this->redirect(['action'=>'index']);
            }
            $this->Flash->set(_('User could not be updated, please try again.'));
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

    /**
     * Admin function access
     */
    public function admin()
    {
        $type=$this->Auth->user('type');
        if($type!='admin') {
            $this->Flash->error('Access Denied');
            $this->redirect(['controller'=>'users','action'=>'dashboard']);
        }
        // Get files
        $c=['Dataset'=>['fields'=>['id'],
                'Report'=>['fields'=>['id','title']],
                'Dataseries'=>['fields'=>['id'],
                    'Datapoint'=>['fields'=>['id'],
                        'Condition'=>['fields'=>['id']],
                        'Data'=>['fields'=>['id']]]]]];
        $files=$this->File->find('all',['contain'=>$c,'recursive'=>-1]);
        $this->set('files',$files);
    }
}
