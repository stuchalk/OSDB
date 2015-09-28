<h2>Spectra</h2>
<ul>
    <?php
    foreach($data as $name=>$r) {
        echo "<li>".$name." ";
        foreach($r as $id=>$title) {
            echo $this->Html->link($title,'/spectra/view/'.$id)." ";
        }
        echo "</li>";
    }
    ?>
</ul>