<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<?php echo $this->Html->charset(); ?>
	<title>OSDB</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->css('jquery-ui.structure');
		echo $this->Html->css('jquery-ui.theme');
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
            <div class="left">
                <h1>The Open Spectral Database</h1>
            </div>
            <div class="right">
                <?php
				$ip=$this->request->host();
				if($ip=="sds.coas.unf.edu") {
					if($this->Session->read('Auth')) {
						echo $this->Html->link('Logout','/users/logout');
					} else {
						echo $this->Html->link('Login','/users/login')."&nbsp;";
						echo $this->Html->link('Register','/users/register');
					}
				}
                ?>
            </div>
        </div>
		<div id="content">
			<?php echo $this->Session->flash(); ?>
            <div class="left" ?>
                <?php echo $this->fetch('content'); ?>
            </div>
            <?php //echo $this->element('navigation'); ?>

        </div>
		<div id="footer">
			<?php echo "Chalk Group @ ".$this->Html->link("University of North Florida",'http://www.unf.edu/',['target' =>'_blank'])." © 2015"; ?>
		</div>
	</div>
</body>
</html>