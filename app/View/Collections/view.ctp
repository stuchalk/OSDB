<?php
// Variables from controller: $data, $subs
$c=$data['Collection'];
$u=$data['User'];
//pr($subs);exit;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $c['name']; ?> Collection</h3>
            </div>
            <div class="panel-body">
                <?php echo $c['description']; ?><br />
                Uploaded by: <?php echo $u['fullname']; ?><br />
                Orginal source: <?php echo $c['source']; ?><br />
                Website: <?php echo $this->Html->link($c['url'],$c['url'],['target'=>'_blank']); ?>
                <?php
                if($c['copyright']!="") {
                    echo "<br />Copyright: ".$c['copyright'];
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php if(count($subs)<12) { ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Compounds</h3>
            </div>
            <div class="panel-body">
                <?php
                $index=0;
                foreach($subs as $name=>$r) {
                    echo $this->element('molspectra',['index'=>$index,'name'=>$name,'cols'=>3] + $r);
                    $index++;
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Compounds</h3>
                </div>
                <div class="panel-body" style="max-height: 500px;overflow-y:scroll;">
                    <?php
                    foreach($subs as $name=>$r) {
                        echo $this->element('molspec2',['name'=>$name] + $r);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>