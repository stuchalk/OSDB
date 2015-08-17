<?php
//pr($data);exit;
$rpt=$data['Report'];
$set=$data['Dataset'];
$file=$set['File'];
$usr=$data['User'];
$met=$set['Methodology'];
$mea=$met['Measurement'];
$con=$set['Context'];
$sys=$con['System'];
$sam=$set['Sample'];
$ser=$set['Dataseries'];
$flot=[];
?>
<h2><?php echo $rpt['description']; ?></h2>

<?php if(isset($met['Measurement'])) { ?>
<div class="left">
    <?php
        if($mea['technique']=="Mass Spectrometry") {
            $flot['tech']="ms";
        } elseif($mea['technique']=="Nuclear Magnetic Resonance") {
            $flot['tech']='nmr';
        } elseif($mea['technique']=="Infrared Spectroscopy") {
            $flot['tech']='ir';
        }
    $marray=[];
        if(strtolower($mea['instrumentType'])!=$flot['tech']) {
            $marray[]=$mea['instrumentType'];
        }
        $meta=['instrument','vendor'];
        foreach($meta as $m) {
            if(!empty(str_replace("?","",$mea[$m]))) { $marray[]=$mea[$m]; }
        }
        if(!empty($mea['Setting'])) {
            $sets=$mea['Setting'];
            foreach($sets as $set) {
                (empty($set['text'])) ? $value=$set['number'] : $value=$set['text']; // So that zeroes are not lost
                $name=$set['Property']['name'];
                if($name=="Observe Frequency") { $flot['freq']=$value; }
                if($name=="Observe Nucleus") { $flot['nuc']=$value; }
                (!empty($set['Unit'])) ? $unit=" ".$set['Unit']['symbol'] : $unit="";
                $marray[]=$name.": ".$value.$unit;
            }
        }
        if(!empty($marray)) {
            echo "<h3>Measurement Info</h3><ul>";
            foreach($marray as $m) {
                echo "<li>".$m."</li>";
            }
            echo "</ul>";
        }
    ?>

<?php } ?>

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

<?php if(count($ser)==1) {
    $spectrum = $ser[0];
    if(isset($spectrum['Annotation'])) {
        foreach ($spectrum['Annotation'] as $ann) {
            if ($ann['class'] == 'origin') {
                $meta = $ann['Metadata'];
                echo "<h3>File Info</h3>";
                echo "<ul>";
                foreach ($ann['Metadata'] as $m) {
                    if($m['field']=="fileComments")         { $comments=$m['value'];continue; }
                    if($m['field']=="conversionErrors")     { $errors=$m['value'];continue; }
                    if($m['field']=="date") {
                        echo "<li>".ucfirst($m['field']).": ".date("M j, Y",strtotime($m['value']))."</li>";
                    } else {
                        echo "<li>".ucfirst($m['field']).": ".$m['value']."</li>";
                    }
                }
                echo "</ul>";
            }
        }
    }
    $points=null;
    //debug($spectrum);
    if(!empty($spectrum['Descriptor'])) {
        echo "<h3>Spectrum Data</h3>";
        echo "<ul>";$scale=1;
        if($spectrum['level']=='processed'&&$spectrum['processedType']=="frequency") {
            echo "<li>Native Format: Frequency Spectrum</li>";$scale=$flot['freq'];
        } elseif ($spectrum['level']=='processed'&&$spectrum['processedType']=="chemical shift") {
            echo "<li>Native Format: Chemical Shift</li>";
        }
        foreach ($spectrum['Descriptor'] as $d) {
            $value=0;
            (empty($d['text'])) ? $value=(float) $d['number'] : $value=$d['text']; // So that zeroes are not lost
            if(stristr($d['title'],"points"))    { $flot['points']=$value; }
            if(stristr($d['title'],"maximum x")) { $flot['maxx']=$value/$scale;$value=number_format(round($value/$scale),0); }
            if(stristr($d['title'],"minimum x")) { $flot['minx']=$value/$scale;$value=number_format(round($value/$scale),0); }
            if(stristr($d['title'],"increment")) { $value=number_format($value/$scale,5); }
            if(stristr($d['title'],"first x"))   { $value=number_format($value/$scale,0); }
            if(stristr($d['title'],"last x"))    { $value=number_format($value/$scale,0); }
            if(stristr($d['title'],"first y"))   { $value=number_format($value,0); }
            if(stristr($d['title'],"maximum y")) { $value=number_format($value,0); }
            if(stristr($d['title'],"minimum y")) { $flot['miny']=$value;$value=number_format($value,0); }
            echo "<li>".ucfirst($d['title']).": ".$value."</li>";
        }
        echo "</ul>";
    }
}
?>
</div>

<div class="middle">
    <h3>Spectrum</h3>
    <?php
    $flot['xsid']=$ser[0]['Datapoint'][0]['Condition'][0]['id'];
    $flot['ysid']=$ser[0]['Datapoint'][0]['Data'][0]['id'];
    echo $this->element('flot',['config'=>$flot]);
    ?>
    <h3>Export</h3>
    <p>Download the data as one of the following formats</p>
    <?php echo $this->Html->link('JCAMP file','/download/jdx/'.$file['id'].'.jdx'); ?> •
    <?php echo $this->Html->link('JCAMP in XML','/download/xml/'.$file['id'].'.xml'); ?> •
    <?php echo $this->Html->link('SciData (JSON-LD)','/reports/scidata/'.$rpt['id']); ?>
    <?php //pr($ser); ?>
</div>