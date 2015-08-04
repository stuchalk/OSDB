<?php

$data = [0.0000,0.1204,0.2423,0.3671,0.4901];

echo "Data: ".implode(", ",$data)."<br />";
echo "Total: ".array_sum($data)."<br />";
echo "Minimum: ".min($data)."<br />";
echo "Maximum: ".max($data)."<br />";

$mean=array_sum($data)/count($data);
echo "Mean: ".$mean."<br />";

$sumsquares=0;
foreach($data as $point) {
    $sumsquares+=pow($point-$mean,2);
}
echo "Std. Dev: ".sqrt($sumsquares/(count($data)-1));
?>