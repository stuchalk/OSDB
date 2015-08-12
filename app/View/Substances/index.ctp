<h2>Substances</h2>
<?php
$chars=[];
foreach($data as $char=>$iarray) { $chars[]=$char; }
echo "<p>Click on a letter below to show substances starting with that letter</p>";
echo "<p>";
foreach($chars as $char)
{
    echo "<div onclick=\"showletter('".$char."')\" style='display: inline;cursor: pointer;'>".$char."</div>&nbsp;&nbsp;";
}
echo "</p>";
$chars=array_keys($data);
foreach($data as $char=>$iarray)
{
    if($char==$chars[0]):	echo "<div id='".$char."' class='letter' style='display: block;'>";
    else:					echo "<div id='".$char."' class='letter' style='display: none;'>";
    endif;
    echo "<ul style='font-size: 12px;'>";
    foreach($iarray as $pid=>$substance)
    {
        echo '<li>'.html_entity_decode($this->Html->link($substance,'/substances/view/'.$pid)).'</li>';
    }
    echo "</ul></div>";
}
?>