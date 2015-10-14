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

        $( "#SourceAddForm" ).submit(function(e) {
            var $inputs = $( "#SourceAddForm").find(":input" );
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
                    d.fadeOut();        // Hide SourceDiv
                     // Add new source to dropdown
                    n = $.parseJSON(data);
                    s.append('<option value="' + n.id + '" selected="selected">' + n.name + '</option>');
                }
            });

            e.preventDefault();
        })
    });
</script>

<h2>Upload a Spectrum</h2>
<?php
echo $this->Form->create('File', ['type' => 'file']);
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$this->Session->read('Auth.User.id')]);
echo $this->Form->input('substance', ['type' =>'text','col'=>60,'label'=>'Compound','div'=>['class'=>'ui-widget']]);
echo $this->Form->input('substance_id', ['type' =>'hidden','value'=>'']);
echo $this->Form->input('source_id', ['type' =>'select','options'=>[''=>'Choose']+$srcs,'label'=>"Source <span id='AddSource' style='cursor: pointer;'>+</span>"]);
echo $this->Form->input('file', ['type' =>'file','label'=>'File Upload']);
echo $this->Form->end('Add File');
echo "<div id='SourceDiv' class='float'>";
echo $this->requestAction('/sources/add',['return']);
echo "</div>";
?>