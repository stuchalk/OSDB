<?php
// Jmol script element
// Variables - $color, $height, $width, $url (required), $uid
// $url is best done through proxy to avoid http security issues
if(!isset($color))  { $color=Configure::read('jmol.color'); }
if(!isset($height)) { $height=Configure::read('jmol.height'); }
if(!isset($width))  { $width=Configure::read('jmol.width'); }
$j2spath=Configure::read('jmol.j2spath');
$proxy=Configure::read('jmol.proxy');
//echo "URL: ".$url."<br />";
//echo "PROXY: ".$proxy;
?>

<script type='text/javascript'>
    <?php
    echo "var Info".$uid." = { color: '".$color."', height: ".$height.", width: ".$width.", src: '".$proxy.$url."', use: 'HTML5', j2sPath: '".$j2spath."' };\n";
    echo "Jmol.getTMApplet('chem".$uid."', Info".$uid.");\n";
    ?>
</script>