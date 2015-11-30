<p>Click on a letter below to show substances starting with that letter</p>
<div class="row">
    <div class="col-md-6">
        <?php
        $chars = array_keys($data);
        foreach ($chars as $char) {
            echo "<div onclick=\"showletter('" . $char . "')\" class='btn btn-success btn-med' role='button'>" . $char . "</div>&nbsp;";
        }
        ?>
    </div>
</div>
<div style="margin-top: 5px;">
    <?php
    foreach ($data as $char => $iarray) {
    ?>
    <div class="row">
        <div class="col-md-4">
            <?php if ($char == $chars[0]) { ?>
            <div id="<?php echo $char; ?>" class="letter list-group" style="display: block;">
                <?php } else { ?>
                <div id="<?php echo $char; ?>" class="letter list-group" style="display: none;">
                    <?php }
                    foreach ($iarray as $id => $name) {
                        echo html_entity_decode($this->Html->link($name, '/'.$type.'/view/' . $id,['class'=>'list-group-item']));
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>