<?php
    $matchMethod=array("preg_match","preg_match_all");
    $rulesNiceText=array(
        'name'=>  "Rule Name",
        'pattern'=> "Pattern",
        'action'=>"Action",
        'failure'=>"Failure",
        'valueName'=>"Storage Name",
        'errorText'=>"Error Text",
        'required'=>"Is Required?",
        'matchIndex'=> "Match index",
        'matchMethod'=> "Match Method",
        'headerIndex' =>"Header Index",
        'description' => "Description",
        'example' => "Example Line"

    );
    echo "<h1> Ruleset ".$ruleset['Ruleset']['name']."</h1><br>";
    echo $ruleset['Ruleset']['comment'];?>
    <br>
    <br>
RULES
<table>
    <?php
        foreach($ruleset['NewRules'] as $index=>$line){
            echo "<tr class=\"lineRow\"><td width='10' class='lineCollapser'>+</td><td> Line ".($index)." (".count($line);
            if(count($line)>1) {
                echo " rules";
            }else {
                echo " rule";
            }
            echo ")</td></tr>";
            foreach ($line as $ruleindex=>$rule){
                echo '<tr class="ruleRow" style="display: none;"><td width="10" class="ruleCollapser">+</td><td><div style="padding-left: 10px ;display: inline;">Rule '.($ruleindex+1).'- '.$this->Html->link($rule['name'],"/rules/view/".$rule['id']).'</div><br>';
                ?>
                <div class="ruleInfo" style="display: none;">
<?php
                foreach($rule as $index=>$entry){
                    if($entry!==null&&$entry!=""&&isset($rulesNiceText[$index])&&$index!="name") {
                        if($index=="failure"||$index=="action"){

                            $entry=$actions[$entry]; //changes value locally without changing it in the array
                        }
                        if($index=='matchMethod'){
                            $entry=$matchMethod[$entry]; //changes value locally without changing it in the array
                        }
                        if($index=='pattern'){
                            $entry="\"".$entry."\""; //changes value locally without changing it in the array
                        }
                        echo $rulesNiceText[$index] . ":  " . $entry . "<br>";
                    }else{
                        if($index=='matchMethod'){
                            echo $rulesNiceText[$index] . ":  " . $matchMethod[0] . "<br>";
                        }
                    }

                }

?>
                </div></td></tr>
    <?php
            }
        }
    ?>
</table>
<script type="application/javascript">
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
</script>