<?php
// In a view file use the following command to display
// $this->element('molspectra',['name'='???','index'=>?,"inchikey"=>"???",'spectra'=>Array,'height'=>value,'width'=>value])
// $index (the index of this viewer on the page - allows id of jmol applet to be unique )
// $name (name of compound)
// $id (id of compound}
// $inchikey (inchikey of compound}
// $spectra (array of id/name pairs of the spectra)
// $height of div
// $width of div
if(!isset($height))     { $height=210; }
if(!isset($width))      { $width="100%"; }
if(!isset($spectra))    { $spectra=[]; }
if(!isset($index))      { $index=0; }

if(!isset($named))      { $named=true; }
if(!isset($fontsize))   { $fontsize=11; }
if(!isset($links))      { $links=true; }
if(!isset($cols))       { $cols=12; }

// Chemicals
if(isset($inchikey))
{
    echo "<div id='chemical".$index."' class='chemical col-sm-6 col-md-".$cols."'>";
    // Show spectral links
    echo "<div class='panel panel-primary'>";
    if(!empty($spectra)) {
        echo "<div class='btn-group btn-group-justified' role='group' style='margin-bottom: 0;'>";
        foreach($spectra as $sid=>$title) {
            echo "<div class='btn-group' role='group' style='font-size: 12px;'>";
            echo $this->Html->link($title,'/spectra/view/'.$sid,['class'=>'btn btn-default','role'=>'button','style'=>'padding: 5px;']);
            echo "</div>";
        }
        echo "</div>";
    }
    // Show JSmol
    echo $this->element('jsmol',['uid'=>$index,'height'=>$height,'width'=>$width,
        'inchikey'=>$inchikey,'inchi'=>$inchi,'name'=>$name]);
    // Show chemical name
    if($named) {
        echo "<div style='text-align: center;margin-top: 5px;'>";
        echo $this->Html->link($name,'/compounds/view/'.$id);
        echo "</div>";
    }
    // Show links on other sites
    if($links) {
        echo "<div style='text-align: center;margin-bottom: 0;font-size: ".$fontsize."px;'>View @ ";
        echo $this->Html->link('ChemSpider','http://www.chemspider.com/Search.aspx?q='.$inchikey,['target'=>'_blank'])." ";
        echo $this->Html->link('NIST','http://webbook.nist.gov/cgi/cbook.cgi?InChI='.$inchikey,['target'=>'_blank'])." ";
        echo $this->Html->link('PubChem','https://pubchem.ncbi.nlm.nih.gov/compound/'.$inchikey,['target'=>'_blank']);
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
} else {
    if(!isset($grid))       { $grid=3;}
    ?>
    <div class='col-sm-<?php echo $grid; ?>'>
        <div id='chemical<?php echo $index; ?>' class='panel panel-default'>
            <div class="panel-heading text-center" style="padding: 5px;">
                <?php
                if(isset($label)) {
                    echo "<h5>".$label."</h5>";
                } elseif(!empty($spectra)) {
                    echo "<p style='margin-top: 5px;margin-bottom: 10px;'>";
                    foreach($spectra as $id=>$title) {
                        echo $this->Html->link($title,'/spectra/view/'.$id,['class'=>'linkbutton'])."&nbsp;";
                    }
                    echo "</p>";
                } else {
                    echo "<h4>&nbsp;</h4>";
                }
                $url=str_replace("<id>",$inchikey,Configure::read('cir.url'));
                echo $this->element('jmol',['uid'=>$index,'url'=>$url,'height'=>$height,'width'=>$width]);
                echo "<p style='margin-top: 5px;'>".$name."</p>";
                echo "<p style='margin-bottom: 0;font-size: 10px;'>View @ ";
                echo $this->Html->link('ChemSpider','http://www.chemspider.com/Search.aspx?q='.$inchikey,['target'=>'_blank'])." ";
                echo $this->Html->link('NIST','http://webbook.nist.gov/cgi/cbook.cgi?InChI='.$inchikey,['target'=>'_blank'])." ";
                echo $this->Html->link('PubChem','https://pubchem.ncbi.nlm.nih.gov/compound/'.$inchikey,['target'=>'_blank']);
                echo "</p>";
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>