<?php
$data=$this->requestAction('/reports/latest');
echo $this->element('molspectra',['index'=>0,'height'=>200,'width'=>200,'grid'=>3,'label'=>'Latest Spectrum']+$data['jmol']);
?>

<div class="col-sm-6">
    <div class=" pull-right">
    <?php echo $this->requestAction('/spectra/plot/'.$data['flot']['id'].'/450/300',['return']); ?>
    </div>
</div>