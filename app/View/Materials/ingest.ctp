<h2>File Upload</h2>
<p>Please select a text file for processing</p>
<?php
echo $this->Form->create('Material',array('action'=>'ingest','type'=>'file'));
echo $this->Form->input('file',array('type'=>'file','label'=>false));
echo $this->Form->end('Upload File');
?>