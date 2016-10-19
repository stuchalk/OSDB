<div class="well text-justify col-sm-11" style="padding: 0 19px;">
    <h3>The Open Spectral Database <span class="label label-danger">Beta</span></h3>
    is exactly what the title suggests - a place where scientists can share spectral data
    and export it in a number of formats in order to do open science.  Each spectrum gets a persistent identifier and
    contributors can get recognition for their contributions.  The site is also an open source project that you can extend, enhance and share.<br />
    <i class="pull-right">Open science, open data, open code, open concept - it's all about the data!</i>
    <br />&nbsp;
</div>
<div class="col-sm-1 text-center" style="padding: 0 5px;">
    Altmetric Score
    <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
    <div class="altmetric-embed" data-badge-type="donut" data-altmetric-id="12615818"></div>
    <a href="http://dx.doi.org/10.1186/s13321-016-0170-2" target="_blank" class="btn btn-info btn-sm" style="margin-top: 5px;">J. Cheminf.<br />Paper <span class="glyphicon glyphicon-link"></span></a>
</div>

<div>
    <div class="row">
        <div class="col-sm-3">
            <?php echo $this->element('recent'); ?>
        </div>
        <?php echo $this->element('latest'); ?>
    </div>
</div>

<h3>Get Involved!</h3>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Submit a Spectrum</h3>
            </div>
            <div class="panel-body text-justify">
                Upload a UV/Vis, IR, MS, 1H NMR or 13C NMR spectrum in JCAMP-DX format for everyone to view and download.
                Users can download the JCAMP-DX file as well as the data encoded in JCAMP XML, JSON, and
                <?php echo $this->Html->link('SciData','http://stuchalk.github.io/scidata/',['target'=>'_blank']); ?> JSON-LD.
                <br /><?php echo $this->Html->link('Upload','/files/upload',['class'=>'btn btn-med btn-default pull-right']); ?>

            </div>
        </div>
    </div><!-- /.col-sm-4 -->
    <div class="col-sm-4">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">Become a Contributor</h3>
            </div>
            <div class="panel-body text-justify">
                If you want to contribute lots of spectra, register for an account (with your ORCID) to create and manage
                collections of spectra, bulk upload and earn badges (coming soon) for your contributions.
                <br /><br /><?php echo $this->Html->link($this->Html->image('orcid-logo.png', ['alt' => 'ORCiD','width'=>'105']),'http://orcid.org',['target'=>'_blank','escape'=>false]); ?>
                <?php echo $this->Html->link('Login','/users/login',['class'=>'btn btn-med btn-primary pull-right']); ?>
                &nbsp;<?php echo $this->Html->link('Register','/users/register',['class'=>'btn btn-med btn-success pull-right','style'=>'margin-right: 10px;']); ?>
            </div>
        </div>
    </div><!-- /.col-sm-4 -->
    <div class="col-sm-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">Contribute Code</h3>
            </div>
            <div class="panel-body text-justify">
                Got a great idea for a new feature? Like to code?  The OSDB is an open source project hosted on GitHub
                and built using current web technologies like
                <?php echo $this->Html->link('CakePHP','http://cakephp.org',['target'=>'_blank']); ?>,
                <?php echo $this->Html->link('jQuery','http://jquery.com',['target'=>'_blank']); ?>,
                <?php echo $this->Html->link('flot','http://www.flotcharts.org/',['target'=>'_blank']); ?> and
                <?php echo $this->Html->link('Bootstrap','http://getbootstrap.com',['target'=>'_blank']); ?>.  Join the repo and
                extend the feature set of this site.
                <br /><?php echo $this->Html->link($this->Html->image('GitHub_Logo.png', ['alt' => 'GitHub','width'=>'80']),'http://github.com/stuchalk/OSDB',['target'=>'_blank','escape'=>false,'class'=>'pull-right']); ?>
            </div>
        </div>
    </div><!-- /.col-sm-4 -->
</div>