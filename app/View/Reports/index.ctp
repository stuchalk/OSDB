<?php
    if($search=="%") {
        $search="";
        $heading="";
    } else {
        $heading=" (for Substances matching '*".$search."*')";
        $search="/".$search;
    }
?>
<h2 class="pull-left">Spectra<?php echo $heading; ?></h2>

<!--Page controls-->
<div class="pull-right" style="margin-top: 20px;">
    <?php
    if($offset!=0) {
        echo $this->Html->link('PREV','/spectra/index/'.($offset-$limit).'/'.$limit.$search,['class'=>'btn btn-success btn-sm','role'=>'button']);
    }
    if($count>$limit) {
        if($count>$offset+$limit) {
            echo "&nbsp;Substances ".($offset+1)."-".($offset+$limit)." of ".$count;
        } else {
            echo "&nbsp;Substances ".($offset+1)."-".$count;
        }
    }
    echo "&nbsp;";
    if($offset<$count-$limit) {
        echo $this->Html->link('NEXT','/spectra/index/'.($offset+$limit).'/'.$limit.$search,['class'=>'btn btn-success btn-sm','role'=>'button']);
    }
    ?>
</div>
<div class="clearfix"></div>

<!-- Page contents-->
<?php
$index=0;
foreach($data as $name=>$r) {
    echo $this->element('molspectra',['index'=>$index,'name'=>$name,'grid'=>4] + $r);
    $index++;
}
?>