<script>
    $(document).ready(function() {
        function log( message ) {
            $( "<div>" ).text( message ).prependTo( "#log" );
            $( "#log" ).scrollTop( 0 );
        }

        $( "#FileSubstance" ).autocomplete({
            source: "<?php echo Configure::read('url'); ?>/substances/search",
            minLength: 2,
            select: function( event, ui ) {
                log( ui.item ?
                "Selected: " + ui.item.value :
                "Nothing selected, input was " + this.value );
                $( "#FileSubstanceId" ).val(ui.item.id); // Sends id to hidden field
            }
        });

        $( "#AddSource" ).click(function(event) {
            var d = $( "#SourceDiv" );
            d.css( {top:event.pageY, left: event.pageX });
            d.fadeIn("slow");
        });

        $( "#FileSourceId").change(function() {
            //alert($('#FileSourceId').val());
            if( $('#FileSourceId').val()=='' ) {
                $('#CollectionUrl').prop('disabled',false);
            } else {
                $('#CollectionUrl').prop('disabled',true);
            }
        });

        $( "#SourceAddForm" ).submit(function(e) {
            var $inputs = $( "#SourceAddForm" ).find(":input" );
            var values= {};
            var url = "https://<?php echo Configure::read('server'); ?>";
            var d = $( "#SourceDiv" );
            var s = $( "#FileSourceId" );
            var n = {};
            $inputs.each(function() {
                values[this.name] = $(this).val();
            });
            $.post(url + $(this).attr('action'), values, function ( data ) {
                // Check for success
                if(data != "failure") {
                    // Clear Source form fields
                    $inputs.each(function() {
                        values[this.name]= $(this).val("");
                    });
                    d.modal('hide');        // Hide SourceDiv modal
                     // Add new source to dropdown
                    n = $.parseJSON(data);
                    s.append('<option value="' + n.id + '" selected="selected">' + n.name + '</option>');
                }
            });

            e.preventDefault();
        })
    });
</script>

<!-- Page -->
<h2>Upload a Spectrum</h2>
<h5 class="text-success">Currently we are only able to process JCAMP files with a single spectrum</h5>
<?php
if($this->Session->read('Auth.User')) {
    $uid=$this->Session->read('Auth.User.id');
} else {
    $uid="00002";
}
echo $this->Form->create('File',['action'=>'add','type'=>'file','role'=>'form','class'=>'form-horizontal','inputDefaults'=>['label'=>false,'div'=>false]]);
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$uid]);
echo $this->Form->input('substance_id', ['type' =>'hidden','value'=>'']);
?>
<div class="form-group form-group-lg">
    <label for="FileSubstance" class="col-sm-2 control-label">Compound</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('substance',['type' =>'text','size'=>60,'class'=>'form-control']); ?>
    </div>
</div>
<?php if($this->Session->read('Auth.User')) { ?>
<div class="form-group form-group-lg">
    <label for="FileSourceId" class="col-sm-2 control-label">Source</label>
    <div class="col-sm-3">
        <?php echo $this->Form->input('source_id',['type' =>'select','options'=>[''=>'Choose']+$srcs,'class'=>'form-control']); ?>
    </div>
    <div class="col-sm-1">
        <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#SourceDiv"><b>Add New Source</b></button>
    </div>
</div>
<?php } ?>
<div class="form-group form-group-lg hidden">
    <label for="CollectionUrl" class="col-sm-2 control-label">URL</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('Collection.url', ['type' =>'text','size'=>60,'class'=>'form-control','placeholder'=>'...or add a URL']); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="SourceFile" class="col-sm-2 control-label top">File Upload</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('file', ['type' =>'file','class'=>'btn btn-default']); ?>
    </div>
</div>
<div class="form-group form-group-lg">
    <div class="col-sm-offset-2 col-sm-6">
        <button type="submit" class="btn btn-default">Upload File</button>
    </div>
</div>

<!-- Popup for adding a source -->
<div class="modal fade" id="SourceDiv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Add a Source</h4>
            </div>
            <div class="modal-body">
                <?php echo $this->requestAction('/sources/add',['return']); ?>
            </div>
        </div>
    </div>
</div>