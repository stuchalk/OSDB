<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Recent Submissions</h3>
    </div>
    <div class="list-group">
            <?php
            $data=$this->requestAction('/reports/recent');
            $index=0;
            foreach($data as $id=>$name) {
                echo $this->Html->link("• ".$name,'/reports/view/'.$id,['class'=>'list-group-item']);
            }
            ?>
    </div>
</div>