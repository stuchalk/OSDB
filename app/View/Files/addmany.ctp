<?php // pr($pubs); ?>

<?php
echo $this->Form->create('File', ['type' => 'file']);
echo $this->Form->input('file',['type'=>'file','label'=>'File Upload']);
echo $this->Form->input('num_systems',['type'=>'text','label'=>'Total Systems']);
echo $this->Form->end('Add File');
?>