<?php
    if($this->Session->check('Auth'))
    {
        echo "<h2>"."Project Status"."</h2>";
        echo "Total Number of Files: ".$filecount."<br>";
        echo "Total Number of Text Files: ".$textfilecount."<br>";
        echo "Total Number of Data Series: ".$dataseriescount."<br>";
        echo "Total Number of Datasets: ".$datasetcount."<br>";
        echo "Total Number of Publications: ".$pubcount."<br><br>";

        echo "<h3>"."Extract Data"."</h3>";
        echo "In order to begin extracting data a file must first be uploaded to the database. To upload a new file click ".$this->Html->link('here','/files/add').",".
             "or continue with a ".$this->Html->link('previously uploaded file','/files/processing')."."."<br>"."<br>";

        ?>
        <?php
    } else {
        echo "<h2>"."Project Plan (3/3/15)"."</h2>".

            '<p>'.'Below are the steps that we need to code for in order to execute the export, cleaning, identification, import, and normalization of the data from the Springer PDF files'.'</p>'.

            '<ul>'.
            '<li>'.'PDF Export'.
            '<ol>'.
            '<li>'.'Save PDF in latest format (new file)'.'</li>'.
            '<li>'.'Export PDF text using pdftotext.exe (options?) using standardized name'.'</li>'.
            '<li>'.'Check text file for any strange non-UTF8 characters'.'</li>'.
            '</ol>'.
            '</li>'.
            '<li>'.'Text File Cleaning (preprocessing)'.
            '<ol>'.
            '<li>'.'Define rules to describe layout of text (this needs a web form)'.'</li>'.
            '<li>'.'Identify and fix any \'PDF export\' anomalies to produce \'standardized\' text file (new file)'.'</li>'.
            '<li>'.'Write edits that were made (and by who) to a log file'.'</li>'.
            '</ol>'.
            '</li>'.
            '<li>'.'Text File Data Identification'.
            '<ol>'.
            '<li>'.'Process text file to identify chemical and associated data, references to original literature, comments'.'</li>'.
            '<li>'.'Clean up chemical formulae, search PubChem, Chemical Identifier Resolver, and Chemspider to get InChI strings/keys'.'</li>'.
            '<li>'.'Save as json file (so we can compare to data when uploaded to MySQL)'.'</li>'.
            '<li>'.'Log chemicals found (including line numbers) and wether the InChI lookup worked for the chemical'.'</li>'.
            '</ol>'.
            '</li>'.
            '<li>'.'MySQL Import'.
            '<ol>'.
            '<li>'.'On ingest generate unique id from file name and Springer ID for each chemical in file (add to join table as one chemical may have multiple property data)'.'</li>'.
            '<li>'.'Add data to the table for that data type, add filename and line number for each chemical, add user that processed data'.'</li>'.
            '<li>'.'Log the results from the ingest so that any rejected data can be identified'.'</li>'.
            '</ol>'.
            '</li>'.
            '<li>'.'MySQL Normalization'.
            '<ol>'.
            '<li>'.'Develop set of SQL statements to check/clean MySQL tables for field consistency'.'</li>'.
            '</ol>'.
            '</li>'.
            '</ul>'.

            '<p>'.'&nbsp;'.'We also need to be able to document this process in terms of what happened when, abnormalities detected in the original data, etc.'.'</p>'.'<br>';
    }
?>