<h2>Substances</h2>

<ul>
    <?php
    foreach($data as $id=>$name)
    {
        echo "<li>".$this->Html->link($name,'/substances/view/'.$id).' (';
        echo html_entity_decode($this->Html->link('Update','/substances/update/'.$id)).')</li>';
    }
    ?>
    <br>
    <?php echo $this->Html->link("Add New Substance", ['action'=>'add']); ?>

</ul>