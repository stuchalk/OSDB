<h1>Add a Ruleset</h1>
<?php
    echo $this->Form->create('Ruleset');
?>
<?php
echo $this->Form->input('name',["placeholder"=>"Helpful name used a display for this ruleset"]);
echo $this->Form->input('comment',["placeholder"=>"Comment used to explain what this ruleset is used for"]);
echo $this->Form->input('property_id',['type'=>'select','options'=>$properties]);
?>
RULES<br>
<table id="rulesetTable">
    <tr class="lineRow"><td width='10' class='lineCollapser'>-</td><td> Line <div class="lineText" style="display: inline;">1</div></td><td class="lineRemove">REMOVE LINE</td></tr>
    <tr class="ruleRow"><td width='10' class='ruleCollapser'>-</td><td>Rule<div class="ruleText" style="display: inline"> 1</div>
<?php
    echo $this->Form->input('rules_rulesets.rule_id.',['type'=>'select','options'=>$rules,'label'=>false]);
    echo $this->Form->input('rules_rulesets.line.',['type'=>'hidden','label'=>false,"value"=>1]);
    echo $this->Form->input('rules_rulesets.step.',['type'=>'input','label'=>"step","value"=>1]);

?>
            </td><td class="ruleRemove">REMOVE RULE</td></tr><tr class="ruleAddRow"><td></td><td><input type="button" class="ruleAdd" value="Add New rule"></td></tr><tr><td colspan="2">
            <input type="button" id="lineAdd" value="Add New Line">
        </td></tr>
</table>
<?php

echo $this->Form->end('Add RuleSet');
?>
<script type="application/javascript">
    $(function () {
        //function called whenever the add line button is called
        $("#lineAdd").click(function(){
            //copy the previous line and change the line number

            var lineRow=$(this).parents("tr").prevAll(".lineRow:first").clone();
            var ruleRow=$(this).parents("table").find(".ruleRow:first").clone();
            var ruleRowAdd=$(this).parents("table").find(".ruleAddRow:first").clone();
            var num=Number(lineRow.find("div.lineText").html());
            ruleRow.find("div.ruleText").html(1);
            lineRow.find("div.lineText").html(num+1);
            ruleRow.find("input[type=hidden]").val(num+1);
            $(this).parents("tr").before(lineRow);
            $(this).parents("tr").before(ruleRow);
            $(this).parents("tr").before(ruleRowAdd);
        });
        $("body").on("click",".ruleAdd",function(){
            var value=$(this).parent().parent().prev().clone();
            var num=Number(value.find("div.ruleText").html());
            value.find("div.ruleText").html(num+1);
            $(this).parent().parent().prev().after(value);
        });
        $("body").on("click",".ruleRemove",function(){
            var row=$(this).parent();
            var mod=1;
            var rows=row.prevAll(".lineRow:last").nextUntil(".lineRow",".ruleRow")
            rows.each(function(i, item){
                var ruleText=$(item).find(".ruleText");
                if(ruleText.html()==row.find(".ruleText").html()){
                    mod=0
                }else {
                    ruleText.html(i + mod);
                }
            });
            row.remove();
        });
        $("body").on("click",".lineRemove",function() {
            var row=$(this).parent();
            var mod=1;
            var line=1;
            $(this).parent().nextUntil(".lineRow", ".ruleRow").remove();
            $(this).parent().nextUntil(".lineRow", ".ruleAddRow").remove();
            var rows=row.parent().find("tr");
            rows.each(function(i, item){
                var lineText=$(item).find(".lineText");
                var ruleText=$(item).find(".ruleText");
                if(lineText.length>0) {
                    if (lineText.html() == row.find(".lineText").html()) {
                        mod = 0
                    } else {
                        lineText.html(line);
                        line++;
                    }
                }else if(ruleText.length>0){
                    $(item).find("input[type=hidden]").val(line-1);
                }
            });
            row.remove();
        });
        $("body").on("click",".lineCollapser",function(e){
            rows=$(this).parent().nextUntil(".lineRow",".ruleRow").toggle();
            console.debug($(this).parent().nextUntil(".lineRow",".ruleRow"));
            if(rows.is(":visible") ) {
                $(this).html("-");
            }else{
                $(this).html("+");
            }
        })
        $("body").on("click",".ruleCollapser",function(e){
            rows=$(this).parent().find(".ruleInfo").toggle();
            if(rows.is(":visible") ) {
                $(this).html("-");
            }else{
                $(this).html("+");
            }
        })
    });

</script>