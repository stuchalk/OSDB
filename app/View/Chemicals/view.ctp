<?php
//pr($data);exit;
$chemical=$data['Chemical'];
if(isset($chemical['name']))		{ echo "<h2>".$chemical['name']."</h2>"; }
if(isset($chemical['formula']))		{ echo "<h4>Chemical Formula: ".$chemical['formula']."</h4>"; }
if(isset($chemical['cas']))			{ echo "<h4>CAS Registry Number: ".$chemical['cas']."</h4>"; }
if(isset($chemical['inchistr']))	{ echo "<h4>InChI String: ".$chemical['inchistr']."</h4>"; }
if(isset($chemical['inchikey']))	{ echo "<h4>InChI Key: ".$chemical['inchikey']."</h4>"; }
if(isset($data['ridata']))
{
	echo "<h3>Refractive Index Data</h3>";
	echo "<table style='width: 400px;'>";
	echo '<tr><th style="width: 100px;">RI</th><th style="width: 100px;">Temp/K</th><th style="width: 100px;">Wavelength/nm</th><th style="width: 100px;">Book</th></tr>';
	$ris=$data['ridata'];
	$datasets=[];
	foreach($ris as $ri)
	{
		if(!isset($datasets[$ri['temperature']]))						{ $datasets[$ri['temperature']]=array(); }
		if(!isset($datasets[$ri['temperature']][$ri['wavelength']]))	{ $datasets[$ri['temperature']][$ri['wavelength']]=array(); }
		$datasets[$ri['temperature']][$ri['wavelength']][]=$ri['value'];
		echo "<tr><td>".$ri['value']."</td><td>".$ri['temperature']."</td><td>".$ri['wavelength']."</td><td>".$ri['book']."</td></tr>";
	}
	echo "</table>";

	echo "<h3>Refractive Index Datasets</h3>";
	echo "<table style='width: 600px;'>";
	echo "<tr><th style='width: 100px;'>Temp/K</th><th style='width: 100px;'>Wavelength/nm</th>";
	echo "<th style='width: 100px;'>Mean</th><th style='width: 100px;'>SD</th><th style='width: 100px;'>RSD(%)</th><th style='width: 100px;'>Count</th></tr>";
	//pr($datasets);
	foreach($datasets as $temp=>$waves)
	{
		foreach($waves as $wave=>$data)
		{
			$decs=9;
			$ss=0;
			$count=count($data);
			$mean=(array_sum($data)/$count);
			foreach($data as $datum)
			{
				list($m,$d)=explode(".",(string) $datum);
				if((strlen($d))<$decs) { $decs=strlen($d);}
				$ss+=pow($datum-$mean, 2);
			}
			if($count==1):	$sd=0;
			else:			$sd=sqrt($ss/($count-1));
			endif;
			$rsd=($sd*100)/$mean;
			echo "<tr><td>".$temp."</td><td>".$wave."</td><td>".number_format($mean,$decs)."</td><td>".number_format($sd,4)."</td><td>".number_format($rsd,3)."</td><td>".$count."</td></tr>";
		}
	}
	echo "</table>";
}
