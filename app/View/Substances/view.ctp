<?php
$substance=$data['Substance'];
$identifiers=$data['Identifier'];
$system=$data['System'];
$idnicetext=['inchi'=>'InChI String','inchikey'=>'InChi Key','casrn'=>'CASRN','pubchemId'=>'PubChem ID','smiles'=>'SMILES']?>

<h2><?php echo $substance['name']; ?></h2>
<ul>
    <li><?php echo "Formula: ".$substance['formula'];?></li>
    <li><?php echo "Molecular Weight: ".$substance['molweight']." g/mol";?></li>
    <?php foreach($identifiers as $identifier)
    {
        if(isset($idnicetext[$identifier['type']])) {
            echo "<li>".$idnicetext[$identifier['type']].": ".$identifier['value']."</li>";
            }
        }
        ?>
    </li>
</ul>
<h3>Systems</h3>
<?php foreach($system as $sys) {
        echo "<h4 style='margin-top: 0.5em;'>". $this->Html->link($sys['name'],'/systems/view/'.$sys['id'])."</h4>";
            foreach($sys['Context'] as $context){
                $rpt=$context['Dataset']['Report'];
                echo "<ul><li>". $this->Html->link($rpt['title'],'/spectra/view/'.$rpt['id'])."</li></ul>";
        }
    }
?>
<?php //pr($system); ?>