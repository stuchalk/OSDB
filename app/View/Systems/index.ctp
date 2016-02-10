<?php
echo "<h3>"."Systems"."</h3>";
echo "<ul>";
foreach($data as $sys) {
    $title=$sys['System']['name'];
    $url='/systems/view/'.$sys['System']['id'];
    echo "<li>".$this->Html->link($title,$url).'</li>';
}
echo "</ul>";
?>



