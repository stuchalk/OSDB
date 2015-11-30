<?php //pr($data);exit; ?>
    <h2>Collections</h2>
<?php
$cutoff=Configure::read('index.display.cutoff');
if($count>$cutoff) {
    echo $this->element('alpha_list', ['data' => $data, 'type' => 'collections']);
} else {
    echo $this->element('column_list', ['data' => $data, 'type' => 'collections']);
}
?>