<?php
echo $this->Session->flash('auth');
echo $this->Form->create('User');
?>
    <fieldset>
        <legend>Enter username and password</legend>
        <?php
        echo $this->Form->input('username');
        echo $this->Form->input('password');
        ?>
    </fieldset>
<?php echo $this->Form->end('Login'); ?>