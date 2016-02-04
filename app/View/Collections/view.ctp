<?php
// Variables from controller: $data, $subs
$c=$data['Collection'];
$u=$data['User'];
?>
<h2><?php echo $c['name']; ?> Collection</h2>
<p><?php echo $c['description']; ?><br />
Provided by: <?php echo $u['fullname']; ?><br />
Orginal source: <?php echo $c['source']; ?><br />
Website: <?php echo $this->Html->link($c['url'],$c['url'],['target'=>'_blank']); ?></p>

<h3>Substances</h3>
<?php
$index=0;
foreach($subs as $name=>$r) {
    echo $this->element('molspectra',['index'=>$index,'name'=>$name,'grid'=>4] + $r);
    $index++;
}
?>