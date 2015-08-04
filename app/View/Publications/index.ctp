<h2>Publications</h2>
<?php //pr($data); ?>
<?php //pr($formula); ?>
<?php
echo "<ul'>";
foreach($data as $id=>$title)
{
    echo '<li>'.html_entity_decode($this->Html->link($title,'/publications/view/'.$id)).' (';
    echo html_entity_decode($this->Html->link('Update','/publications/update/'.$id)).')</li>';
}
echo "</ul>";
?>
<br>
<?php echo $this->Html->link("Add New Publication", ['action'=>'add']); ?>