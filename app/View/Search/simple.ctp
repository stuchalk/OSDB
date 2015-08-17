<h2>Results</h2>
    <?php
    foreach($data as $table=>$results) {
        if(!empty($results)) {
            if($table=="Report") { $table="Spectra"; }
            echo "<h3>".$table."</h3><ul>";
            foreach($results as $id=>$name) {
                echo "<li>".$this->Html->link($name,'/'.Inflector::pluralize(strtolower($table)).'/view/'.$id)."</li>";
            }
            echo "</ul>";
        }
    }
    ?>