<?php
//pr($data);
$rpt=$data['Report'];
$set=$data['Dataset'];
$usr=$data['User'];
$met=$set['Methodology'];
$mea=$met['Measurement'];
$con=$set['Context'];
$sys=$con['System'];
$sam=$set['Sample'];
$ser=$set['Dataseries'];

?>
<h2><?php echo $rpt['title']; ?></h2>
<p><?php echo $rpt['description']; ?></p>

<div class="left">
<h3>Measurement Info</h3>
<ul>
    <?php
        $meta=['instrumentType','instrument','vendor'];
        foreach($meta as $m) {
            if(!empty(str_replace("?","",$mea[$m]))) { echo "<li>".$mea[$m]."</li>"; }
        }
        if(!empty($mea['Setting'])) {
            $sets=$mea['Setting'];
            foreach($sets as $set) {
                (empty($set['text'])) ? $value=$set['number'] : $value=$set['text']; // So that zeroes are not lost
                (!empty($set['Unit'])) ? $unit=" ".$set['Unit']['symbol'] : $unit="";
                echo "<li>".$set['Property']['name'].": ".$value.$unit."</li>";
            }
        }

    ?>
</ul>

<?php if(isset($sam['title'])&&!empty($sam['title'])) { ?>
    <h3>Sample Info</h3>
    <ul>
        <li>
            <?php
            echo $sam['title'];
            if(!empty($sam['Annotation'])) {
                // TODO
            }?>
        </li>
    </ul>
<?php } ?>

<?php if(count($ser)==1) { ?>
    <?php
    $spectrum = $ser[0];
    if (isset($spectrum['Annotation'])) {
        foreach ($spectrum['Annotation'] as $ann) {
            if ($ann['class'] == 'origin') {
                $meta = $ann['Metadata'];
                echo "<h3>File Info</h3>";
                echo "<ul>";
                foreach ($ann['Metadata'] as $m) {
                    if($m['field']=="fileComments") { $comments=$m['value'];continue; }
                    if($m['field']=="conversionErrors") { $errors=$m['value'];continue; }
                    echo "<li>".ucfirst($m['field']).": ".$m['value']."</li>";
                }
                echo "</ul>";
            }
        }
    }
    if(!empty($spectrum['Descriptor'])) {
        echo "<h3>Spectrum Data</h3>";
        foreach ($spectrum['Descriptor'] as $d) {
            (empty($d['text'])) ? $value=$d['number'] : $value=$d['text']; // So that zeroes are not lost
            echo "<li>".ucfirst($d['title']).": ".$value."</li>";
        }
    }

    // Generate JSON for flot

    ?>
<?php } ?>

</div>

<!-- Process data -->
<?php
$xs=json_decode($spectrum['Datapoint'][0]['Condition'][0]['number']);
$ys=json_decode($spectrum['Datapoint'][0]['Data'][0]['number']);
$xy=[];
foreach($xs as $x) {

}
?>

<div class="right">
    <?php echo $this->element('flot'); ?>
</div>

<!-- Create data for flot -->
<script language="JavaScript" type="text/javascript">
    var data = [[  ]];
</script>