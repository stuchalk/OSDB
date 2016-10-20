<?php
$cs=$data['Collection'];
$u=$data['User'];
$path=Configure::read('path');
// TODO: Preferences
?>

<h2>OSDB Dashboard</h2>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">My Collections <a href="<?php echo $path; ?>/collections/add" class="pull-right"><span class='glyphicon glyphicon-plus-sign'></span></a></h3>
            </div>
            <div class="list-group">
                <?php
                foreach($cs as $c) {
                    echo $this->Html->link($c['name'],'/collections/view/'.$c['id'],['class'=>'list-group-item']);
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php if(count($reps)<12) { ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">My Spectra <a href="<?php echo $path; ?>/files/upload"><span class='glyphicon glyphicon-plus-sign'></span></a></h3>
            </div>
            <div class="panel-body">
                <?php
                $index=0;
                foreach($reps as $name=>$r) {
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
                    <h3 class="panel-title">My Spectra <a href="<?php echo $path; ?>/files/upload"><span class='glyphicon glyphicon-plus-sign'></span></a></h3>
                </div>
                <div class="panel-body" style="max-height: 500px;overflow-y:scroll;">
                    <?php
                    foreach($reps as $name=>$r) {
                        echo $this->element('molspec2',['name'=>$name] + $r);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
