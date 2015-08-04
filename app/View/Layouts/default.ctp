<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<?php echo $this->Html->charset(); ?>
	<title>Springer Materials: <?php echo $title_for_layout; ?></title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->script('jquery');
		echo $this->Html->script('jquery-ui');
		echo $this->Html->script('jqcake');
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container">
		<div id="header">
            <div style="float: left;width: 80%;">
                <h1>The Open Spectral Database</h1>
            </div>
            <div style="float: right;">
                <?php
                    if($this->Session->read('Auth')) {
                        echo $this->Html->link('Logout','/users/logout');
                    } else {
                        echo $this->Html->link('Login','/users/login')."&nbsp;";
                        echo $this->Html->link('Register','/users/register');
                }
                ?>
            </div>
        </div>
		<div id="content">
			<?php echo $this->Session->flash(); ?>
            <div style="float: left;width: 80%;" ?>
                <?php echo $this->fetch('content'); ?>
            </div>
            <?php echo $this->element('navigation'); ?>

        </div>
		<div id="footer">
			<?php echo "Chalk Group @ ".$this->Html->link("University of North Florida",'http://www.unf.edu/',array('target' =>'_blank'))." © 2015"; ?>
		</div>
	</div>
</body>
</html>