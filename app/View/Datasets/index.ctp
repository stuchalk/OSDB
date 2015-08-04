<?php //pr($data); ?>
<?php
foreach($data as $publication) {
    echo "<h3>".$publication['title']."</h3>";
    echo "<ul>";
    foreach($publication['reports'] as $report){
        echo "<li>".$this->Html->link($report['rtitle'], '/datasets/view/' . $report['did']) . ' (';
        echo html_entity_decode($this->Html->link('Update', '/datasets/update/' . $report['did'])) . ')</li>';
    }
    echo "</ul><br/>";
    }
?>
