<h2>Rectify The Data</h2>
<?php // pr($pubs); ?>

<?php echo $this->Form->create('TextFile', ['type' => 'file']); ?>

<?php
echo $this->Form->input('inputFile', ['options' => $file,'empty'=>'Select file', 'label' => false]);
echo $this->Form->end('Rectify Data');
?>