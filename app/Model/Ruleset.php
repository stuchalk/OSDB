<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Ruleset
 * Ruleset model
 */
class Ruleset extends AppModel
{

    public $hasAndBelongsToMany = ['Rule'=>array('unique'=>"keepExisting",'Order'=>'rules_rulesets.line DESC')];

    public $hasMany=['Propertytype'];

    public $actions=[ //Usable actions both here and in the form
        "NEXTLINE",
        "USELAST",
        "USELASTLINE",
        "END",
        "SKIP",
        "STORE",
        "EXCEPTION",
        "STOREASHEADER",
        "USELASTLINEUNTIL",
        "STOREALL",
        "STOREALLASHEADER",
        "CONTINUE",
        "STOREALLASDATA",
        "NEXTRULE",
        "PREVIOUSLINE",
        "INCREASEMULTIPLIER"
    ];

    /**
     * function generateRulesetArray
     * @params array $query = an array returned by a find request for a ruleset
     * @return array $config = an array that can be easily inserted into the Reader to parse a text file
     */
    public function generateRulesetArray($query){
        $neededFields=array(
            "action",
            "failure",
            "pattern",
            "valueName",
            "errorText",
            "required",
            "matchIndex",
            "matchMethod",
            "headerIndex",
            "skip"
        );
        $newRules=array();
        $i=0;
        //Correct rules to mimic the original config format
        foreach($query['Rule'] as $rule){
            $i=$rule['RulesRuleset']['line'];
            if(!isset($newRules[$i])||!is_array($newRules[$i])){
                $newRules[$i]=ARRAY(); //make sure we have an array to iterate here
                $newRules[$i][]=0;
            }
            $newRules[$i][]=$rule;
        }
        //usort($newRules, "sortRules");
        //remove unneeded information
        foreach($newRules as &$line){
              unset($line[0]);
            foreach($line as &$rule){
                foreach($rule as $index=>&$field){
                    if($index=="action"||$index=="failure"){
                        if($field!==null)
                            $field=$this->actions[$field]; //make action and failure have the right text
                    }
                    if($index=="matchMethod"){
                        if($field==0)
                            $field="preg_match"; //fix matchMethod
                        else
                            $field="preg_match_all";
                    }
                    if(!in_array($index,$neededFields)||$field==''||$field==null){
                        unset($rule[$index]);
                    }
                }
            }
        }


        $config=array();
        $config['Rules']=$newRules;
        return $config;

    }
}
function sortRules($a,$b){
    if(is_array($a)&&is_array($b)&&isset($a['RulesRuleset'])&&isset($b['RulesRuleset'])&&$a['RulesRuleset']['step']>=$b['RulesRuleset']['step']) {
        if($a['RulesRuleset']['step']==$b['RulesRuleset']['step']){
            return 0;
        }else {
            return 1;
        }
    }else {
        return -1;
    }
}