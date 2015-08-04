<h2><?php echo 'Data From: '.'"'.$Data['Dataseries']['Dataset']['File']['Publication']['title'].'"'; ?></h2>
<ul>
    <li><?php echo "Publication: ".$this->Html->link($Data['Dataseries']['Dataset']['File']['Publication']['title'],"/publications/view/".$Data['Dataseries']['Dataset']['File']['Publication']['id']); ?></li>
    <li><?php echo "Property Type: ".$Data['Dataseries']['Dataset']['Propertytype']['code']; ?>  </li>
    <li><?php echo "States: ".$Data['Dataseries']['Dataset']['Propertytype']['states']."<br>".'<span style="width:25px;">Phases:</span>'.$Data['Dataseries']['Dataset']['Propertytype']['phases']."<br>"; ?></li>
    <li><?php echo "Number of Components: ".$Data['Dataseries']['Dataset']['Propertytype']['num_components']; ?></li>
    <li>Components:
        <?php
        foreach($Data['Dataseries']['Dataset']['System']['Substance'] as $i=>$substance){
            echo "<br>";
            echo $this->Html->link($substance['formula']." ".$substance['name'],"/substances/view/".$substance['id']);
            foreach($substance['Identifier'] as $ident){
                if($ident['type']=="casrn") {
                    echo " (" . $ident['value'] . ") ";
                }
            }
        }
        ?></li>
    <li><?php echo "Method: ".$Data['Dataseries']['Dataset']['Propertytype']['method'];?></li>
</ul>
<ul>
    <li><?php echo "File Number: ".$this->Html->link((isset($dump['Report']['file_code'])?$dump['Report']['file_code']:"N/A"),"/files/view/".$Data['Dataseries']['Dataset']['File']['id']);?></li>
    <li><?php echo "Data Series: ".$this->Html->link($Data['Dataseries']['id'],"/dataseries/view/".$Data['Dataseries']['id']); ?></li>
    <li><?php echo "Data Set: ".$this->Html->link($Data['Dataseries']['Dataset']['id'],"/datasets/view/".$Data['Dataseries']['Dataset']['id']); ?></li>
</ul><br>
<h3>Data</h3>
<table  align="center" border="0" style="width:50%">
    <thead>
    <?php
    $dataSize=0;
    $columns=count($Data['Condition'])+1;
    for($i=0;$i<count($Data['Dataseries']['Condition']);$i++) {
        echo "<tr>";
        echo "<td colspan='$columns'>";
        echo $Data['Dataseries']['Condition'][$i]['Property']['name'] . " = ";
        if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="decimal") {
            echo  ((float)$Data['Dataseries']['Condition'][$i]['number']);
            if((float)$Data['Dataseries']['Condition'][$i]['error']!==0.0) {
                echo "±" . ((float)$Data['Dataseries']['Condition'][$i]['error']);
            }
        } else{
            echo  $Data['Dataseries']['Condition'][$i]['number'];
            if((float)$Data['Dataseries']['Condition'][$i]['error']!==0.0) {
                echo "±" . $Data['Dataseries']['Condition'][$i]['error'];
            }
        }
        echo " " . $Data['Dataseries']['Condition'][$i]['Unit']['symbol'];
        echo "</td>";
        if (count($Data['Dataseries']['Data']) > $dataSize) {
            $dataSize=count($Data['Dataseries']['Data']);
        }
        echo "</tr>";
    }
    echo "<tr>";
    foreach($Data['Condition'] as $cond){
        $headers[]=$cond['Unit']['symbol'];
    }
    $headers[]=$Data['Unit']['symbol'];
    foreach($headers as $header) {
        echo "<th>" . $header . "</th>";
    }
    echo "</tr></thead>";
    echo "<tr>";
    foreach($Data['Condition'] as $condition) {
        if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") {
            echo "<td>" . $condition['number'];
            if((float)$condition['error']!==0.0){
                echo " ± " . $condition['error'] . "</td>";
            }
        } else {
            echo "<td>" . ((float)$condition['number']);
            if((float)$condition['error']!==0.0){
                echo " ± " . ((float)$condition['error']). "</td>";
            }
        }
    }
    if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") {
        echo "<td>" . $Data['Data']['number'];
        if((float)$Data['Data']['error']!==0.0) {
            " ± " . $Data['Data']['error'] . "</td>";
        }
    }else {
        echo "<td>" . ((float)$Data['Data']['number']);
        if ((float)$Data['Data']['error'] !== 0.0) {
            echo " ± " . ((float)$Data['Data']['error']) . "</td>";
        }
    }

    echo "</tr>";
?>
</table>