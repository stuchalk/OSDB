<?php
/**
 * Created by PhpStorm.
 * User: stu
 * Date: 3/3/15
 * Time: 3:41 PM
 */

class RulesetsController extends AppController
{
    public $uses=['Ruleset','Rule','Property','rules_rulesets'];

    public $actionsNiceView=[ //Usable actions both here and in the form
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
        "INCREASEMULTIPLIER"];


    public function view($id)
    {
        $ruleset=$this->Ruleset->find('first',['conditions'=>['Ruleset.id'=>$id],'recursive'=>2]);

        $this->set('actions',$this->actionsNiceView); //load the actions the be useable during the view


        $newRules=array();
        //Correct rules to allow easy display
        foreach($ruleset['Rule'] as $rule){
            $i=$rule['RulesRuleset']['line'];
            if(!isset($newRules[$i])||!is_array($newRules[$i])){
                $newRules[$i]=ARRAY(); //make sure we have an array to iterate here
            }
            $newRules[$i][]=$rule;
        }
        $ruleset['NewRules']=$newRules;


      //  echo "<pre>";print_r($ruleset);echo "</pre>";
        $this->set('ruleset',$ruleset);
    }

    public function add()
    {
        if (!empty($this->data)) {

            //I start abusing cake php here
            $data=$this->data;
            //var_dump($data);
           // die();
            //set the version in code
            $data['Ruleset']['version']=1;

            //create and save the ruleset
            $this->Ruleset->create();
            if ($this->Ruleset->save($data)) {
                $i=0;

                //loop through the rules and add them to the join table properly
                while($i<count($data['rules_rulesets']['rule_id'])){

                    //more abuse begins here
                    $rules_rulesetsData=array();
                    $rules_rulesetsData['rules_rulesets']['rule_id']=$data['rules_rulesets']['rule_id'][$i]; //take all the rules, and assign their values to a new array so that we can save this in the join table
                    $rules_rulesetsData['rules_rulesets']['line']=$data['rules_rulesets']['line'][$i];
                    $rules_rulesetsData['rules_rulesets']['step']=$data['rules_rulesets']['step'][$i];
                    $rules_rulesetsData['rules_rulesets']['ruleset_id']=$this->Ruleset->id;
                    $this->rules_rulesets->create();
                    $this->rules_rulesets->save($rules_rulesetsData['rules_rulesets']);
                    $i++;
                }
                // Set a session flash message and redirect.
                $this->Session->setFlash('Ruleset '.$this->Ruleset->id.' Created!');
                return $this->redirect('/rulesets/view/'.$this->Ruleset->id);
            }
        } else {
            $rules=$this->Rule->find('list',['fields'=>['id','name']]);
            $this->set('rules',$rules);
            $properties=$this->Property->find('list',['fields'=>['id','name']]);
            $this->set('properties',$properties);
        }
    }
    public function update($id)
    {
        if (!empty($this->data)) {

            //I start abusing cake php here
            $data=$this->data;

            //set the version in code
            $data['Ruleset']['version']=1;

            //create and save the ruleset
            $this->Ruleset->id=$id;
            if ($this->Ruleset->save($data)) {

                $i=0;
                $this->rules_rulesets->deleteAll(['rules_rulesets.ruleset_id' => $id],false);
                //loop through the rules and add them to the join table properly
                while($i<count($data['rules_rulesets']['rule_id'])){

                    //more abuse begins here
                    $rules_rulesetsData=array();
                    $rules_rulesetsData['rules_rulesets']['rule_id']=$data['rules_rulesets']['rule_id'][$i]; //take all the rules, and assign their values to a new array so that we can save this in the join table
                    $rules_rulesetsData['rules_rulesets']['line']=$data['rules_rulesets']['line'][$i];
                    $rules_rulesetsData['rules_rulesets']['step']=$data['rules_rulesets']['step'][$i];
                    $rules_rulesetsData['rules_rulesets']['ruleset_id']=$this->Ruleset->id;
                    $this->rules_rulesets->create();
                    $this->rules_rulesets->save($rules_rulesetsData['rules_rulesets']);
                    $i++;
                }
                // Set a session flash message and redirect.
                $this->Session->setFlash('Ruleset '.$this->Ruleset->id.' Updated!');
                return $this->redirect('/rulesets/view/'.$this->Ruleset->id);
            }
        } else {
            $ruleset=$this->Ruleset->find('first',['conditions'=>['Ruleset.id'=>$id],'recursive'=>2]);
            $this->set('actions',$this->actionsNiceView); //load the actions the be useable during the view


            $newRules=array();
            //Correct rules to allow easy display
            foreach($ruleset['Rule'] as $rule){
                $i=$rule['RulesRuleset']['line'];
                if(!isset($newRules[$i])||!is_array($newRules[$i])){
                    $newRules[$i]=ARRAY(); //make sure we have an array to iterate here
                }
                $newRules[$i][]=$rule;
            }
            $ruleset['NewRules']=$newRules;

            $properties=$this->Property->find('list',['fields'=>['id','name']]);
            $this->set('properties',$properties);
            //  echo "<pre>";print_r($ruleset);echo "</pre>";
            $this->set('ruleset',$ruleset);
            $rules=$this->Rule->find('list',['fields'=>['id','name']]);
            $this->set('rules',$rules);
        }
    }
}