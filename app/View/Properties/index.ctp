<h2>Chemical Properties</h2>
<ul>
<?php
foreach($data as $id=>$name) {
    echo "<li>".$this->Html->link($name,'/properties/view/'.$id)."</li>";
}
?>
</ul>