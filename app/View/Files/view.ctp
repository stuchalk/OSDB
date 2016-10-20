<?php pr($data);exit; ?>
<?php
$file=$data['File'];
?>

<h2>File</h2>
<?php
echo $this->Html->link("Convert File","/textfiles/add/".$file['id']);
?>
<ul>
    <li> <?php echo "File Name: ".$file['filename'];?> </li>
    <li> <?php echo "File Size: ".$file['filesize']; ?> </li>
    <li> <?php echo "PDF Version: ".$file['pdf_version']; ?> </li>
    <li> <?php echo "Total Systems: ".$file['num_systems']; ?> </li>
</ul>
