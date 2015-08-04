<?php $props=$data['ps'];$quans=$data['qs']; ?>
<h2>Add a Chemical Property</h2>
<?php
echo $this->Form->create();
echo $this->Form->input('name',['type'=>'text','size'=>30]);
echo $this->Form->input('definition',['type'=>'textarea','cols'=>30,'rows'=>3]);
echo $this->Form->input('source',['type'=>'text','size'=>60]);
// The script variable contains a javascript call to the 'list' function in jqcake.js to autopopulate the PropertyUnitId select list and show it
$script="if(this.options[this.selectedIndex].value!='') { list('PropertyUnitId','/units/index/'+this.options[this.selectedIndex].value+'/json');document.getElementById('PropertyUnitId').style.display='inline'; }";
echo $this->Form->input('quantity_id',['type'=>'select','label'=>'Quantity','onchange'=>$script,'div'=>false,'options'=>[''=>'Select Quantity']+$quans]);
echo $this->Form->input('unit_id',['type'=>'select','label'=>false,'style'=>'display: none;','options'=>[''=>'Select Unit...']]);
echo $this->Form->end('Add Property');
?>