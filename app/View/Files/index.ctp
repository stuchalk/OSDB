<?php //pr($data);pr($pubs); ?>
<h2>Files</h2>
<?php
foreach($data as $pid=>$file) {
    echo "<h3>".$pubs[$pid]."</h3>";
    echo "<ul>";
    foreach ($file as $id=>$filename) {
        echo "<li>" . $this->Html->link($filename, '/files/view/' . $id) . ' (';
        echo html_entity_decode($this->Html->link('Update', '/files/update/' . $id)) . ')</li>';
    }
    echo "</ul><br/>";
}
?><br>
<?php echo $this->Html->link("Add New File",['action'=>'add']); ?>
