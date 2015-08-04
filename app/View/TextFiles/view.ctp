<div style="width: 100%">
    <br>
    <div style="width: 40%;display: inline-block;	vertical-align: text-top;">
        <pre id="rawText" style="overflow-x:scroll;overflow-y:scroll;height: 500px;"><?php echo htmlentities($textfile['TextFile']['text'])?></pre>
        <textarea id="text" style="display: none;height: 500px;">
            <?php echo $textfile['TextFile']['text']?>
        </textarea>
        <input type="button" value="EditText" id="toggleEdit">
        <input type="button" value="Save" id="saveEdit" style="display: none;">
        <input type="button" value="Show Data -->" id="toggleButton">
        <input type="button" value="Ingest File" id="ingest">
        <input type="button" value="Submit Github Issue" id="submitGithubIssue">
        <textarea id="githubBody" placeholder="Issue Body Text"></textarea>
    </div>
    <div style="width: 40%;display: inline-block;	vertical-align: text-top;">
        <object id="pdfFile" data="<?php echo $pdf; ?>" type="application/pdf" style="width: 100%;height: 500px;display: inline-block;">
            alt : <a href="<?php echo $pdf; ?>">test.pdf</a>
        </object>
        <pre style="display: none;border: none;box-shadow: none;overflow-x:scroll;overflow-y:scroll;height: 500px;" id="rawData"><?php var_dump(json_decode($textfile['TextFile']['extracted_data'],true));?></pre>
    </div>
</div>
<script type="application/javascript">
    $("body").on("click","#toggleButton",function(e){
        $("#pdfFile").toggle();
        $("#rawData").toggle();
        if($(this).val()=="Show Data -->") {
            $(this).val("Show PDF -->")
            $("pre").css("box-shadow","none");
        }else{
            $(this).val("Show Data -->")
        }
    });
    $("body").on("click","#toggleEdit",function(e){
        $("#rawText").toggle();
        $("#text").toggle();
        $("#saveEdit").toggle();
        if($(this).val()=="Cancel") {
            $(this).val("Edit");
        }else{
            $("#text").val($("#rawText").text());
            $("#text").css("width","100%");
            $(this).val("Cancel");
            $(this).css("width","50%");
            $("#saveEdit").css("width","49%");
        }
    });
    $("body").on("click","#submitGithubIssue",function(e){
        $('<div></div>').appendTo('body')
            .html('<div><h6>Are you sure you want to submit this issue?</h6></div>')
            .dialog({
                modal: true,
                title: 'Submit Issue',
                zIndex: 10000,
                autoOpen: true,
                width: 'auto',
                resizable: false,
                buttons: {
                    Yes: function () {
                        $.ajax({
                            type: 'POST',
                            url: '?submitGithubIssue',
                            data: "body="+$("#githubBody").val(),
                            success: function(data,textStatus,XHR){}
                        });

                        $(this).dialog("close");
                    },
                    No: function () {
                        $(this).dialog("close");
                    }
                },
                close: function (event, ui) {
                    $(this).remove();
                }
            });
    })
    $("body").on("click","#saveEdit",function(e){
        $('<div></div>').appendTo('body')
            .html('<div><h6>Are you sure you want to save this edit?</h6></div>')
            .dialog({
                modal: true,
                title: 'Save Edit',
                zIndex: 10000,
                autoOpen: true,
                width: 'auto',
                resizable: false,
                buttons: {
                    Yes: function () {
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '<?php echo $path."/textfiles/edit/".$textfile['TextFile']['id']; ?>',
                            data: "text="+ encodeURIComponent($("#text").val()),
                            success: function(data,textStatus,XHR){
                                if(data.result=="success")
                                    window.location.href='<?php echo $path."/textfiles/view/"?>'+data.id;
                                else
                                    alert(data.error);
                            }
                        });

                        $(this).dialog("close");
                    },
                    No: function () {
                        $(this).dialog("close");
                    }
                },
                close: function (event, ui) {
                    $(this).remove();
                }
            });
    })
    $("body").on("click","#ingest",function(e){
        window.location.href='<?php echo $path."/datarectification/ingest/".$textfile['TextFile']['file_id']; ?>';
    })

</script>