<script>
    $(document).ready(function() {
        $( "#SourceName" ).change(function() {
            var url = "<?php if($this->request->host()=="sds.coas.unf.edu") { echo "/osdb"; } ?>/sources/check/name";
            var e = $( "#SourceName" );
            var w = $( "#warn1" );
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

        $( "#SourceContact" ).change(function() {
            var url = "<?php if($this->request->host()=="sds.coas.unf.edu") { echo "/osdb"; } ?>/sources/check/contact";
            var e = $( "#SourceContact" );
            var w = $( "#warn2" );
            var resp;
            if( e.val().length > 3) {
                $.get(url + '/' + e.val(),function(data) {
                    if(data > 0) {
                        w.html("Contact already exists");
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
    <h2>Add a Source</h2>
<?php } ?>
<?php
echo $this->Form->create('Source',['role'=>'form','class'=>'form-horizontal','inputDefaults'=>['label'=>false,'div'=>false]]);
?>
<div class="form-group">
    <label for="SourceName" class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
        <?php echo $this->Form->input('name', ['type' =>'text','size'=>30,'placeholder'=>"Name"]); ?>
    </div>
</div>
<div id='warn1' style='display: inline;'></div>
<div class="form-group">
    <label for="SourceUrl" class="col-sm-2 control-label">Website</label>
    <div class="col-sm-10">
        <?php echo $this->Form->input('url', ['type' =>'text','size'=>50,'placeholder'=>"URL..."]); ?>
    </div>
</div>
<div class="form-group">
    <label for="SourceContact" class="col-sm-2 control-label">Contact</label>
    <div class="col-sm-10">
        <?php echo $this->Form->input('contact', ['type' =>'text','size'=>30,'placeholder'=>"Contact"]); ?>
    </div>
</div>
<div id='warn2' style='display: inline;'></div>
<div class="form-group">
    <label for="SourceEmail" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-10">
        <?php echo $this->Form->input('email', ['type' =>'text','size'=>30,'placeholder'=>"Email"]); ?>
    </div>
</div>
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10 pull-right">
        <?php echo $this->Form->end('Add Source',['class'=>'btn btn-default']); ?>
    </div>
</div>