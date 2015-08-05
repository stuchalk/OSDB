<h2>Upload a Spectrum</h2>
<?php
echo $this->Form->create('File', ['type' => 'file']);
echo $this->Form->input('file', ['type' =>'file','label'=>'File Upload']);
echo $this->Form->end('Add File');
?>