<h2>Result summary for <?php echo $filename; ?></h2>
<h3>Statistics</h3>
<?php
	echo "<ul>";
	echo "<li>Lines processed: ".$stats['lines']."</li>";
	echo "<li>Chemicals identified: ".$stats['comps']."</li>";
	echo "<li>Data points extracted: ".$stats['data']."</li>";
	echo "</ul>";
	$jsonsize=filesize(WWW_ROOT.$jsonfile);
?>
<p>&nbsp;</p>
<h3>Chemicals</h3>
<ul>
<?php foreach($comps as $comp) { echo "<li>".$comp."</li>"; } ?>
</ul>
<p>&nbsp;</p>
<h3>Data in JSON Format</h3>
<p>Download the extracted data <?php echo $this->Html->link('here',"/".$jsonfile); ?> (<?php echo $jsonsize." bytes"; ?>)</p>