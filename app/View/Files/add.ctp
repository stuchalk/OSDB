<h2>Upload a Spectrum</h2>
<?php
echo $this->Form->create('File', ['type' => 'file']);
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$this->Session->read('Auth.User.id')]);
echo $this->Form->input('substance_id', ['type' =>'input','size'=>60,'label'=>'Compound']);
echo $this->Form->input('file', ['type' =>'file','label'=>'File Upload']);
echo $this->Form->end('Add File');
?>