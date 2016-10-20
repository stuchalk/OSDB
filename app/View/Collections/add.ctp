<script>
    $(document).ready(function() {
        $( "#CollectionName" ).change(function() {
            var url = "<?php if($this->request->host()=="sds.coas.unf.edu") { echo "/osdb"; } ?>/collections/check/name";
            var e = $( "#CollectionName" );
            var w = $( "#warn" );
            var resp;
            if( e.val().length > 3) {
                $.get(url + '/' + e.val(),function(data) {
                    if(data > 0) {
                        w.html("Name already exists");
                    } else {
                        w.html("");
                    }
                });
            } else {
                w.html("");
            }
        })
    });
</script>

<?php if(!$ajax) { ?>
    <h2>Add a Collection</h2>
<?php } ?>
<div class="row">
    <div class="col-sm-8">
        <?php
        echo $this->Form->create('Collection',['role'=>'form','class'=>'form-horizontal','inputDefaults'=>['label'=>false,'div'=>false]]);
        echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$this->Session->read('Auth.User.id')]);
        ?>
        <div class="form-group">
            <label for="CollectionName" class="col-sm-2 control-label">Name</label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('name', ['type' =>'text','size'=>30,'placeholder'=>"Name",'class'=>'form-control']); ?>
            </div>
        </div>
        <div id='warn1' style='display: inline;'></div>
        <div class="form-group">
            <label for="CollectionDescription" class="col-sm-2 control-label">Description</label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('description', ['type' =>'textarea','rows'=>4,'cols'=>60,'class'=>'form-control','placeholder'=>"Description..."]); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="SourceUrl" class="col-sm-2 control-label">Website</label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('url', ['type' =>'text','size'=>50,'class'=>'form-control','placeholder'=>"URL..."]); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="CollectionSource" class="col-sm-2 control-label">Contact</label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('source', ['type' =>'text','size'=>30,'class'=>'form-control','placeholder'=>"Who made available the collection..."]); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="CollectionCopyright" class="col-sm-2 control-label">Copyright</label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('copyright', ['type' =>'textarea','cols'=>30,'rows'=>3,'class'=>'form-control','placeholder'=>"Copyright information. If you are unsure about the legality of a collection you are intending to upload please contact schalk@unf.edu prior to initiation."]); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10 pull-right">
                <?php echo $this->Form->end('Add Collection',['class'=>'btn btn-default']); ?>
            </div>
        </div>
    </div>
</div>