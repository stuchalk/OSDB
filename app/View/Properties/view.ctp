<?php $property=$data['Property'];//pr($property); ?>

<h2>Chemical Property</h2>
<h3><?php echo $property['name']; ?></h3>
<p><?php echo $property['definition']; ?></p>
<p>
    <?php
    if(stristr($property['source'],'http')) {
        echo $this->Html->link('Source',$property['source'],['target'=>'_blank']);
    } else {
        echo $property['source'];
    }
    ?>
</p>
<?php pr($data); ?>