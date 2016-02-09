<?php
//pr($data);
//pr($reps);
$cs=$data['Collection'];
$u=$data['User'];
$path=Configure::read('path');
// TODO: Preferences
?>
    <h2>OSDB Dashboard</h2>
    <div class="row">
        <div class="col-md-4">
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
    <h3>My Spectra <a href="<?php echo $path; ?>/files/upload"><span class='glyphicon glyphicon-plus-sign'></span></a></h3>
<?php
$index=0;
foreach($reps as $name=>$r) {
    echo $this->element('molspectra',['index'=>$index,'name'=>$name] + $r);
    $index++;
}
?>