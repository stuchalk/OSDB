<div class="col-sm-3">
    <?php
    $data=$this->requestAction('/reports/latest');
    $pw=5;
    echo $this->element('molspectra',['index'=>0,'fontsize'=>12,'height'=>$pw*50,'named'=>true]+$data['jmol']);
?>
</div>
<div class="col-sm-6">
    <div class="text-center">
        <?php echo $this->requestAction('/spectra/plot/'.$data['flot']['id'].'/null/auto/300',['return']); ?>
    </div>
</div>