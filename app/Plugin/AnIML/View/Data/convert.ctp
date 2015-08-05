<?php
/**
 * Created by PhpStorm.
 * User: stu
 * Date: 1/13/15
 * Time: 5:30 PM
 */
?>

    <h2>AnIML Converter</h2>
    <p>Please select a data file for conversion</p>

<?php
    echo $this->Form->create('Animl.Data',['action'=>'convert','type'=>'file']);

    echo $this->Form->input('type',['type'=>'select','label'=>false,'options'=>[''=>'Select data file format...','jcamp'=>'JCAMP']]);

    $targets=array_keys($techs);$newTargets=[];
    foreach($targets as $target)
    {
        $newTargets[]="Data".ucfirst($target);
    }

    $script="multitogglevis('".implode(":",$newTargets)."','Data' + ucfirst(this.options[this.selectedIndex].value),'inline')";
    echo $this->Form->input('tech',['type'=>'select','label'=>false,'options'=>[''=>'Select technique...']+$techs,'onchange'=>$script]);

    foreach($techs as $type=>$tech)
    {
        echo $this->Form->input($type,['type'=>'select','label'=>false,'div'=>false,'style'=>'display: none;','options'=>[''=>'Select '.$type.' type...']+$$type]);
    }

    echo $this->Form->input('file',['type'=>'file','label'=>false]);
    echo $this->Form->end('Upload File');
?>