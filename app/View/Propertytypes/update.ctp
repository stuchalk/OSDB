<?php
$propertytype=$data['Propertytype'];
$ruleset=$data['Ruleset'];
$property=$data['Property'];
//pr($propertytype);
$p=explode(",",$propertytype['phases']);
$selphases=[];$fphases=array_flip($phases);
foreach($p as $phase) {
    $selphases[]=$fphases[ucfirst($phase)];
}
$s=explode(",",$propertytype['states']);
$selstates=[];$fstates=array_flip($states);
foreach($s as $state) {
    $selstates[]=$fstates[ucfirst($state)];
}
?>

<h1>Update Property Type</h1>
<?php echo $this->Form->create(null,['url'=>'/propertytypes/update/'.$id]); ?>

<?php
echo $this->Form->input('code',
    ['type'=>'text','label'=>'Property Code','default'=>$propertytype['code']]);
echo $this->Form->input('property_id',
    ['type'=>'select','options'=>$properties,'empty'=>'Select Property ID','label'=>false,'value'=>$propertytype['property_id']]);
echo $this->Form->input('phases',
    ['type'=>'select','multiple'=>true,'options'=>$phases,'label'=>'Select Phase(s)','selected'=>$selphases]);
echo $this->Form->input('states',
    ['type'=>'select','multiple'=>true,'options'=>$states,'label'=>'Select State(s)','selected'=>$selstates]);
echo $this->Form->input('num_components',
    ['type'=>'text','label'=>'Number of Components','default'=>$propertytype['num_components']]);
echo $this->Form->input('width',
    ['type'=>'text','label'=>'Width','default'=>$propertytype['width']]);
echo $this->Form->input('height',
    ['type'=>'text','label'=>'Height','default'=>$propertytype['height']]);

echo $this->Form->end('Update Property Type');
?>
