<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OSDB <?php echo $title_for_layout; ?></title>
    <?php
    echo $this->Html->meta('icon');
    echo $this->Html->css('jquery-ui');
    echo $this->Html->css('bootstrap.min');
    echo $this->Html->css('bootstrap-theme.min');
    echo $this->Html->css('sticky-footer-navbar');
    echo $this->Html->css('shadows');
    echo $this->Html->css('signin');
    echo $this->Html->script('jquery');
    echo $this->Html->script('jquery-ui');
    echo $this->Html->script('jqcake');
    echo $this->Html->script('bootstrap.min');
    echo $this->Html->script('flot/jquery.flot');
    echo $this->Html->script('flot/jquery.flot.axislabels');
    echo $this->Html->script('flot/jquery.flot.labels');
    echo $this->Html->script('flot/jquery.flot.resize');
    echo $this->Html->script('JSmol.min.nojq');
    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');
    ?>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-1412148-18', 'auto');
        ga('send', 'pageview');

    </script>
</head>
<body>
    <?php include('header.ctp'); ?>
    <div class="container theme-showcase" role="main">
        <?php echo $this->fetch('content'); ?>
    </div>
    <?php include('footer.ctp'); ?>
</body>
</html>