<h1>View a Rule</h1>

<?php
    echo $this->Form->create('Rule',array('action' => 'update/'.$rule['Rule']['id'],'id'=>'RuleViewForm'));
    echo "ID:".$rule['Rule']['id'];
    echo $this->Form->input('name',["value"=>$rule['Rule']['name'],"placeholder"=>"Name of the Rule"]);
    echo $this->Form->input('pattern',["value"=>$rule['Rule']['pattern'],"placeholder"=>"Pattern used for regex evaluation"]);
    echo $this->Form->input('action',["options"=>$actions,"selected"=>$rule['Rule']['action']]);
    echo $this->Form->input('failure',["options"=>$actions,"selected"=>$rule['Rule']['failure'],"empty"=>"No Failure"]);
    echo $this->Form->input('valueName',["value"=>$rule['Rule']['valueName'],"placeholder"=>"Name of the value being stored","label"=>"Parameter Name"]);
    echo $this->Form->input('errorText',["value"=>$rule['Rule']['errorText'],"placeholder"=>"Error to be displayed if value not found"]);
    echo $this->Form->input('required',["type"=>"checkbox","checked"=>$rule['Rule']['required']]);
    echo $this->Form->input('matchIndex',["value"=>$rule['Rule']['matchIndex'],"placeholder"=>"index in array value will be found"]);
    echo $this->Form->input('matchMethod',["options"=>["preg_match","preg_match_all"],"selected"=>$rule['Rule']['matchMethod'],"empty"=>"No Match Method"]);
    echo $this->Form->input('headerIndex',["value"=>$rule['Rule']['headerIndex'],"placeholder"=>"Header to use to store value"]);
    echo $this->Form->input('skip',["value"=>$rule['Rule']['skip'],"type"=>"text","placeholder"=>"How many lines/rules to move"]);
    echo $this->Form->input('description',["type"=>"text","value"=>$rule['Rule']['description'],"placeholder"=>"Short Description of rule"]);
    echo $this->Form->input('example',["value"=>$rule['Rule']['example'],"placeholder"=>"Line this rule would act on","label"=>"Example Line"]);
    echo $this->Form->end('Update Rule');
?>
<script type="application/javascript">
    $('#RuleViewForm').on('submit',function(e){
        e.preventDefault();
        var dataArray=$(this).serialize();
        $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            data: dataArray,
            async: false,
            success: function(data,textStatus,XHR){
                returnValue = $.parseJSON(data);
                $('#RuleViewForm').before('<div id="flashMessage" class="message">Rule Has Been Updated </div>');
            }
        });
        return false;
    });
</script>