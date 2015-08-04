<?php

/**
 * Created by PhpStorm.
 * User: John Turner
 * Date: 1/15/2015
 * Time: 10:04 AM
 *
 * Updated for CakePHP  - Stuart Chalk 021615
 */
class Reader {

    // Holds the config of what to look for in this file
    protected $config;

    // Holds the current file pointer being read
    protected $file;

    // Current line
    public $line=0;

    // Current line in the config array
    public $ruleLine;

    // Current position in the config array
    public $rulePosition=1;

    public $advancement;

    public $lineAdvancement;

    public $headers;

    public $anomalies=array();

    public $indexStart=0;

    public $currentColumns=0;

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

    /**
     * Constructor for new class instance
     */
    function __construct()
    {
        foreach($this->constants as $constant=>$value) {
            if (!defined($constant)) {
                define($constant, $value);
            }
        }
    }

    /**
     * Sets the config for the file being read
     * @param $config
     */
    function SetConfig($config)
    {
        $this->config=$config;
    }

    /**
     * Loads the file given into the file pointer or returns new exception
     * @param $filename
     * @throws Exception
     */
    function LoadFile($filename)
    {
        if (file_exists($filename))
        {
            $this->file = fopen($filename, 'r');
            $this->line=0;
        } else {
            throw new Exception($filename.": File Not Found");
        }
    }
    /**
     * Loads the file given into the file pointer or returns new exception
     * @param $filename
     * @throws Exception
     */
    function setStream($stream)
    {
        if ($stream)
        {
            $this->file = $stream;
            $this->line=0;
        } else {
            throw new Exception(" Stream invalid");
        }
    }
    /**
     * Loads the file given into $str and then uses the replacement array to fix special characters
     * @param $filename
     * @param $replacementArray
     * @returns string $text
     * @throws Exception
     */
    function FixCharacters($text,$replacementArray)
    {
        foreach($replacementArray as $index=>$replacement){
            $text=str_replace($index,$replacement,$text); //replace each index with the replacement
        }
        return $text;
    }

    /**
     * Read a line in the file
     * @param int $lineStart
     * @return array|bool
     * @throws Exception
     */
    function ReadFile($lineStart=0)
    {
        // Set all of the things to defaults
        $this->ruleLine=1;
        $results=array();
        $this->advancement=1;
        $this->lineAdvancement=1;
        $this->headers=array();
        $this->indexStart=0;
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
        if ($this->file) {
            while (($buffer = fgets($this->file)) !== false)
            {
                //echo $buffer."<br />";
                $this->line++;
                if ($this->line<$lineStart&&$lineStart!=0) {
                    continue;
                }
                // If the config says to skip blank lines, go to next line if blank
                if ($buffer===""&&isset($this->config['skipblank'])&&$this->config['skipblank']==true)
                {
                    continue;
                }
                if (!isset($this->config['Rules'][$this->ruleLine]))
                {
                    foreach($results as $key=>&$result)
                    {
                        if(strpos($key,"Parameter")===false&&strpos($key,"Data")===false) {
                            if (is_array($result) && count($result) == 1) {
                                $result = $result[0];
                            }
                        }
                    }
                    return $results;
                }
                $this->rulePosition=1;
                while($this->rulePosition<count($this->config['Rules'][$this->ruleLine])+1)
                {
                    $matches = array();
                    $count=0;
                    $didMatch=false;
                    //Echo "Line: ".$this->line." Line Rule: ".$this->ruleLine."  Line Position: ".$this->rulePosition." Multi: ".$this->indexStart."  Input: ".$buffer."<br>";
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'])) {
                        // If specified in this rule to use a specific match method use those, otherwise assume match all
                        if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchMethod'])) {
                            $matchMethod = "preg_match_all";
                        } else {
                            $matchMethod= $this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchMethod'];
                        };
                        switch ($matchMethod) {
                            case "preg_match":
                                $didMatch = preg_match("!" . $this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'] . "!", $buffer, $matches);
                                break;
                            case "preg_match_all":
                            default:
                                $didMatch = preg_match_all("!" . $this->config['Rules'][$this->ruleLine][$this->rulePosition]['pattern'] . "!", $buffer, $matches);
                                break;
                        }

                        // If we matched anything then handle that, else handle the failure
                        if ($didMatch) {
                            // Method of calling a variable method name
                            $count=$this->{"handle_".$matchMethod}($buffer, $results, $matches);
                            // Allow the function to issue continues and end if needed
                            switch ($count) {
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
                    } else if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action'])) {
                        switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action']) {
                            case "SKIP":
                                $this->rulePosition += $this->advancement;
                                $this->advancement = 1;
                                break;
                            case "NEXTLINE":
                                $this->rulePosition += $this->advancement +1;
                                $this->advancement = 1;
                                continue 3;
                            case "CONTINUE":
                                $this->rulePosition--;
                                break;
                            case "USELAST":
                                $this->rulePosition--;
                                $this->advancement++;
                                continue 2;
                                break;
                            case "USELASTLINE":
                                $this->rulePosition = 0;
                                $this->ruleLine--;
                                $this->lineAdvancement++;
                                continue 2;
                                break;
                            case "NEXTRULE":
                                $newPosition=$this->rulePosition;
                                $newLine=$this->ruleLine;
                                //if we want to skip a specific number of rules, calculate where that puts us
                                if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                                    $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip']=1;
                                }
                                if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                                    $newPosition += $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                                    $ready = false;
                                    $tick = 0;
                                    while (!$ready) {
                                        $tick++; //tick to make sure we donot get stuck in infinite loop
                                        if (isset($this->config['Rules'][$newLine])) {
                                            if ($newPosition > count($this->config['Rules'][$newLine])) {
                                                $newPosition=$newPosition-count($this->config['Rules'][$newLine]);
                                                $newLine++;
                                            }elseif(isset($this->config['Rules'][$newLine][$newPosition])){
                                                $ready=true;
                                                if($newPosition>=1){
                                                    $newPosition--;
                                                }
                                            }
                                        }

                                        if($tick>100){
                                            return "END";
                                        }
                                    }
                                    $this->rulePosition=$newPosition;
                                    $this->ruleLine=$newLine;
                                }
                                break;
                            case "PREVIOUSLINE":
                                if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])){
                                    $this->ruleLine-=$this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                                }else {
                                    $this->ruleLine -= 1;
                                }
                                if(!isset($this->config['Rules'][$this->ruleLine])){
                                    $this->ruleLine=1;
                                }
                                $this->rulePosition=1;
                                break;
                            case "END":
                                foreach ($results as $key=>&$result) {
                                    if(strpos($key,"Parameter")===false&&strpos($key,"Data")===false) {
                                        if (is_array($result) && count($result) == 1) {
                                            $result = $result[0];
                                        }
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

            if (isset($results)) {
                foreach ($results as $key=>&$result) {
                    if(strpos($key,"Parameter")===false&&strpos($key,"Data")===false) {
                        if (is_array($result) && count($result) == 1) {
                            $result = $result[0];
                        }
                    }
                }
                return $results;
            } else {
                return false;
            }
        } else {
            throw new Exception("File has not been set yet");
        }
    }

    /**
     * Destructor
     */
    function __destruct()
    {
        fclose($this->file);
    }

    /**
     * Handles any failure to find the correct
     * @param $buffer
     * @param $results
     * @return int|string
     * @throws Exception
     */
    function handleFailure($buffer,&$results)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
            var_dump($this->headers);
            var_dump($this->config['Rules'][$this->ruleLine][$this->rulePosition]);
            echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
        }
        // We failed to find what we wanted, how do we handle this
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure'])) {
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure']) {
                case "STORE":
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName'])) {
                        $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] = null;
                    } else {
                        $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = null;
                    }
                    if (!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                        $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition ."<br> String:<pre>".$buffer."</pre><br><br>";
                    break;
                case "SKIP":
                    break;
                case "NEXTLINE":
                    return 2;
                    break;
                case "USELAST":
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case "USELASTLINE":
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case "NEXTRULE":
                    $newPosition=$this->rulePosition;
                    $newLine=$this->ruleLine;
                    //if we want to skip a specific number of rules, calculate where that puts us
                    if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip']=1;
                    }
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $newPosition += $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                        $ready = false;
                        $tick = 0;
                        while (!$ready) {
                            $tick++; //tick to make sure we donot get stuck in infinite loop
                            if (isset($this->config['Rules'][$newLine])) {
                                if ($newPosition > count($this->config['Rules'][$newLine])) {
                                    $newPosition=$newPosition-count($this->config['Rules'][$newLine]);
                                    $newLine++;
                                }elseif(isset($this->config['Rules'][$newLine][$newPosition])){
                                    $ready=true;
                                    if($newPosition>=1){
                                        $newPosition--;
                                    }
                                }
                            }

                            if($tick>100){
                                return "END";
                            }
                        }
                        $this->rulePosition=$newPosition;
                        $this->ruleLine=$newLine;
                    }
                    break;
                case "PREVIOUSLINE":

                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])){
                        $this->ruleLine-=$this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                    }else {
                        $this->ruleLine -= 1;
                    }
                    if(!isset($this->config['Rules'][$this->ruleLine])){
                        $this->ruleLine=1;
                    }
                    $this->rulePosition=1;
                    break;
                case "CONTINUE":
                    $this->advancement=0;
                    return 2;
                    break;
                case "EXCEPTION":
                    echo "EXCEPTION";
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['error'])) {
                        throw new Exception($this->config['Rules'][$this->ruleLine][$this->rulePosition]['error']."; Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition);
                    }
                    break;
                case "END":
                    foreach ($results as &$result) {
                        if (is_array($result) && count($result) == 1) {
                            $result = $result[0];
                        }
                    }
                    return "END";
                    break;
                default:
                    throw new Exception("Pattern Not Found and Failure Not Listed; Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition);
                    break;
            }
        }
    }

    /**
     * Process preg_match_all
     * @param $buffer
     * @param $results
     * @param $matches
     * @return int|string
     * @throws Exception
     */
    function handle_preg_match_all($buffer,&$results,$matches)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action'])) {
            if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
                var_dump($matches);
                var_dump($this->headers);
                var_dump($this->config['Rules'][$this->ruleLine][$this->rulePosition]);
                echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
            }
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action']) {
                case "STORE":
                    //if a name is given use that name
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName'])) {
                        //if an index is given use that index
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check to see if a certain index was requested
                            if (!isset($matches[1][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1])) {
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure']!="STORE"||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                        $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                    return $this->handleFailure($buffer,$results);
                                break;
                            }
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] = $matches[1][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1];
                        } else {
                            //store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] = $matches[1][0];
                        }
                        //if a header index is given use that
                    } elseif (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])) {
                        //check to see if a certain index was requested
                        if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check if there is something stored there
                            if(!isset($matches[1][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1])){
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure']!="STORE"||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])
                                    $this->anomalies[]="Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
                                return $this->handleFailure($buffer,$results);
                            }
                            if (!isset($this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1])) {
                                //We have more data than headers, this is a problem
                                throw new Exception("Header Not Found ; Line " . $this->line . " <br>Rule Line " . $this->ruleLine . " <br>Rule Position " . $this->rulePosition);
                            }
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[1][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']-1];
                        } else {
                            //store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[1][0];
                        }
                    }
                    break;
                case "STOREALL":
                    foreach($matches[1] as $match) {
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName'])) {
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] =$match;
                        } elseif (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])) {
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][]=$match;
                        }
                    }
                    break;
                case "STOREALLASDATA":
                    foreach($matches[1] as $index=>$match) {
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName'])) {
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][$index+$this->indexStart][] =$match;
                        } elseif (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])) {
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][$index+$this->indexStart][]=$match;
                        }
                        if($index>$this->currentColumns){
                            $this->currentColumns=$index+1;
                        }
                    }
                    break;
                case "STOREASHEADER":
                    // Store this information as a header
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                        $this->headers[] = $matches[1][$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'] - 1];
                    } else {
                        $this->headers[] = $matches[1][0];
                    }
                    break;
                case "STOREALLASHEADER":
                    foreach($matches[1] as $match) {
                        $this->headers[]=$match;
                    }
                    break;
                case "SKIP":
                    break;
                case "NEXTLINE":
                    // Go to the next line
                    return 2;
                    break;
                case "USELAST":
                    // Use the last rule for this position as well
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case "USELASTLINE":
                    // Go back 1 line in the config and advance 2 lines afterwords
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case "USELASTLINEUNTIL":
                    // Go back 1 line in the config and use that until errors
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    return 1;
                case "CONTINUE":
                    $this->advancement=0;
                    return 2;
                    break;
                case "NEXTRULE":
                    $newPosition=$this->rulePosition;
                    $newLine=$this->ruleLine;
                    //if we want to skip a specific number of rules, calculate where that puts us
                    if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip']=1;
                    }
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $newPosition += $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                        $ready = false;
                        $tick = 0;
                        while (!$ready) {
                            $tick++; //tick to make sure we donot get stuck in infinite loop
                            if (isset($this->config['Rules'][$newLine])) {
                                if ($newPosition > count($this->config['Rules'][$newLine])) {
                                    $newPosition=$newPosition-count($this->config['Rules'][$newLine]);
                                    $newLine++;
                                }elseif(isset($this->config['Rules'][$newLine][$newPosition])){
                                    $ready=true;
                                    if($newPosition>=1){
                                        $newPosition--;
                                    }
                                }
                            }

                            if($tick>100){
                                return "END";
                            }
                        }
                        $this->rulePosition=$newPosition;
                        $this->ruleLine=$newLine;
                    }
                    break;
                case "PREVIOUSLINE":
                    $this->rulePosition=0;
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])){
                     $this->ruleLine-=$this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                    }else {
                        $this->ruleLine -= 1;
                    }
                    if(!isset($this->config['Rules'][$this->ruleLine])){
                        $this->ruleLine=1;
                    }
                    break;
                case "INCREASEMULTIPLIER":
                    $this->indexStart+=$this->currentColumns;
                    break;
                case "END":
                    // If there is any array that only has a single value, then make it that value rather than an array
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

    /**
     * Process preg_match
     * @param $buffer
     * @param $results
     * @param $matches
     * @return int|string
     * @throws Exception
     */
    function handle_preg_match($buffer,&$results,$matches)
    {
        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action'])) {
            if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug'])&&$this->config['Rules'][$this->ruleLine][$this->rulePosition]['debug']==true) {
                echo "Line " . $this->line . "<br> Rule Line " . $this->ruleLine . "<br> Rule Position " . $this->rulePosition."<br> String:<pre>".$buffer."</pre><br><br>";
            }
            switch ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['action']) {
                case "STORE":
                    // If a name is given use that name
                    if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName'])) {
                        //if an index is given use that index
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check to see if a certain index was requested
                            if (!isset($matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']])) {
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure']!="STORE"||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly']) {
                                    $this->anomalies[] = "Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition . "<br> String:<pre>" . $buffer . "</pre><br><br>";
                                    return $this->handleFailure($buffer, $results);
                                }
                                break;
                            }
                            // Store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];
                        } else {
                            // Store it
                            $results[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['valueName']][] = $matches[0];
                        }
                        // If a header index is given use that
                    } elseif (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'])) {
                        // Check to see if a certain index was requested
                        if (isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex'])) {
                            //check if there is something stored there
                            if (!isset($matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']])) {
                                //either its going to be set as an anomaly in the store or we don't want it to be marked as such
                                if ($this->config['Rules'][$this->ruleLine][$this->rulePosition]['failure']!="STORE"||!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly'])||!$this->config['Rules'][$this->ruleLine][$this->rulePosition]['notAnomaly']) {
                                    $this->anomalies[] = "Line " . $this->line . " Rule Line " . $this->ruleLine . " Rule Position " . $this->rulePosition . "<br> String:<pre>" . $buffer . "</pre><br><br>";
                                    return $this->handleFailure($buffer, $results);
                                }
                            }
                            if (!isset($this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1])) {
                                // We have more data than headers, this is a problem
                                throw new Exception("Header Not Found ; Line " . $this->line . " <br>Rule Line " . $this->ruleLine . " <br>Rule Position " . $this->rulePosition);
                            }
                            // Store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];

                        } else {
                            // Store it
                            $results[$this->headers[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['headerIndex'] - 1]][] = $matches[0];
                        }
                    }
                    break;
                case "STOREASHEADER":
                    //store this information as a header
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']))
                        $this->headers[] = $matches[$this->config['Rules'][$this->ruleLine][$this->rulePosition]['matchIndex']];
                    else
                        $this->headers[] = $matches[0];
                    break;

                case "SKIP":
                    break;
                case "NEXTLINE":
                    return 2;
                    break;
                case "USELAST":
                    //use the last rule for this position as well
                    $this->rulePosition--;
                    $this->advancement++;
                    return 1;
                case "USELASTLINE":
                    //go back 1 line in the config and advance 2 lines afterwords
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    $this->lineAdvancement++;
                    return 1;
                case "USELASTLINEUNTIL":
                    //go back 1 line in the config and use that until errors
                    $this->rulePosition = 1;
                    $this->ruleLine--;
                    return 1;
                case "CONTINUE":
                    $this->advancement=0;
                    return 2;
                    break;
                case "NEXTRULE":
                    $newPosition=$this->rulePosition;
                    $newLine=$this->ruleLine;
                    //if we want to skip a specific number of rules, calculate where that puts us
                    if(!isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip']=1;
                    }
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])) {
                        $newPosition += $this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                        $ready = false;
                        $tick = 0;
                        while (!$ready) {
                            $tick++; //tick to make sure we donot get stuck in infinite loop
                            if (isset($this->config['Rules'][$newLine])) {
                                if ($newPosition > count($this->config['Rules'][$newLine])) {
                                    $newPosition=$newPosition-count($this->config['Rules'][$newLine]);
                                    $newLine++;
                                }elseif(isset($this->config['Rules'][$newLine][$newPosition])){
                                    $ready=true;
                                    if($newPosition>=1){
                                        $newPosition--;
                                    }
                                }
                            }
                            if($tick>100){
                                return "END";
                            }
                        }
                        $this->rulePosition=$newPosition;
                        $this->ruleLine=$newLine;
                    }
                    break;
                case "PREVIOUSLINE":
                    if(isset($this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'])){
                        $this->ruleLine-=$this->config['Rules'][$this->ruleLine][$this->rulePosition]['skip'];
                    }else {
                        $this->ruleLine -= 1;
                    }
                    if(!isset($this->config['Rules'][$this->ruleLine])){
                        $this->ruleLine=1;
                    }
                    $this->rulePosition=0;
                    break;
                case "INCREASEMULTIPLIER":
                    $this->indexStart+=$this->currentColumns;
                    break;
                case "END":
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
