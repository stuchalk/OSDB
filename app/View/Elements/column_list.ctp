<?php
// Element: List results in three columns
// Variables: $data, $type
?>

<div class="row">
    <?php
    if(count($data)<11) {
        $chunk=10;
    } else {
        $chunk=ceil(count($data)/3);
    }
    $chunks=array_chunk($data,$chunk,true);
    foreach($chunks as $chunk) {
        if(count($chunk)>0) {
            ?>
            <div class="col-sm-4">
                <div class="panel panel-primary">
                    <div class="list-group">
                        <?php
                        foreach($chunk as $id=>$name) {
                            echo $this->Html->link($name,'/'.$type.'/view/'.$id,['class'=>'list-group-item']);
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>