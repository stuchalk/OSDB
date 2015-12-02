<?php
// Variables: $data, $count
?>
<h2>Substances</h2>
<?php if(isset($term)) { echo "<h3 class='text-success'>Results for '".$term."'  <span class='badge'>".$count."</span></h3>"; } ?>

<?php
$cutoff=Configure::read('index.display.cutoff');
if($count>$cutoff) {
    echo $this->element('alpha_list',['data'=>$data,'type'=>'substances']);
} else {
    echo $this->element('column_list',['data'=>$data,'type'=>'substances']);
}
?>
<?php
if(!isset($term)) {
?>
<div class="row">
    <div class="col-md-4">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"">Export Options</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <?php echo $this->Html->image('xml.png',['url'=>'/substances/index/XML','alt'=>'Output as XML','class'=>'img-responsive']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Html->image('json.png',['url'=>'/substances/index/JSON','alt'=>'Output as JSON','class'=>'img-responsive']); ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<?php
}
?>