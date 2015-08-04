<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 1/15/2015
 * Time: 10:04 AM
 */

class Reader {

    //holds the config of what to look for in this file
    protected $config;

    //holds the current file pointer being read
    protected $file;

    //current line
    public $line=0;

    //current line in the config array
    public $ruleLine;

    //current position in the config array
    public $rulePosition=1;

    public $advancement;

    public $lineAdvancement;

    public $headers;

    public $anomalies=array();

    private $constants=array(
        "NEXTLINE"=>1,
        "USELAST"=>6,
        "USELASTLINE"=>7,
        "END"=>0,
        "SKIP"=>2,
        "STOREALLASHEADER"=>5,
        "STOREALL"=>4,
        "STORE"=>3,
        "EXCEPTION"=>8,
        "STOREASHEADER"=>9,
        "USELASTLINEUNTIL"=>10,

    );


    function __construct()
    {
        foreach($this->constants as $constant=>$value) {
            if (!defined($constant)) {
                define($constant, $value);
            }
        }


    }
    //sets the config for the file being read
   function SetConfig($config)
   {
        $this->config=$config;
    }

    //loads the file given into the file pointer or returns new exception
    function LoadFile($filename)
    {
        if(file_exists($filename)) {
            $this->file = fopen($filename, 'r');
            $this->line=0;
        }else
            throw new Exception($filename.": File Not Found");
    }


    function ReadFile($lineStart=0)
    {
        //set all of the things to
        $this->ruleLine=1;
        $results=array();
        $this->advancement=1;
        $this->lineAdvancement=1;
        $this->headers=array();
        /*
        if ($this->file) {
            while (($buffer = fgets($this->file, 4096)) !== false) {
                echo "<pre>".$buffer."</pre><br>";
            }
            if (!feof($this->file)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($this->file);
        }*/
        if($this->file){
            while (($buffer = fgets($this->file)) !== false) {


                $this->line++;
                if($this->line<$lineStart&&$lineStart!=0)
                    continue;

                // if the config says to skip blank lines, go to next line if blank
                if($buffer===""&&isset($this->config['skipblank'])&&$this->config['skipblank']==true){
                    continue;
                }
                if(!isset($this->config['Rules'][$this->ruleLine])) {
                    foreach($results as &$result){
                        if(is_array($result)&&count($result)==1){
                            $result=$result[0];
                        }
                    }
                    return $results;
                }
                $this->rulePosition=1;
                while($this->rulePosition<count($this->config['Rules'][$this->ruleLine])+1) {
                    $matches = array();
                    $count=0;
                    $didMatch=false;
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'])) {

                        //if specified in this rule to use a specific match method use those, otherwise assume match all
                        if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchMethod'])) {
                            $matchMethod = "preg_match_all";
                        }else{
                            $matchMethod= $this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchMethod'];
                        }
                        switch ($matchMethod) {

                            case "preg_match":
                                $didMatch = preg_match("!" . $this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'] . "!", $buffer, $matches);
                                break;
                            case "preg_match_all":
                            default:
                                $didMatch = preg_match_all("!" . $this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'] . "!", $buffer, $matches);
                                break;

                        }
                        //if we matched anything then handle that, else handle the failure
                        if ($didMatch) {
                            foreach($matches as &$match1){
                                if(is_array($match1)){
                                    foreach($match1 as &$match2){
                                        $match2=trim($match2);
                                    }
                                }else{
                                    $match1=trim($match1);
                                }
                            }
                            //method of calling a variable method name
                            $count=$this->{"handle_".$matchMethod}($buffer, $results, $matches);
                            //allow the function to issue continues and end if needed
                            switch ($count){
                                case "END":
                                    return $results;
                                case 2:
                                    continue 3;
                                case 1:
                                    continue 2;
                                default:
                                    break;
                            }
                        } else {
                            $count=$this->handleFailure($buffer,$results);
                            //allow the function to issue continues and end if needed
                            switch ($count){
                                case "END":
                                    return $results;
                                case 2:
                                    continue 3;
                                case 1:
                                    continue 2;
                                default:
                                    break;
                            }
                        }
                        $this->rulePosition += $this->advancement;
                        $this->advancement = 1;
                    } else if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION'])) {
                        switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION']) {
                            case SKIP:
                                break;
                            case NEXTLINE:
                                continue 3;
                                break;
                            case USELAST:
                                $this->rulePosition--;
                                $this->advancement++;
                                continue 2;
                                break;
                            case USELASTLINE:
                                $this->rulePosition = 0;
                                $this->ruleLine--;
                                $this->lineAdvancement++;
                                continue 2;
                                break;
                            case END:
                                foreach ($results as &$result) {
                                    if (is_array($result) && count($result) == 1) {
                                        $result = $result[0];
                                    }
                                }
                                return $results;
                                break;

                        }
                    }
                }
                $this->ruleLine+=$this->lineAdvancement;
                $this->lineAdvancement=1;
            }
            if (!feof($this->file)) {
                throw new Exception("Error reading file at ".$this->line);
            }
            if(isset($results))
                return $results;
            else
                return false;
        }else{
            throw new Exception("File has not been set yet");
        }

    }
    function __destruct()
    {
        fclose($this->file);

    }

    //Handles any failure to find the correct
    function handleFailure($buffer,&$results)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
            var_dump($this->headers);
            var_dump($this->config['Rules'][$this->ruleLine][$this->rulePosition]);
            echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
        }
        //we failed to find what we wanted, how do we handle this
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE'])) {
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE']) {
                case STORE:
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename'])) {
                        $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] = null;
                    }else{
                        $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = null;
                    }
                    if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                        $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition ."<br> String:<pre>".$buffer."</pre><br><br>";
                case SKIP:
                    break ;
                case NEXTLINE:
                    return 2;
                    break ;
                case USELAST:
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case USELASTLINE:
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case EXCEPTION:
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['error'])) {
                        throw new Exception($this->config['Rules'][$this->ruleLine][$this->rulePosition]['error']."; Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition);
                    }
                    break ;
                case END:
                    foreach ($results as &$result) {
                        if (is_array($result) && count($result) == 1) {
                            $result = $result[0];
                        }
                    }
                    return "END";
                    break ;
                default:
                    throw new Exception("Pattern Not Found and Failure Not Listed; Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition);
                    break;
            }
        }
    }
    function handle_preg_match_all($buffer,&$results,$matches)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION'])) {
            if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
                var_dump($matches);
                var_dump($this->headers);
                var_dump($this->config['Rules'][$this->ruleLine][$this->rulePosition]);
                echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
            }
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION']) {
                case STORE:
                    //if a name is given use that name
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename'])) {
                        //if an index is given use that index
                        if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check to see if a certain index was requested
                            if (!isset($matches[0][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1])) {
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE']!=STORE||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                        $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                    return $this->handleFailure($buffer,$results);
                                break;
                            }
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] = $matches[0][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1];
                        }else {
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] = $matches[0][0];
                        }
                        //if a header index is given use that
                    }elseif(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])){
                        //check to see if a certain index was requested
                        if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check if there is something stored there
                            if(!isset($matches[0][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1])){
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE']!=STORE||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                    $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                return $this->handleFailure($buffer,$results);
                            }
                            if (!isset($this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1])) {
                                //We have more data than headers, this is a problem
                                throw new Exception("Header Not Found ; Line " . $this->line . " <br>Rule Line " . $this->ruleLine . " <br>Rule Position " . $this->rulePosition);
                            }
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[0][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1];

                        }else {
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[0][0];
                        }
                    }
                    BREAK;
                case STOREALL:
                    foreach($matches[0] as $match){
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename'])) {
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] =$match;
                        }elseif(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])){
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][]=$match;
                        }
                    }
                    break;
                case STOREASHEADER:
                    //store this information as a header
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']))
                        $this->headers[] = $matches[0][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1];
                    else
                        $this->headers[] = $matches[0][0];
                    BREAK;
                case STOREALLASHEADER:
                    foreach($matches[0] as $match){
                        $this->headers[]=$match;
                    }
                    BREAK;
                case SKIP:
                    break;
                case NEXTLINE:
                    //go to the next line
                    return 2;
                    break;
                case USELAST:
                    //use the last rule for this position as well
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case USELASTLINE:
                    //go back 1 line in the config and advance 2 lines afterwords
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case USELASTLINEUNTIL:
                    //go back 1 line in the config and use that until errors
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    return 1;
                case END:
                    //if there is any array that only has a single value, then make it that value rather than an array
                    foreach ($results as &$result) {
                        if (is_array($result) && count($result) == 1) {
                            $result = $result[0];
                        }
                    }
                    return "END";
                    break;

            }
        }
    }
    function handle_preg_match($buffer,&$results,$matches)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION'])) {
            if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
                var_dump($matches);
                var_dump($this->headers);
                var_dump($this->config['Rules'][$this->ruleLine][$this->rulePosition]);
                echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
            }
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['ACTION']) {
                case STORE:
                    //if a name is given use that name
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename'])) {
                        //if an index is given use that index
                        if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check to see if a certain index was requested
                            if (!isset($matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']])) {
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE']!=STORE||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                    $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                return $this->handleFailure($buffer,$results);
                                break;
                            }
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];
                        }else {
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valuename']][] = $matches[0];
                        }
                        //if a header index is given use that
                    }elseif(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])){
                        //check to see if a certain index was requested
                        if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check if there is something stored there
                            if(!isset($matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']])){
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['FAILURE']!=STORE||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                        $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                    return $this->handleFailure($buffer,$results);
                            }
                            if (!isset($this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1])) {
                                //We have more data than headers, this is a problem
                                throw new Exception("Header Not Found ; Line " . $this->line . " <br>Rule Line " . $this->ruleLine . " <br>Rule Position " . $this->rulePosition);
                            }
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];

                        }else {
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[0];
                        }
                    }
                    BREAK;
                case STOREASHEADER:
                    //store this information as a header
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']))
                        $this->headers[] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];
                    else
                        $this->headers[] = $matches[0];
                    BREAK;
                case SKIP:
                    break;
                case NEXTLINE:
                    //go to the next line
                    break;
                case USELAST:
                    //use the last rule for this position as well
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case USELASTLINE:
                    //go back 1 line in the config and advance 2 lines afterwords
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case USELASTLINEUNTIL:
                    //go back 1 line in the config and use that until errors
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    return 1;
                case END:
                    //if there is any array that only has a single value, then make it that value rather than an array
                    foreach ($results as &$result) {
                        if (is_array($result) && count($result) == 1) {
                            $result = $result[0];
                        }
                    }
                    return "END";
                    break;

            }
        }
    }

}