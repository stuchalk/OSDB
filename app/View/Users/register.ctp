<?php
echo $this->Form->create('User', ['action' => 'register']);
echo $this->Form->input('username',
    ['type'=>'text']);
echo $this->Form->input('password',
    ['type'=>'password']);
echo $this->Form->input('firstname',
    ['type'=>'text','label'=>'First Name']);
echo $this->Form->input('lastname',
    ['type'=>'text','label'=>'Last Name']);
echo $this->Form->input('email',
    ['type'=>'text','label'=>'Email Address']);
echo $this->Form->input('phone',
    ['type'=>'text','label'=>'Phone Number']);
echo $this->Form->input('type',
    ['options'=>[''=>'Choose User Type','regular'=>'Regular','admin'=>'Admin'],'label'=>'User Type']);
echo $this->Form->end('Register');
?>

