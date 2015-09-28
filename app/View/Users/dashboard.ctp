<?php
$cs=$data['Collection'];
$u=$data['User'];
//pr($reps);exit; ?>
<h2>OSDB Dashboard</h2>
<h3>My Spectra</h3>
<ul>
    <?php
    foreach($reps as $name=>$r) {
        echo "<li>".$name." ";
        foreach($r as $id=>$title) {
            echo $this->Html->link($title,'/spectra/view/'.$id)." ";
        }
        echo "</li>";
    }
    ?>
</ul>
<p>&nbsp;<br /><?php echo $this->Html->link("Add a New Spectrum",'/files/add'); ?></p>

<h3>My Collections</h3>
<ul>
    <?php
    foreach($cs as $c) {
        echo "<li>".$this->Html->link($c['name'],'/collections/view/'.$c['id'])."</li>";
    }
    ?>
</ul>
<p>&nbsp;<br /><?php echo $this->Html->link("Add a New Collection",'/collections/add'); ?></p>

<h3>Preferences</h3>
