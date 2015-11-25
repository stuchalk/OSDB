<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/osdb">OSDB</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><?php echo $this->Html->link('Home','/'); ?></li>
                <li><?php echo $this->Html->link('About','/pages/about'); ?></li>
                <li><?php echo $this->Html->link('Contact','/pages/contact'); ?></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My OSDB <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><?php echo $this->Html->link('Login','/users/login'); ?></li>
                        <li><?php echo $this->Html->link('Register','/users/register'); ?></li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-right">
                <input type="text" class="form-control" placeholder="Search...">
            </form>
        </div><!--/.nav-collapse -->
    </div>
</nav>