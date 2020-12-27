<?php $stats=$this->requestAction('/files/stats'); ?>
<ul>
    <?php
    foreach($stats as $tech=>$count) {
        if($count>0) {
            $link=$this->Html->link($count." spectra",'/techniques/view/'.str_replace(' ','', $tech));
            echo "<li>".$tech.": ".$link."</li>";
        }
    }
    ?>
</ul>
