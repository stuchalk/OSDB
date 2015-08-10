<?php
pr($data);exit;
$system=$data['System'];
$substance=$data['Substance'];
$dataset=$data['Context']['Dataset'];
?>

<h2>System</h2>
<ul>
    <li><?php echo "Name: ".$system['name']; ?></li>
    <li><?php echo "Description: ".$system['description']; ?></li>
    <li><?php echo "Type: ".$system['type']; ?></li>
</ul>
<p>&nbsp;</p>
<h3>Data Set</h3>
<ul>
    <?php
    foreach($dataset as $set){
        echo "<li>".$this->Html->link($set['Propertytype']['method'],'/datasets/view/'.$set['id']) ."</li>";
    }
?>
</ul>
<p>&nbsp;</p>
<h3>Substances</h3>
<ul>
<?php
    foreach ($substance as $sub) {
        echo "<li>".$this->Html->link($sub['name'],'/substances/view/'.$sub['id'])."</li>";
    }
?>
</ul>
