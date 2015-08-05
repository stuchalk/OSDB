<?php
/**
 * Created by PhpStorm.
 * User: stu
 * Date: 1/13/15
 * Time: 5:30 PM
 */
?>

<h2>AnIML Files</h2>

<p>Please select a data file from below</p>
    <ul>
<?php

    foreach($files as $file) {
        echo '<i>'.$this->Html->link($file['title'],'/animl/files/view/'.$file['pid']).'</i><br/>';
        foreach($file['results'] as $r)
        {
            $url='https:///eureka.coas.unf.edu/animl/files/svg/'.$file['pid'].'/'.$r['expt'].'/'.$r['result'];
            echo $this->Html->image($url,['width'=>'200px','height'=>'150px']);
        }
    }
    //pr($files);
?>