<?php // $desc, $minfo, $sinfo, $finfo, $dinfo, $cinfo variables coming in... ?>
<h2><?php echo $desc; ?> <small><?php echo $sinfo[0]; ?></small></h2>

<div>
    <div class="row">
        <div class="col-sm-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Measurement Info</h3>
                </div>
                <div class="panel-body text-justify">
                    <ul style="padding-left: 20px;">
                    <?php
                    foreach($minfo as $i) {
                        echo '<li class="text-left"><small>'.$i.'</small></li>';
                    }
                    ?>
                    </ul>
                </div>
            </div>
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title" style="cursor: pointer;" onclick="$('#finfo').toggleClass('hidden');">File Info</h3>
                </div>
                <div id="finfo" class="panel-body text-justify hidden">
                    <ul style="padding-left: 20px;">
                        <?php
                        foreach($finfo as $i) {
                            echo '<li class="text-left"><small>'.$i.'</small></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title" style="cursor: pointer;" onclick="$('#sinfo').toggleClass('hidden');">Spectral Info</h3>
                </div>
                <div id="sinfo" class="panel-body text-justify hidden">
                    <ul style="padding-left: 20px;">
                        <?php
                        foreach($dinfo as $i) {
                            echo '<li class="text-left"><small>'.$i.'</small></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="cursor: pointer;" onclick="$('#cinfo').toggleClass('hidden');">Processing Notes</h3>
                </div>
                <div id="cinfo" class="panel-body text-justify hidden">
                    <ul style="padding-left: 20px;">
                        <?php
                        foreach($cinfo as $type=>$j) {
                            $is=json_decode($j,true);
                            echo '<li class="text-left">'.ucfirst($type);
                            echo "<ul style=\"padding-left: 20px;\">";
                            foreach($is as $type2=>$as) {
                                if($type=="comments") {
                                    echo '<li class="text-left"><small>'.$as.'</small></li>';
                                } elseif($type=="errors") {
                                    echo '<li class="text-left"><small>'.$type2.'</small>';
                                    echo "<ul style=\"padding-left: 20px;\">";
                                    foreach($as as $type3=>$cs) {
                                        echo '<li class="text-left"><small>'.$cs.'</small></li>';
                                    }
                                    echo "</ul>";
                                    echo "</li>";
                                }
                            }
                            echo "</ul>";
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"">Export Options</h3>
                </div>
                <div class="panel-body text-justify">
                    <ul style="padding-left: 20px;">
                        <li><?php echo $this->Html->link('JCAMP-DX File','/download/jdx/'.$fileid.'.jdx'); ?></li>
                        <li><?php echo $this->Html->link('JCAMP-DX in XML','/download/xml/'.$fileid.'.xml'); ?></li>
                        <li><?php echo $this->Html->link('JSON-LD (SciData)','/reports/scidata/'.$id); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-sm-9">
            <?php echo $this->requestAction('/reports/plot/'.$id,['return']); ?>
        </div>
    </div>
</div>