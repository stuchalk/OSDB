<?php
$data=$this->requestAction('/reports/latest');
echo $this->element('molspectra',['index'=>0,'height'=>200,'width'=>200,'grid'=>3,'label'=>'Latest Spectrum =>']+$data['jmol']);
?>

<div class="col-sm-6">
    <div class="text-center">
        <?php echo $this->requestAction('/spectra/plot/'.$data['flot']['id'].'/null/auto/300',['return']); ?>
    </div>
</div>