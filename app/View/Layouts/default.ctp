<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $this->Html->charset(); ?>
	<title>OSDB</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('bootstrap-theme.min');
        echo $this->Html->css('sticky-footer-navbar');
        echo $this->Html->script('jquery');
		echo $this->Html->script('bootstrap.min');
        echo $this->Html->script('flot/jquery.flot');
		echo $this->Html->script('flot/jquery.flot.axislabels');
		echo $this->Html->script('flot/jquery.flot.labels');
		echo $this->Html->script('jsmol/JSmol.lite.nojq');
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
    <?php include('header.ctp'); ?>
    <div class="container theme-showcase" role="main">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->fetch('content'); ?>
    </div>
    <?php include('footer.ctp'); ?>
</body>
</html>