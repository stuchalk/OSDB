<?php // pr($pubs); ?>

<?php echo $this->Form->create('File', ['type' => 'file']); ?>

<?php
echo $this->Form->input('publication_id',
    ['options'=>$pubs,'empty'=>'Select Publication','label'=>'Publication']);
echo $this->Form->input('file',
    ['type' =>'file','label'=>'File Upload']);
echo $this->Form->input('num_systems',
    ['type'=>'text','label'=>'Total Systems']);
echo $this->Form->end('Add File');

?>
