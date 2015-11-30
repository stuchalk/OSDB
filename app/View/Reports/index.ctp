<h2 class="pull-left">Spectra</h2>
<!--Page controls-->
<div class="pull-right" style="margin-top: 20px;">
    <?php
    if($offset!=0) {
        echo $this->Html->link('PREV','/spectra/index/'.($offset-$limit).'/'.$limit,['class'=>'btn btn-success btn-sm','role'=>'button']);
    }
    echo "&nbsp;Substances ".($offset+1)."-".($offset+$limit+1)."&nbsp;";
    if($offset<$count-$limit) {
        echo $this->Html->link('NEXT','/spectra/index/'.($offset+$limit).'/'.$limit,['class'=>'btn btn-success btn-sm','role'=>'button']);
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