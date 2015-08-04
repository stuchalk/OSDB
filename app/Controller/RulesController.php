<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 4/11/2015
 * Time: 9:37 PM
*/

class RulesController extends AppController
{
    public $uses=['Rule'];

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
        "INCREASEMULTIPLIER"];


    public function view($id)
    {
        $rule=$this->Rule->find('first',['conditions'=>['Rule.id'=>$id]]); //get the first rule with id=$id
        $this->set('rule',$rule);
        $this->set('actions',$this->actions); //load the actions the be useable during the view
    }
    public function index()
    {
        $rule=$this->Rule->find('all');
        $this->set('actions',$this->actions); //load the actions the be useable during the view
        $this->set('rules',$rule);
    }

    public function add()
    {
        if (!empty($this->data)) {
            $this->Rule->create();
            if ($this->Rule->save($this->request->data)) {
                // Set a session flash message and redirect.
                $this->Session->setFlash('Rule '.$this->Rule->id.' Created!');
                return $this->redirect('/rules/view/'.$this->Rule->id);
            }

        }else{
            $this->set('actions',$this->actions);
        }
    }
    public function update($id)
    {
        if (!empty($this->data)) {
            $this->Rule->id=$id;
            if ($this->Rule->save($this->request->data)) {
                die('{"result":"success"}');
            }
        } else {
            $this->redirect('/rules/view/'.$id);
        }
    }
}