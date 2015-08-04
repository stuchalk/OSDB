<h1>Add Property Type</h1>
<?php echo $this->Form->create('Propertytype', ['action' => 'add']); ?>

<?php
echo $this->Form->input('code',['type' => 'text', 'label' => 'Property Code']);
echo $this->Form->input('property_id',['type' => 'select','options' =>$properties, 'empty' => 'Select Property ID', 'label' => false]);
echo $this->Form->input('phases',['type' => 'select', 'multiple' => true, 'options' =>$phases, 'label' => 'Select Phase(s)' ]);
echo $this->Form->input('states',['type' => 'select', 'multiple' => true, 'options' =>$states, 'label' => 'Select State(s)' ]);
echo $this->Form->input('num_components', ['type' => 'text', 'label' => 'Number of Components']);
echo $this->Form->input('width', ['type' => 'text', 'label' => 'Width']);
echo $this->Form->input('height', ['type' => 'text', 'label' => 'Height']);

echo $this->Form->end('Add Property Type');
?>
