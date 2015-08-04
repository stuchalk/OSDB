<?php $set=$data['Publication']; ?>
<h2><?php echo $set['title']; ?></h2>

<?php echo $set['description']; ?><br><br>
<p> ISBN: <?php echo $set['isbn']; ?><br>
    eISBN: <?php echo $set['eisbn']; ?><br>
    Number of files: <?php echo $set['total_files']; ?>
</p>

<p><?php echo $this->Html->link('View publication info online',$set['url'],['target'=>'_blank']); ?><br>
    <?php echo $this->Html->link('Back to the publications list','/publications'); ?></p>