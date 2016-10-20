<?php $t=$data['Technique']; // Variables from controller: $data, $subs ?>

    <h2><?php echo $t['title']; ?></h2>
    <p><i>Technique code: <?php echo $t['matchstr']; ?></i></p>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Substances (<?php echo count($subs); ?>)</h3>
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