<?php //pr($files);exit; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Spectra</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-striped">
                    <tr>
                        <th class="col-md-3">Spectrum</th>
                        <th class="col-md-2">File</th>
                        <th class="col-md-2">Report</th>
                        <th class="col-md-2">Dataseries</th>
                        <th class="col-md-2">Data</th>
                        <th class="col-md-1">Actions</th>
                    </tr>
                    <?php
                        foreach($files as $file) {
                            $dset=$file['Dataset'];
                            $file=$file['File'];
                            $rept=$dset['Report'];
                            $dser=$dset['Dataseries'][0];
                            $dpnt=$dser['Datapoint'][0];
                            $x=$dpnt['Condition'][0];
                            $y=$dpnt['Data'][0];
                            ?>
                    <tr>
                        <td><?php echo $rept['title']; ?></td>
                        <td><?php echo $this->Html->link($file['id'],'/files/view/'.$file['id']); ?></td>
                        <td><?php echo $this->Html->link($rept['id'],'/reports/view/'.$rept['id']); ?></td>
                        <td><?php echo $this->Html->link($dser['id'],'/dataseries/view/'.$dser['id']); ?></td>
                        <td><?php
                            echo $this->Html->link('x-axis','/conditions/view/'.$x['id']).', ';
                            echo $this->Html->link('y-axis','/data/view/'.$y['id']);
                            ?>
                        </td>
                        <td><?php echo $this->Html->link('Delete','/reports/delete/'.$rept['id']); ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>

