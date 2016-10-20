<div class="well well-sm col-sm-6">
    <div class="col-sm-3" style="padding: 0;">
        <?php
        // image
        $path=WWW_ROOT.'img'.DS.'mol'.DS.$inchikey.'.png';
        if(stristr(WWW_ROOT,'sds')) {
            if(!file_exists($path)) {
                $this->requestaction('/identifiers/addpng/'.$id);
            }
        } else {
            if(!file_exists($path)) {
                // This allows osdb.info to get png file of molecule from sds
                $opts=["ssl"=>["verify_peer"=>false, "verify_peer_name"=>false,]];
                $png=file_get_contents('https://sds.coas.unf.edu/osdb/identifiers/addpng/'.$id.'/yes',false,stream_context_create($opts));
                $fp=fopen($path,"w");
                fwrite($fp,$png);
                fclose($fp);
            }
        }
        echo $this->Html->image('mol/'.$inchikey.'.png',['class'=>'img-thumbnail','alt'=>$name]);
        ?>
    </div>
    <div class="col-sm-9">
        <?php
        // identifiers
        echo "<p>".$this->Html->link($name,'/compounds/view/'.$id)."<br />";
        echo "Inchikey: ".$inchikey."</p>";
        // spectra buttons
        foreach($spectra as $sid=>$type) { ?>
            <a href="/spectra/view/<?php echo $sid; ?>" target="_blank" class="btn btn-success btn-sm"><?php echo $type; ?></a>
        <?php } ?>
    </div>
</div>