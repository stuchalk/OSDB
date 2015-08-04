<h2>Property Types</h2>

<ul>
    <?php
    foreach($data as $id=>$code)
    {
        echo "<li>".$this->Html->link($code,'/propertytypes/view/'.$id).' (';
        echo html_entity_decode($this->Html->link('Update','/propertytypes/update/'.$id)).')</li>';
    }
    ?>
</ul><br>
<?php echo $this->Html->link("Add New Property Type", ['action'=>'add']); ?>
