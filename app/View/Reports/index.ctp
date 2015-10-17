<h2>Spectra</h2>
<ul>
    <?php
    foreach($data as $name=>$r) {
        echo $this->element('molspectra',['name'=>$name] + $r);
    }
    ?>
</ul>
<?php pr($data); ?>