<?php

/**
 * Configuration file for default values for conversion of JCAMP files
 */

// JCAMP converter evaluates what file type is being processed and then defines DATATYPE as below
$config['jcamp']['datatypes']=[ 'uvvis'=>'UV/VIS SPECTRUM',
    'ir'=>'IR SPECTRUM',
    'ms'=>'MASS SPECTRUM',
    'nmr'=>'NMR SPECTRUM',
    'flow'=>'FLOW ANALYSIS'];


// Crosswalk: Where does data in a jcamp file go in AnIML?
$config['jcamp']['uvvis']['crosswalk']['sampledescription']="/AnIML/SampleSet[1]/Sample[1]/Category[1]/Parameter[@name='Descriptive Name']";

?>
