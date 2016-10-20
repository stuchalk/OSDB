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

        $( "#AddCollection" ).click(function(event) {
            var d = $( "#ColDiv" );
            d.css( {top:event.pageY, left: event.pageX });
            d.fadeIn("slow");
        });

        $( "#CollectionAddForm" ).submit(function(e) {
            var $inputs = $( "#CollectionAddForm" ).find(":input" );
            var values= {};
            var url = "https://<?php echo Configure::read('server'); ?>";
            var d = $( "#ColDiv" );
            var s = $( "#CollectionId" );
            var n = {};
            $inputs.each(function() {
                values[this.name] = $(this).val();
            });
            $.post(url + $(this).attr('action'), values, function ( data ) {
                // Check for success
                if(data != "failure") {
                    // Clear Collection form fields
                    $inputs.each(function() {
                        values[this.name]= $(this).val("");
                    });
                    d.modal('hide');        // Hide ColDiv modal
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
<h2>Upload a Spectrum/Spectra</h2>
<h5 class="text-success">Currently we are only able to process JCAMP files with a single spectrum</h5>
<?php
if($this->Session->read('Auth.User')) {
    $uid=$this->Session->read('Auth.User.id');
} else {
    $uid="00002";
}
echo $this->Form->create('File',['url'=>['controller'=>'files','action'=>'add'],'id'=>'FilesAdd','type'=>'file','role'=>'form','class'=>'form-horizontal','inputDefaults'=>['label'=>false,'div'=>false]]);
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$uid]);
echo $this->Form->input('substance_id', ['type' =>'hidden','value'=>'']);
?>
<div class="form-group form-group-lg">
    <label for="FileSubstance" class="col-sm-3 control-label">Compound</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('substance',['type' =>'text','size'=>60,'class'=>'form-control']); ?>
    </div>
</div>
<?php if($this->Session->read('Auth.User')) { ?>
<div class="form-group form-group-lg">
    <label for="CollectionId" class="col-sm-3 control-label">Collection</label>
    <div class="col-sm-3">
        <?php echo $this->Form->input('Collection.id',['type' =>'select','options'=>[''=>'Choose']+$cols,'class'=>'form-control']); ?>
    </div>
    <div class="col-sm-3">
        <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#ColDiv"><b>Add New Collection</b></button>
    </div>
</div>
<div class="form-group form-group-lg">
    <label for="FileUrl" class="col-sm-3 control-label">URL</label>
    <div class="col-sm-6">
        <?php echo $this->Form->input('url', ['type' =>'text','size'=>60,'class'=>'form-control','placeholder'=>'Add original file URL if available...']); ?>
    </div>
</div>
<?php } ?>
<div class="form-group form-group-lg">
    <label for="SourceFile" class="col-sm-3 control-label top">File Upload</label>
    <div id="files" class="col-sm-5">
        <?php echo $this->Form->input('file.', ['type' =>'file','class'=>'btn btn-default btn-md upload','multiple']); ?>
    </div>
    <div class="col-sm-3">
        <button type="button" class="btn btn-warning btn-md" data-toggle="modal" onclick="$('.upload').first().clone().val(null).appendTo('#files').trigger('click'); return false;"><b>Add Another File</b></button>
    </div>
</div>
<div class="form-group form-group-lg">
    <div class="col-sm-offset-3 col-sm-6">
        <button type="submit" class="btn btn-default">Upload File</button>
    </div>
</div>
<?php echo $this->Form->end(); ?>

<!-- Popup for adding a source -->
<div class="modal fade" id="ColDiv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Add a Collection</h4>
            </div>
            <div class="modal-body">
                <?php echo $this->requestAction('/collections/add',['return']); ?>
            </div>
        </div>
    </div>
</div>