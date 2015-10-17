<?php
// In a view file use the following command to display
// $this->element('molspectra',['name'='???','index'=>?,"inchikey"=>"???",'spectra'=>Array])
// $index (the index of this viewer on the page - allows id of jmol applet to be unique )
// $name (name of compound)
// $id (id of compound}
// $inchikey (inchikey of compound}
// $spectra (array of id/name pairs of the spectra)

// Chemicals
if(isset($inchikey))
{
    if(!isset($index)) { $index=0; }
    echo "<div id='chemical".$index."' style='float: left;text-align: center;' class='chemical'>";
    $url=str_replace("<id>",$inchikey,Configure::read('cir.url'));
    echo "<p style='margin-top: 5px;margin-bottom: 10px;'>";
    foreach($spectra as $id=>$title) {
        echo $this->Html->link($title,'/spectra/view/'.$id,['class'=>'linkbutton'])."&nbsp;";
    }
    echo "</p>";
    echo $this->element('jmol',['uid'=>$index,'url'=>$url,'height'=>150,'width'=>230]);
    echo "<p style='margin-top: 5px;'>".$name."</p>";
    echo "<p style='margin-bottom: 0;font-size: 10px;'>View @ ";
    echo $this->Html->link('ChemSpider','http://www.chemspider.com/Search.aspx?q='.$inchikey,['target'=>'_blank'])." ";
    echo $this->Html->link('NIST','http://webbook.nist.gov/cgi/cbook.cgi?InChI='.$inchikey,['target'=>'_blank'])." ";
    echo $this->Html->link('PubChem','https://pubchem.ncbi.nlm.nih.gov/compound/'.$inchikey,['target'=>'_blank']);
    echo "</p>";
    echo "</div>";
    echo "<div class='clear'></div>";
}