<?php $property=$data['Property']; ?>
    <h2>Update a Chemical Property</h2>
<?php
echo $this->Form->create('Property',['action'=>'update/'.$property['id']]);
echo $this->Form->input('name',['type'=>'text','size'=>30,'default'=>$property['name']]);
echo $this->Form->input('definition',['type'=>'textarea','cols'=>30,'rows'=>3,'default'=>$property['definition']]);
echo $this->Form->input('source',['type'=>'text','size'=>60,'default'=>$property['source']]);
echo $this->Form->end('Update Property');
?>