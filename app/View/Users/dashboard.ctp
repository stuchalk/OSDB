<?php
$cs=$data['Collection'];
$u=$data['User'];
//pr($reps);exit; ?>
<h2>OSDB Dashboard</h2>
<h3>My Spectra</h3>
<ul>
    <?php
    $index=0;
    foreach($reps as $name=>$r) {
        echo $this->element('molspectra',['index'=>$index,'name'=>$name] + $r);
        $index++;
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
