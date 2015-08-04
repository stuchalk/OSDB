
<?php
echo $this->Form->create('User',['action'=>'update']);
echo $this->Form->hidden('id',
    ['value'=>$this->data['User']['id']]);
echo $this->Form->input('username',
    ['readonly'=>'readonly','label'=>'Username (cannot be changed)']);
echo $this->Form->input('password_update',
    ['label'=>'New Password (leave empty if password is not being changed)','type'=>'password','required'=>0]);
echo $this->Form->input('password_confirm_update',
    ['label'=>'Confirm New Password *','title'=>'Confirm New Password','type'=>'password','required'=>0]);
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
echo $this->Form->submit('Update User',
    ['class'=>'form-submit','title'=>'Update User']);
echo $this->Form->end(); ?>

<?php
echo $this->Html->link("Return to Home Page",
    ['action'=>'index']);
?>
<br/>
<?php
echo $this->Html->link( "Logout",
    ['action'=>'logout']);
?>



