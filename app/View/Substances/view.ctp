<?php //pr($data); ?>
<?php
$substance=$data['Substance'];
$identifiers=$data['Identifier'];
$system=$data['System'];
$idnicetext=['inchi'=>'InChI String','inchikey'=>'InChi Key','casrn'=>'CASRN','pubchemId'=>'PubChem ID','smiles'=>'SMILES']?>

<h2>Substance</h2>
<ul><li><?php echo "Substance Name: ".$substance['name'];?></li>
    <li><?php echo "Formula: ".$substance['formula'];?></li>
    <li><?php echo "Molecular Weight: ".$substance['molweight']." g/mol";?></li>
    <li><?php foreach($identifiers as $identifier)
        {
            echo $idnicetext[$identifier['type']].": ".$identifier['value']."<br>";
        }
        ?>
    </li>
</ul><br>
<h3>Systems</h3>
    <?php foreach($system as $sys) {
        echo "<h4 style='margin-top: 0.5em;'>". $this->Html->link($sys['name'],'/systems/view/'.$sys['id'])."</h4>";
            foreach($sys['Dataset'] as $dataset){
                echo "<ul><li>". $this->Html->link($dataset['Propertytype']['method'],'/datasets/view/'.$dataset['id'])."</li></ul>";

        }

    }
    ?>

