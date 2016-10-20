<script type="application/javascript">
$(function() {
    $("th").tooltip();
});
</script>
<h2><?php echo '"'.$dump['Report']['title'].'"'.' Data Set';?></h2>
<ul>
    <li><?php echo "Property Type: ".$dump['Propertytype']['code']; ?>  </li>
    <li><?php echo "States: ".$dump['Propertytype']['states']."<br>".'<span style="width:25px;">Phases:</span>'.$dump['Propertytype']['phases']."<br>"; ?></li>
    <li><?php echo "Number of Components: ".$dump['Propertytype']['num_components']; ?></li>
    <li>
        <?php echo "Parameters:<br />";
        foreach($dump['Propertytype']['Parameter'] as $i=>$parameter){
            if($i!=0){
                echo "<br>";
            }
            echo $parameter['Parameter']['symbol'].",  ".$parameter['Property']['name'];
        }
        ?></li>
    <li>
        <?php echo "Variables:<br />";
        $columns=0;
        foreach($dump['Propertytype']['Variable'] as $i=>$variable){
            if(strpos($variable['Variable']['identifier'],"Error")!==false) {
                continue;
            }
            $columns++;
            if($i!=0){
                echo "<br>";
            }
            echo $variable['Variable']['symbol'].",  ".$variable['Property']['name'];
        }
        ?>
    </li>
    <li><?php echo "Method: ".$dump['Propertytype']['method'];?></li>
    <li>Components:
        <?php
        foreach($dump['System']['Substance'] as $i=>$substance){
            echo "<br>";
            echo $this->Html->link($substance['Substance']['formula']." ".$substance['Substance']['name'],"/substances/view/".$substance['Substance']['id']);
            foreach($substance['Identifier'] as $ident){
                if($ident['type']=="casrn") {
                    echo " (" . $ident['value'] . ") ";
                }
            }
        }
        ?></li>
</ul><br>
<h3>Data</h3>
<?php
?>
<table  align="center" border="0" style="width:75%">
    <thead>
    <?php
    $dataSize=0;
    for($i=0;$i<count($dump['Dataseries'][0]['Condition']);$i++) {
        echo "<tr>";
        foreach ($dump['Dataseries'] as $series) {
            echo "<td colspan='$columns'>";
            echo $series['Condition'][$i]['Property']['name'] . " = ";
            if (isset($_GET['numDisplay'])&&$_GET['numDisplay']=="exp") {
                echo  $series['Condition'][$i]['number'];
                if((float)$series['Condition'][$i]['error']!==0.0){
                    echo " ± ".$series['Condition'][$i]['error'];
                }
            } else{
                echo  ((float)$series['Condition'][$i]['number']);
                if((float)$series['Condition'][$i]['error']!==0.0){
                    echo " ± ".((float)$series['Condition'][$i]['error']);
                }
            }
            echo " " . $series['Condition'][$i]['Unit']['symbol'];
            echo "</td>";
        }
        echo "</tr>";
    }

    ///// Print table Headers
    echo "<tr>";
    for($i=0;$i<count($dump['Dataseries']);$i++) {
        if (isset($dump['Dataseries'][$i]['Datapoint'][0])) {
            foreach ($dump['Dataseries'][$i]['Datapoint'][0]['Condition'] as $condition) { //loop through conditions first
                echo "<th title=\"".$condition['Property']['name']."\">" . $condition['Property']['symbol']." "; //print the property symbol
                if($condition['Unit']['symbol'] ) {
                    echo "( " . $condition['Unit']['symbol'] . " )"; // print unit if not unitless

                }
                echo "</th>";
            }
            foreach ($dump['Dataseries'][$i]['Datapoint'][0]['Data'] as $data) {//loop through data second
                echo "<th title=\"".$data['Property']['name']."\">" . $data['Property']['symbol']." ";//print the property symbol
                if($data['Unit']['symbol'] ) {
                    echo "( " . $data['Unit']['symbol'] . " )"; // print unit if not unitless

                }
                echo "</th>";
            }
        }
        if (count($dump['Dataseries'][$i]['Datapoint']) > $dataSize) { //count how many rows
            $dataSize=count($dump['Dataseries'][$i]['Datapoint']);
        }
    }
    echo "</tr></thead>";
    for($i=0;$i<$dataSize;$i++) { //for each row of data we have
        echo "<tr>";
        foreach($dump['Dataseries'] as $series) { //loop through the series
            if(isset($series['Datapoint'][$i])) { //if we have data
                foreach ($series['Datapoint'][$i]['Condition'] as $condition) {
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
                foreach ($series['Datapoint'][$i]['Data'] as $data) {
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
            }else{
                for($p=0;$p<$columns;$p++) {
                    echo "<td></td>";
                }
            }

        }
        echo "</tr>";
    }
    //TODO: Add second dataseries tables. Add display:inline-block to the style of each dataseries.  ?>
</table>

<br>
<h4>Reference</h4>
<ul>
    <?php echo "<b>".$dump['Reference']['authors'].". "."</b>".
        $this->Html->link($dump['Reference']["title"],'http://dx.doi.org/'.$dump['Reference']['doi'])."<i>"." ".$dump['Reference']['journal']."</i>".". "."<b>".$dump['Reference']['year']."</b>".", ".$dump['Reference']['volume'].", ".$dump['Reference']['startpage']." - ".$dump['Reference']['endpage'];
    ?>
</ul>

