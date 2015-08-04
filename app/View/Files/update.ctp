<?php //pr($data);pr($pubs);
$file=$data['File']; ?>

<h2>Update File</h2>
<?php
    echo $this->Form->create(null, ['url'=>'/files/update/'.$id]);
    echo $this->Form->input('publication_id',
        ['type'=>'select','options'=>$pubs,'empty'=>'Select Publication','label'=>'Publication','value'=>$file['publication_id']]);
    echo $this->Form->input('total',
        ['type'=>'text','options'=>$file,'empty'=>'Total Systems','label'=>'Total Systems','default'=>$file['num_systems']]);
    echo $this->Form->end('Update file');
?>
<br>
<font color="red">
    <?php
    echo $this->Html->link("Delete File","/files/delete/".$file['id']);
    ?>
</font>
