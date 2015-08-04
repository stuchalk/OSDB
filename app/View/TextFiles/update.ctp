<?php

?>
<h2>Update Text File</h2>

<?php echo $this->Form->create(null,['url'=>'/textfiles/update/'.$id]); ?>
<?php
echo $this->Form->input('inputFile',
    ['type'=>'select','options'=>$file,'empty'=>'Select File','label'=>'File','selected'=>$id]);
//echo $this->Form->input('ruleset',
    //['type'=>'select','options'=>$rulesets,'empty'=>'Select Ruleset','label'=>'Ruleset','selected'=>$rulesets['name']]);
echo $this->Form->end('Update File');
?>


