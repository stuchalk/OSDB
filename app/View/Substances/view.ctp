<?php
$substance=$data['Substance'];
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
<div class="col-md-4">
    <?php
    $chem=['name'=>$substance['name'],'inchi'=>$inchi,'inchikey'=>$inchikey,'casrn'=>$casrn];
    echo $this->element('molspectra',['index'=>0,'grid'=>12]+$chem); // Sets it full width with col-md-4 grid
    ?>
    <div class="text-center">
    <?php
    echo $this->Html->image('xml.png',['width'=>'150','url'=>'/substances/view/'.$substance['id'].'/xml','alt'=>'Output as XML','style'=>'padding-right: 20px;']);
    echo $this->Html->image('json.png',['width'=>'150','url'=>'/substances/view/'.$substance['id'].'/json','alt'=>'Output as JSON','style'=>'padding-right: 20px;']);
    ?>
    </div>
</div>
<div class="col-md-8">
    <h2><?php echo $substance['name']; ?></h2>
    <ul>
        <li><?php echo "Formula: ".$substance['formula'];?></li>
        <li><?php echo "Molecular Weight: ".$substance['molweight']." g/mol";?></li>
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
            echo "<li>".$this->Html->link('Add Wikidata ID','/identifiers/wikidata/'.$substance['name'])."</li>";
        }
        ?>
    </ul>
    <h3>Systems and Spectra</h3>
    <?php
    foreach($systems as $sys) {
        ?>
        <div class="col-md-6">
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
<div class="clearfix"></div>
<code>
    <?php //pr($systems); ?>
</code>
