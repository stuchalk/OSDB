<script src='https://www.google.com/recaptcha/api.js'></script>
<script>
    $(document).ready(function() {
        $("[data-toggle=popover]").popover();
    });
</script>
<h2>Register for an Account on OSDB</h2>
<?php
echo $this->Form->create(false,['url'=>['controller'=>'users','action'=>'register'],'role'=>'form','class'=>'form-horizontal','inputDefaults'=>['label'=>false,'div'=>false]]);
echo $this->Form->input('type', ['type' =>'hidden','value'=>'regular']);
?>
<div class="form-group form-group-lg">
    <label for="UserUsername" class="col-sm-2 control-label">Username</label>
    <div class="col-sm-3">
        <?php
        $ba=['tab-index'=>'0','data-toggle'=>'popover','data-trigger'=>'focus','title'=>'Username help',
             'data-content'=>'Minimum of eight characters - case sensitive','data-placement'=>'right'];
        echo $this->Form->input('username', ['type'=>'text','size'=>'30','class'=>'form-control']+$ba); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="UserPassword" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-3">
        <?php
        $ba=['tab-index'=>'1','data-toggle'=>'popover','data-trigger'=>'focus','title'=>'Password help',
            'data-content'=>'Minimum of eight characters, both letters and numbers - case sensitive','data-placement'=>'right'];
        echo $this->Form->input('password', ['type'=>'password','size'=>'30','class'=>'form-control']+$ba); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="UserFirstname" class="col-sm-2 control-label">First Name</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('firstname', ['type'=>'text','size'=>'30','class'=>'form-control']); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="UserLastname" class="col-sm-2 control-label">Last Name</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('lastname', ['type'=>'text','size'=>'30','class'=>'form-control']); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="UserEmail" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('email', ['type'=>'text','size'=>'30','class'=>'form-control']); ?>
    </div>
</div>
<div class="form-group form-group-lg clearfix">
    <div class="col-sm-offset-2 col-sm-6">
        <div class="g-recaptcha pull-right" data-sitekey="6LcL5iMTAAAAAErS1vHJCWYyHu-ba5qF7Ubkd-7Q"></div>
    </div>
</div>
<div class="form-group form-group-lg">
    <div class="col-sm-offset-2 col-sm-6">
        <button type="submit" class="btn btn-default">Register</button>
    </div>
</div>