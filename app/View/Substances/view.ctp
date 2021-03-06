<?php
$sub=$data['Substance'];
$identifiers=$data['Identifier'];
$systems=$data['System'];
$idnicetext=['inchi'=>'InChI String','inchikey'=>'InChI Key','casrn'=>'CASRN','pubchemId'=>'PubChem CID','smiles'=>'SMILES','wikidata'=>'Wikidata'];
$inchi=$inchikey=$casrn="";
foreach($identifiers as $identifier) {
    if ($identifier['type']=='inchi') {
        $inchi=$identifier['value'];
    } elseif ($identifier['type']=='inchikey') {
        $inchikey=$identifier['value'];
    } elseif ($identifier['type']=='casrn') {
        $casrn=$identifier['value'];
    }
}
?>
<div class="row">
    <div class="col-sm-8">
    <h2><?php echo $sub['name']; ?></h2>
    <ul>
        <li><?php echo "Formula: ".$sub['formula'];?></li>
        <li><?php echo "Molecular Weight: ".$sub['molweight']." g/mol";?></li>
        <?php
        //pr($identifiers);
        $wiki='no';
        foreach($identifiers as $identifier) {
            if(isset($idnicetext[$identifier['type']])) {
                if($identifier['type']=='pubchemId') {
                    echo "<li>".$idnicetext[$identifier['type']].": ";
                    echo $this->Html->link($identifier['value'],'https://pubchem.ncbi.nlm.nih.gov/compound/'.$identifier['value'],['target'=>'_blank'])." <span class=\"glyphicon glyphicon-new-window\" aria-hidden=\"true\"></span></li>";
                } elseif($identifier['type']=='wikidata') {
                    $wiki='yes';
                    echo "<li>".$idnicetext[$identifier['type']].": ";
                    echo $this->Html->link($identifier['value'],'https://www.wikidata.org/wiki/'.$identifier['value'],['target'=>'_blank'])." <span class=\"glyphicon glyphicon-new-window\" aria-hidden=\"true\"></span></li>";
                } else {
                    echo "<li>".$idnicetext[$identifier['type']].": ".$identifier['value']."</li>";
                }
            }
        }
        if($wiki=='no' and $_SERVER['REMOTE_ADDR']='139.62.52.13') {
            echo "<li>".$this->Html->link('Add Wikidata ID','/identifiers/wikidata/'.$sub['name'])."</li>";
        }
        ?>
    </ul>
    <h3>Systems and Spectra</h3>
    <?php
    foreach($systems as $sys) {
        ?>
        <div class="col-sm-6 col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $this->Html->link($sys['name'],'/systems/view/'.$sys['id']); ?></h3>
                </div>
                <div class="list-group">
                    <?php
                    foreach($sys['Context'] as $context) {
                        $rpt=$context['Dataset']['Report'];
                        echo $this->Html->link($rpt['title'],'/spectra/view/'.$rpt['id'],['class'=>'list-group-item']);
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
    <div class="col-sm-4 col-lg-4" style="padding-top: 40px;">
        <div class="col-sm-12 col-lg-12" style="margin-bottom: 20px;">
            <?php
            $chem=['id'=>$sub['id'],'name'=>$sub['name'],'inchi'=>$inchi,'inchikey'=>$inchikey,'casrn'=>$casrn];
            echo $this->element('molspectra',['index'=>0,'grid'=>12,'named'=>false]+$chem); // Sets it full width with col-md-4 grid
            ?>
        </div>
        <div class="text-center" style="margin-top: 20px;">
            <?php
            echo $this->Html->image('xml.png',['width'=>'120px','url'=>'/substances/view/'.$sub['id'].'/XML','alt'=>'Output as XML','style'=>'padding-right: 20px;']);
            echo $this->Html->image('json.png',['width'=>'120px','url'=>'/substances/view/'.$sub['id'].'/JSON','alt'=>'Output as JSON']);
            ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>