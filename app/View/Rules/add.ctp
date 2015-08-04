<h1>Add a Ruleset</h1>
<?php
    echo $this->Form->create('Rule');
    echo $this->Form->input('name',["placeholder"=>"Name of the Rule"]);
    echo $this->Form->input('pattern',["placeholder"=>"Pattern used for regex evaluation"]);
    echo $this->Form->input('action',["options"=>$actions]);
    echo $this->Form->input('failure',["options"=>$actions,"empty"=>"No Failure"]);
    echo $this->Form->input('valueName',["placeholder"=>"Name of the value being stored","label"=>"Parameter Name"]);
    echo $this->Form->input('errorText',["placeholder"=>"Error to be displayed if value not found"]);
    echo $this->Form->input('required',["type"=>"checkbox"]);
    echo $this->Form->input('matchIndex',["placeholder"=>"index in array value will be found"]);
    echo $this->Form->input('matchMethod',["options"=>["preg_match","preg_match_all"],"empty"=>"No Match Method"]);
    echo $this->Form->input('headerIndex',["placeholder"=>"Header to use to store value"]);
    echo $this->Form->input('skip',["type"=>"text","placeholder"=>"How many lines/rules to move"]);
    echo $this->Form->input('description',["type"=>"text","placeholder"=>"Short Description of rule"]);
    echo $this->Form->input('example',["placeholder"=>"Line this rule would act on","label"=>"Example Line"]);
    echo $this->Form->end('Add Rule');
?>