<h2>Techniques</h2>
<ul>
    <?php
    foreach($data as $id=>$name) {
        echo "<li>".$this->Html->link($name,'/techniques/view/'.$id)."</li>";
    }
    ?>
</ul>