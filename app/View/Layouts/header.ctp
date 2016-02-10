<script>
    $(document).ready(function() {
        function log( message ) {
            $( "<div>" ).text( message ).prependTo( "#log" );
            $( "#log" ).scrollTop( 0 );
        }

        $( "#FileSubstance" ).autocomplete({
            source: "<?php echo Configure::read('url'); ?>/substances/search",
            minLength: 2,
            select: function( event, ui ) {
                log( ui.item ?
                "Selected: " + ui.item.value :
                "Nothing selected, input was " + this.value );
                $( "#FileSubstanceId" ).val(ui.item.id); // Sends id to hidden field
            }
        });

        $('#SubstanceTerm').on("keypress", function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                e.preventDefault();
                e.stopPropagation();
                $(this).closest('form').submit();
            }
        });

    });
</script>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid" id="navfluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo Configure::read('url'); ?>">OSDB</a>
        </div>
        <div class="navbar-collapse collapse" id="navbar">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Browse <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><?php echo $this->Html->link('Spectra','/spectra'); ?></li>
                        <li><?php echo $this->Html->link('Compounds','/substances'); ?></li>
                        <li><?php echo $this->Html->link('Techniques','/techniques'); ?></li>
                        <li><?php echo $this->Html->link('Collections','/collections'); ?></li>
                    </ul>
                </li>
                <li><?php echo $this->Html->link('Add Spectra','/files/upload'); ?></li>
                <li><?php echo $this->Html->link('API','/pages/api'); ?></li>
                <li><?php echo $this->Html->link('About/Contact','/pages/about'); ?></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <?php
                        if($this->Session->read('Auth.User')) {
                            echo $this->Session->read('Auth.User.fullname');
                        } else {
                            echo "My OSDB";
                        }
                        ?>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <?php if($this->Session->read('Auth.User')) { ?>
                            <li><?php echo $this->Html->link('Dashboard','/users/dashboard'); ?></li>
                            <li><?php echo $this->Html->link('Logout','/users/logout'); ?></li>
                        <?php } else { ?>
                            <li><?php echo $this->Html->link('Login','/users/login'); ?></li>
                            <li><?php echo $this->Html->link('Register','/users/register'); ?></li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
            <?php
            echo $this->Form->create('Report',['url'=>'/spectra','class'=>'navbar-form navbar-right']);
            echo $this->Form->input('search',['type'=>'text','class'=>'form-control','div'=>false,'label'=>false,'placeholder'=>'Search compounds...']);
            echo $this->Form->end();
            ?>
            <?php echo $this->Flash->render(); ?>
        </div><!-- /.nav-collapse -->
    </div><!-- /.container-fluid -->
</nav>