<?php
$ref=$Dataseries['Dataset']['Reference'];
?>
    <h2><?php echo 'Data Series From: '.'"'.$Dataseries['Dataset']['Report']['title'].'"'; ?></h2>
<ul>
    <li><?php echo "Property Type: ".$Dataseries['Dataset']['Propertytype']['code']; ?>  </li>
    <li><?php echo "States: ".$Dataseries['Dataset']['Propertytype']['states']."<br>".'<span style="width:25px;">Phases:</span>'.$Dataseries['Dataset']['Propertytype']['phases']."<br>"; ?></li>
    <li><?php echo "Number of Components: ".$Dataseries['Dataset']['Propertytype']['num_components']; ?></li>
    <li>
        <?php
            echo "Parameter:<br />";
            for($x=0;$x<count($Dataseries['Dataset']['Propertytype']['Parameter']);$x++) {
                if($x==count($Dataseries['Dataset']['Propertytype']['Parameter'])-1) {
                    echo $Dataseries['Dataset']['Propertytype']['Parameter'][$x]['symbol'].",  ".$Dataseries['Dataset']['Propertytype']['Parameter'][$x]['Property']['name'];
                } else {
                    echo $Dataseries['Dataset']['Propertytype']['Parameter'][$x]['symbol'].",  ".$Dataseries['Dataset']['Propertytype']['Parameter'][$x]['Property']['name']."<br />";
                }
            }
         ?>
    </li>
    <li>
        <?php
            echo "Variable:<br />";
            for($x=0;$x<count($Dataseries['Dataset']['Propertytype']['Variable']);$x++) {
                if($x==count($Dataseries['Dataset']['Propertytype']['Variable'])-1) {
                    echo $Dataseries['Dataset']['Propertytype']['Variable'][$x]['symbol'].",  ".$Dataseries['Dataset']['Propertytype']['Variable'][$x]['Property']['name'];
                } else {
                    echo $Dataseries['Dataset']['Propertytype']['Variable'][$x]['symbol'].",  ".$Dataseries['Dataset']['Propertytype']['Variable'][$x]['Property']['name']."<br />";
            }
        }
        ?>
    </li>
    <li><?php echo "Method: ".$Dataseries['Dataset']['Propertytype']['method'];?></li>
</ul>
<ul>
    <li><?php echo "File Number: ".$Dataseries['Dataset']['Report']['file_code']; ?></li>
    <li><?php echo "Components: ".$Dataseries['Dataset']['System']['name']; ?></li>
</ul><br>
    <h3>Data</h3>

<table  align="center" border="0" style="width:25%">
    <?php foreach($Dataseries['Condition'] as $cond)
    {
        echo  "<i>".$cond['Property']['name']." = ";
        if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") { //if exponential is requested
            echo  $cond['number']; //print raw number from db
            if((float)$cond['error']!==0.0) { //if the error is not 0.0
                echo " ± " . $cond['error']; //print error
            }
        }else{
            echo  ((float)$cond['number']); //if we didn't request exponential then convert to float and display
            if((float)$cond['error']!==0.0) {//if the error is not 0.0
                echo " ± " . ((float)$cond['error']);//print error
            }
        }
        echo  " ".$cond['Unit']['symbol']."</i><br>";
    }
    echo "<tr>";
    foreach ($Dataseries['Datapoint'][0]['Condition'] as $condition) { //loop through conditions first
        echo "<th>" . $condition['Property']['symbol']." "; //print the property symbol
        if($condition['Unit']['symbol'] ) {
            echo "( " . $condition['Unit']['symbol'] . " )"; // print unit if not unitless

        }
        echo "</th>";
    }
    foreach ($Dataseries['Datapoint'][0]['Data'] as $data) {//loop through data second
        echo "<th>" . $data['Property']['symbol']." ";//print the property symbol
        if($data['Unit']['symbol'] ) {
            echo "( " . $data['Unit']['symbol'] . " )"; // print unit if not unitless

        }
        echo "</th>";
    }
    echo "</tr>";
    foreach($Dataseries['Datapoint'] as $point)
    {
        echo "<tr>";

        foreach($point['Condition'] as $condition) {
            if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") { //if exponential is requested
                echo "<td>" . $condition['number']; //print raw number from db
                if((float)$condition['error']!==0.0) { //if the error is not 0.0
                    echo " ± " . $condition['error']; //print error
                }
                echo "</td>";
            }else{
                echo "<td>" . ((float)$condition['number']); //if we didn't request exponential then convert to float and display
                if((float)$condition['error']!==0.0) {//if the error is not 0.0
                    echo " ± " . ((float)$condition['error']);//print error
                }
                echo "</td>";
            }
        }
        foreach($point['Data'] as $data) {
            if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") { //if exponential is requested
                echo "<td>" . $data['number']; //print raw number from db
                if((float)$data['error']!==0.0) { //if the error is not 0.0
                    echo " ± " . $data['error']; //print error
                }
                echo "</td>";
            }else{
                echo "<td>" . ((float)$data['number']); //if we didn't request exponential then convert to float and display
                if((float)$data['error']!==0.0) {//if the error is not 0.0
                    echo " ± " . ((float)$data['error']);//print error
                }
                echo "</td>";
            }
        }
        echo "</tr>";
    }
    ?>
</table>
<br>
    <h4>Reference</h4>
    <ul>
    <?php echo "<b>".$ref['authors'].". "."</b>".
        $this->Html->link($ref["title"],'http://dx.doi.org/'.$ref['doi'])."<i>"." ".$ref['journal']."</i>".". "."<b>".$ref['year']."</b>".", ".$ref['volume'].", ".$ref['startpage']." - ".$ref['endpage'];
    ?>
    </ul>

