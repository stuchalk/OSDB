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

<h2>Add a Collection</h2>
<?php
echo $this->Form->create('Collection');
echo $this->Form->input('user_id', ['type' =>'hidden','value'=>$this->Session->read('Auth.User.id')]);
echo $this->Form->input('name', ['type' =>'text','size'=>40,'label'=>false,'div'=>false,'placeholder'=>"Name"]);
echo "<div id='warn' style='display: inline;'></div>";
echo $this->Form->input('description', ['type' =>'textarea','rows'=>4,'cols'=>60,'label'=>false,'div'=>false,'placeholder'=>"Description"]);
echo $this->Form->input('source', ['type' =>'text','size'=>40,'label'=>false,'div'=>false,'placeholder'=>"Source"]);
echo $this->Form->end('Add Collection');
?>