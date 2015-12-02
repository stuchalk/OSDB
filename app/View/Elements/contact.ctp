<?php
// DIV to show contact information
// View Variables: None
// Element Variables: None
?>
<!-- element: contact.ctp -->
<div class="row">
    <div class="col-md-6 col-md-offset-3 text-justify">
        <div class="col-md-10 col-md-offset-1 well">
            <div class="col-md-6">
                <a href="#" class="thumbnail">
                    <?php echo $this->Html->image('stuchalk.jpg',['alt'=>'Stuart Chalk']); ?>
                </a>
            </div>
            <div class="col-md-6" style="padding-right: 10px;">
                    <p>Stuart Chalk<br />
                        Department of Chemistry<br />
                        University of North Florida<br />
                        Phone: 1-904-620-1938<br />
                        Fax: 1-904-620-3535<br />
                        Email: <?php echo $this->Html->link('schalk@unf.edu','mailto:schalk@unf.edu'); ?><br />
                        Website: <?php echo $this->Html->link('@unf.edu','http://www.unf.edu/coas/chemistry/faculty/Stuart_Chalk.aspx',['target'=>'_blank']); ?><br />
                        ORCID: <?php echo $this->Html->link('0000-0002-0703-7776','http://orcid.org/0000-0002-0703-7776',['target'=>'_blank']); ?></p>
                        <a href="http://www.linkedin.com/in/stuchalk">
                            <?php echo $this->Html->image('btn_viewmy_160x25.png',['width'=>'160','height'=>'25','border'=>'0','alt'=>'View Stuart Chalk\'s profile on LinkedIn']); ?>
                        </a>
            </div>
        </div>
    </div>
</div>