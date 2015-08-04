<?php
$substance=$data['Substance'];
$identifier=$data['Identifier'];
?>
<h2>Update Substance</h2>
<?php
    echo $this->Form->create(null,['url'=>'/Substances/update/'.$id]);
    echo $this->Form->input('name',
        ['type'=>'text','label'=>'Name','default'=>$substance['name']]);
    echo $this->Form->input('Identifier.type',
        ['type'=>'hidden','value'=>'inchi']);
    echo $this->Form->input('Identifier.value',
        ['type'=>'text','label'=>'InChI String','default'=>$identifier['0']['value']]);
    echo $this->Form->end('Update Substance');
?>