<?php
//pr($data);exit;
$system=$data['System'];
$substance=$data['Substance'];
$contexts=$data['Context'];
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
    foreach($contexts as $context)
    {
        $id=$context['Dataset']['id'];
        $name=$context['Dataset']['Propertytype']['name'];
        echo "<li>".$this->Html->link($name,'/reports/view/'.$id) ."</li>";
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
