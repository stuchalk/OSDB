<?php //var_dump(PHP_OS); ?>

<h2>Add Text Files</h2>

<?php
echo $this->Form->create('TextFile',['type'=>'file']);
echo $this->Form->input('inputFile',
    ['options'=>$file,'empty'=>'Select File','label'=>'File']);
echo $this->Form->end('Convert File');