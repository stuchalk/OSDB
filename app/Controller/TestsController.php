<?php

/**
 * Class TestsController
 * Dr. Chalks play area
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class TestsController extends AppController
{

    public $uses = ['Propertytype'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Process a file
     */
    public function index()
    {
        $text = file('files/text/11005155_7.txt');
        $data=[];

        $i=0;
        // Detect the Property (Rule)
        if(stristr($text[$i],"Property Type: [")) {
            preg_match("/\[([A-Z]{4}[0-9]{4})\]/",$text[$i], $matches);
            // $matches has [1] => property code
            $data['code']=$matches[1];
            // Search propertyTypes table with code for id and add details to the data array to use later
            $data['propertyType']['parameters']=["pressure"=>"P/103Pa","temperature"=>"T/K"]; // Surrogate
            $i++;
        }

        // Find the line the File Number
        while(!stristr($text[$i],"File Number:")) {
            $i++;
        }

        // Get the file number
        preg_match("/LB[0-9]{4}$/",$text[$i], $matches);
        $data['fileNumber']=$matches[0];
        $i++;

        // Find the start of the components
        while(!preg_match("/\[[0-9]{2,5}-[0-9]{2}-[0-9]{1}\]$/",$text[$i])) {
            $i++;
        }

        // Find all of the components
        while(preg_match("/\[[0-9]{2,5}-[0-9]{2}-[0-9]{1}\]$/",$text[$i],$matches)) {
            $data['substances'][]=$matches[0];
            $i++;
        }

        // Data table
        $data['table']=array();

        // Find the start of the tables
        while(empty($text[$i])) {
            $i++;
        }

        // Find the values of the parameters
        foreach($data['propertyType']['parameters'] as $param=>$string)
        {
            if(stristr($string,$text[$i]))
            {
                preg_match("/ = [0-9]+\.[0-9]+$/",$text[$i], $matches);
                echo "<pre>";print_r($matches);echo "</pre>";
                $data['table'][0]['parameters'][$param]=$matches[0];
            }
        }



        // echo "<pre>";print_r($matches);echo "</pre>";
        // preg_match("/\[([0-9]{2,5}-[0-9]{2}-[0-9]{1})\]$/",$text[$i], $matches);


        $this->set('text',$text);
        $this->set('data',$data);
    }

}