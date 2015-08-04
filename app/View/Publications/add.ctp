<h2>Add Publication</h2>
<?php
echo $this->Form->create('Publication',['action' => 'add']);
echo $this->Form->input('title',['type'=>'text','size'=>32,'maxlength'=>256]);
echo $this->Form->input('description',['type'=>'textarea','rows'=>4,'cols'=>80,'maxlength'=>1024]);
echo $this->Form->input('isbn',['type'=>'text','label'=>'ISBN','size'=>12,'maxlength'=>17]);
echo $this->Form->input('eisbn',['type'=>'text','label'=>'eISBN','size'=>12,'maxlength'=>17]);
echo $this->Form->input('total_files',['type'=>'text', 'label'=>'PDF Count in Set','size'=>7]);
echo $this->Form->input('url',['type'=>'text','label'=>'Webpage for Set','size'=>32,'maxlength'=>128]);
echo $this->Form->end('Add Publication');
?>