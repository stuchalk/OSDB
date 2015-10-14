<?php
$c=$data['Collection'];
$u=$data['User'];
//pr($cols);//exit; ?>
<h2>Collection</h2>
<p><?php echo $c['description']; ?></p>
<p>Provided by: <?php echo $u['fullname']; ?></p>
<p>Orginal source: <?php echo $c['source']; ?></p>
<p>Website: <?php echo $c['url']; ?></p>
<h3>Spectra</h3>
<ul>
    <?php
    foreach($cols as $name=>$r) {
        echo "<li>".$name." ";
        foreach($r as $id=>$title) {
            echo $this->Html->link($title,'/spectra/view/'.$id)." ";
        }
        echo "</li>";
    }
?>
</ul>