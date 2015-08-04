<?php //pr($data); ?>
<?php
echo "<h3>"."Systems"."</h3>";
echo "<ul>";
foreach($data as $sys) {
    echo "<li>".$this->Html->link($sys['System']['name'], '/systems/view/' . $sys['System']['id']) . ' (';
        echo html_entity_decode($this->Html->link('Update', '/systems/update/' . $sys['System']['id'])) . ')</li>';
}
echo "</ul><br/>";
?>
