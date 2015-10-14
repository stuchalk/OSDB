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

<h2>Add a Source</h2>
<?php
echo $this->Form->create('Source');
echo $this->Form->input('name', ['type' =>'text','size'=>40,'label'=>false,'div'=>false,'placeholder'=>"Name"]);
echo "<div id='warn1' style='display: inline;'></div>";
echo $this->Form->input('url', ['type' =>'text','size'=>60,'label'=>false,'div'=>false,'placeholder'=>"Website"]);
echo $this->Form->input('contact', ['type' =>'text','size'=>40,'label'=>false,'div'=>false,'placeholder'=>"Contact"]);
echo "<div id='warn2' style='display: inline;'></div>";
echo $this->Form->input('email', ['type' =>'text','size'=>40,'label'=>false,'div'=>false,'placeholder'=>"Email"]);
echo $this->Form->end(['value'=>'Add Source']);
?>