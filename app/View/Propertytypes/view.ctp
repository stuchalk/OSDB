<?php
$propertytype=$data['Propertytype'];
$ruleset=$data['Ruleset'];
$property=$data['Property'];
//pr($property);
?>

<h2>Property Type</h2>
<ul>
    <li> <?php echo "Property Name: ".$property['name']; ?> </li>
    <li> <?php echo "Code: ".$propertytype['code'];?> </li>
    <li> <?php echo "Ruleset: ".$ruleset['name']; ?> </li>
    <li> <?php echo "Phase(s): ".ucfirst($propertytype['phases']); ?> </li>
    <li> <?php echo "State(s): ".ucfirst($propertytype['states']); ?> </li>
</ul>
