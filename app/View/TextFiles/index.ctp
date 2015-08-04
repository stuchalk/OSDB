<?php //pr($data);//pr($files);pr($pubs); ?>
    <h2>Text Files</h2>
<?php
foreach($pubs as $pid=>$pubtitle) {
    if(isset($files[$pid])) {
        echo "<h3>".$pubtitle."</h3>";
        foreach($files[$pid] as $fid=>$filename) {
            if(isset($data[$fid])) {
                echo "<ul>";
                foreach($data[$fid] as $vid=>$version) {
                    echo "<li>".$this->Html->link($filename, '/textfiles/view/' . $vid) .' (v'.$version.')'. ' (';
                    echo html_entity_decode($this->Html->link('Update', '/textfiles/update/' . $vid)) . ')</li>';
                }
                echo "</ul>";
            }
        }
    }
}
?>

<?php echo $this->Html->link("Add New Text File", ['action'=>'add']); ?>