<?php
    //pr($pubs);
    //pr($files); pr($textfile);
    foreach($files as $pubid=>$pub)
    {
        foreach($pub as $fileid=>$title)
        {
            if(in_array($fileid,$textfile)) {
                unset($files[$pubid][$fileid]);
            }
        }
    }
    //pr($files);
?>
<h2>Files</h2>
<?php
    foreach($files as $pubid=>$pub)
    {
        echo "<h3>".$pubs[$pubid]."</h3>";
        echo "<ul>";
            foreach($pub as $fileid=>$title) {
                echo "<li>" . $this->Html->link($title,'/files/view/'.$fileid).' (';
                echo html_entity_decode($this->Html->link('Update','/files/update/'.$fileid)).') '.' (';
                echo $this->Html->link('Convert To Text File','/textfiles/add/'.$fileid).')</li>';
            }
        echo "</ul><br/>";
    }
?>
