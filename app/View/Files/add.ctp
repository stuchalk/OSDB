<script>
    $(function() {
        function log( message ) {
            $( "<div>" ).text( message ).prependTo( "#log" );
            $( "#log" ).scrollTop( 0 );
        }

        $( "#FileSubstance" ).autocomplete({
            source: "<?php if($this->request->host()=="sds.coas.unf.edu") { echo "/osdb"; } ?>/substances/search",
            minLength: 2,
            select: function( event, ui ) {
                log( ui.item ?
                "Selected: " + ui.item.value :
                "Nothing selected, input was " + this.value );
                $( "#FileSubstanceId" ).val(ui.item.id); // Sends id to hidden field
            }
        });
    });
</script>

<h2>Upload a Spectrum</h2>
<?php
echo $this->Form->create('File', ['type' => 'file']);
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$this->Session->read('Auth.User.id')]);
echo $this->Form->input('substance', ['type' =>'text','col'=>60,'label'=>'Compound','div'=>['class'=>'ui-widget']]);
echo $this->Form->input('substance_id', ['type' =>'hidden','value'=>'']);
echo $this->Form->input('file', ['type' =>'file','label'=>'File Upload']);
echo $this->Form->input('source_id', ['type' =>'select','options'=>$srcs]);
echo $this->Form->end('Add File');
?>