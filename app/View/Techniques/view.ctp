<?php
// Variables from controller: $data, $subs
$t=$data['Technique'];
?>
    <h2><?php echo $t['title']; ?></h2>
    <p>Technique code: <?php echo $t['matchstr']; ?> </p>

    <h3>Substances</h3>
<?php
$index=0;
foreach($subs as $name=>$r) {
    echo $this->element('molspectra',['index'=>$index,'name'=>$name,'grid'=>4] + $r);
    $index++;
}
?>