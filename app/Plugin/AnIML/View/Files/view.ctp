<?php
/**
 * Created by PhpStorm.
 * User: stu
 * Date: 1/13/15
 * Time: 5:30 PM
 */
?>

<h2>AnIML File</h2>

<h3>X Axis Start Value: //a:Series[@seriesID='wavelength1']/a:AutoIncrementedValueSet/a:StartValue/a:F"</h3>
<?php echo $data['xstart']; ?>
<h3>X Axis Increment: //a:Series[@seriesID='wavelength1']/a:AutoIncrementedValueSet/a:Increment/a:F"</h3>
<?php echo $data['inc']; ?>
<h3>Y Axis Data: //a:Series[@seriesID='absorbance1']/a:IndividualValueSet/a:F"</h3>
<?php for($x=0;$x<4;$x++) {
    echo "[".$x."] => ".$data['ydata'][$x]."<br />";
} ?>

<h3>Samples</h3>
<?php pr($samples); ?>

<h3>Data</h3>
<?php pr($results); ?>

</div>