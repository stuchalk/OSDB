<h2>Results</h2>
    <?php
    foreach($data as $mass=>$results) {
        if(!empty($results)) {
            echo "<h3>".$mass." g/mol</h3><ul>";
            foreach($results as $id=>$name) {
                echo "<li>".$this->Html->link($name,'/substances/view/'.$id)."</li>";
            }
            echo "</ul>";
        }
    }
    ?>