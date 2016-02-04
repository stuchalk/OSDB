<h2>Techniques</h2>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-primary">
            <div class="list-group">
                <?php
                foreach($data as $id=>$name) {
                    echo $this->Html->link($name,'/techniques/view/'.$id,['class'=>'list-group-item']);
                }
                ?>
            </div>
        </div>
    </div>
</div>