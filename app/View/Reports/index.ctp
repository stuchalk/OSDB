<h2>Spectra</h2>
<ul>
    <?php
    foreach($data as $id=>$name) {
        echo "<li>".$this->Html->link($name,'/reports/view/'.$id)."</li>";
    }
    ?>
</ul>