<?php
// In a view file use the following command to display
// $this->element('jmolviewer',['chemicals'=>["chemical1"=>["inchi"=>"inchi1"],"chemical2"=>["inchi"=>"inchi2"]],"size"=>???])

// Chemicals
if(isset($chemicals))
{
    if(!isset($size)) { $size=190; }
    echo "<div id='chemicals'>";
    echo "<h3 style='text-align: right;'>Chemicals</h3>\n";
    for($x=0;$x<count($chemicals);$x++)
    {
        $chem=$chemicals[$x];
        echo "<div id='chemical".$x."' class='chemical'>";
        //echo $this->Html->image('http://cactus.nci.nih.gov/chemical/structure/'.$chem['inchi'].'/image?format=png&linewidth=2',array('alt'=>$chem['name']));
        echo "<script type='text/javascript'>\n";
        echo "  var Info".$x." = { color: '#000000', height: ".$size.", width: ".$size.", use: 'HTML5', defaultModel: '$".$chem['inchikey']."', j2sPath: '/sol/js/jsmol/j2s' };\n";
        echo "  Jmol.getTMApplet('chem".$x."', Info".$x.");\n";
        echo "</script>\n";
        echo "<p>".$chem['name']."<br />\n";
        echo "View @ ";
        echo $this->Html->link('NIST','http://webbook.nist.gov/cgi/cbook.cgi?ID='.$chem['casrn'],['target'=>'_blank']).", ";
        echo $this->Html->link('ChemSpider','http://www.chemspider.com/Search.aspx?q='.$chem['inchi'],['target'=>'_blank']);
        echo "</p>";
        echo "</div>";
    }
    echo "<div class='floatreset'></div></div>";
}
echo "<div class='floatreset'></div></div>";

?>