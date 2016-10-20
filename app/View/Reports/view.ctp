<?php // $desc (not used since adding $sub), $minfo, $sinfo, $finfo, $dinfo, $cinfo, $rinfo, $sub variables coming in... ?>
<h2>
    <?php
    $path=Configure::read('path');
    $link=str_replace($sub['name'],'<a href="'.$path.'/substances/view/'.$sub['id'].'">'.$sub['name'].'</a>',$desc);
    echo $link;
    ?>
    <small class="pull-right"><?php echo $sinfo[0]; ?></small>
</h2>

<div class="row">
    <div class="col-sm-3">
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-primary">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h3 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Measurement Info</a>
                    </h3>
                </div>
                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
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
            </div>
            <div class="panel panel-success">
                <div class="panel-heading" role="tab" id="headingTwo">
                    <h3 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            File Info</a>
                    </h3>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                    <div id="finfo" class="panel-body text-justify">
                        <ul style="padding-left: 20px;">
                            <?php
                            foreach($finfo as $i) {
                                echo '<li class="text-left"><small>'.$i.'</small></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel-heading" role="tab" id="headingThree">
                    <h3 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Spectral Info</a>
                    </h3>
                </div>
                <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                    <div id="sinfo" class="panel-body text-justify">
                        <ul style="padding-left: 20px;">
                            <?php
                            foreach($dinfo as $i) {
                                echo '<li class="text-left"><small>'.$i.'</small></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">
                    <h3 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Processing Notes</a>
                    </h3>
                </div>
                <div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingFour">
                    <div id="cinfo" class="panel-body text-justify">
                        <ul style="padding-left: 20px;">
                            <?php
                            foreach($cinfo as $type=>$j) {
                                $is=json_decode($j,true);
                                echo '<li class="text-left">'.ucfirst($type);
                                echo "<ul style=\"padding-left: 20px;\">";
                                foreach($is as $type2=>$as) {
                                    if($type=="comments") {
                                        if(!is_array($as)) { echo '<li class="text-left"><small>'.$as.'</small></li>'; }
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
            </div>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"">Export Options</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4" style="padding: 0 5px;">
                            <?php
                            if(is_null($jdxurl)) {
                                echo $this->Html->image('jcamp.jpg',['url'=>'/spectra/view/'.$fileid.'/JCAMP','alt'=>'Output as JCAMP-DX','class'=>'img-responsive']);
                            } else {
                                echo $this->Html->image('jcamp.jpg',['url'=>$jdxurl,'alt'=>'Original JCAMP-DX','target'=>'_blank','class'=>'img-responsive']);
                            }
                            ?>
                        </div>
                        <div class="col-md-4" style="padding: 0 5px;">
                            <?php echo $this->Html->image('xml.png',['url'=>'/spectra/view/'.$fileid.'/XML','alt'=>'Output as JCAMP-DX XML','class'=>'img-responsive']); ?>
                        </div>
                        <div class="col-md-4" style="padding: 0 5px;">
                            <?php echo $this->Html->image('jsonld.png',['url'=>'/spectra/view/'.$id.'/JSONLD','alt'=>'Output as JSON-LD','class'=>'img-responsive']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-9">
        <?php echo $this->requestAction('/spectra/plot/'.$id.'/null/auto/450',['return']); ?>
        <div class="row" style="margin-top: 10px;">
            <div class="col-sm-7">
                <?php if(!is_null($splash)) {
                    echo "<b>".$splash."</b> (".$this->Html->link('What is this?','http://splash.fiehnlab.ucdavis.edu/',['target'=>'_blank']).")";
                }
                if(!empty($rinfo)) {
                    if(!is_null($rinfo['doi'])) {
                        echo $this->Html->link($rinfo['citation']." DOI: ".$rinfo['doi'],"http://dx.doi.org/".$rinfo['doi'],['target'=>"_blank"]);
                    } elseif(!is_null($rinfo['url'])) {
                        echo $this->Html->link($rinfo['citation']." ".$rinfo['url'],$rinfo['url'],['target'=>"_blank"]);
                    } else {
                        if(!empty($rinfo['citation'])) {
                            echo $rinfo['citation'];
                        } else {
                            if(!is_null($rinfo['title'])) { echo "'".$rinfo['title']."' "; }
                            if(!is_null($rinfo['authors'])) { echo $rinfo['authors'].", "; }
                            echo $rinfo['journal']." ".$rinfo['year']." ".$rinfo['volume']." ".$rinfo['startpage'];
                            if(!is_null($rinfo['endpage'])) { echo "-".$rinfo['endpage']; }
                        }
                    }
                }
                ?>
            </div>
            <div class="col-sm-5 text-right">
                <?php
                $text="";
                foreach($finfo as $i) {
                    if(stristr($i,'owner')) {
                        list($temp,$text)=explode(": ",$i);
                    }
                }
                if($text=="") {
                    echo "No copyright info available";
                } else {
                    if(stristr($text,'copy')||stristr($text,'(c)')||stristr($text,'©')) {
                        echo $text;
                    } else {
                        echo "No copyright info available";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>