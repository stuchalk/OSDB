<h2>Data Reports</h2>
<ul>
    <?php
    foreach($data as $id=>$name) {
        echo "<li>".$this->Html->link($name,'/reports/view/'.$id)."</li>";
    }
    ?>
</ul>