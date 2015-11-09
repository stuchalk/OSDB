<h2>Spectra</h2>
<ul>
    <?php
    $index=0;
    foreach($data as $name=>$r) {
        echo $this->element('molspectra',['index'=>$index,'name'=>$name] + $r);
        $index++;
    }
    ?>
</ul>
<?php //pr($data); ?>