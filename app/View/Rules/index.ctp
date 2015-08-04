<h1>List Rulesets</h1>
<table>

<?php
    $matchMethod=array("preg_match","preg_match_all");
    echo $this->Html->tableHeaders(array('Name', 'Regex Pattern', 'Action',  'Failure', 'Parameter Name', 'Error Text', 'Required', 'Match Index', 'Match Method', 'Header Index', 'Description','Example Line', 'updated' ));
    foreach($rules as $rule){
        echo "<tr>";
            echo "<td>".$rule['Rule']['name']."</td>";
            echo "<td>".$rule['Rule']['pattern']."</td>";
            echo "<td>" . $actions[$rule['Rule']['action']] . "</td>";
            if(isset($rule['Rule']['failure'])) {
                echo "<td>" . $actions[$rule['Rule']['failure']] . "</td>";
            }else{
                echo "<td></td>";
            }
            echo "<td>".$rule['Rule']['valueName']."</td>";
            echo "<td>".$rule['Rule']['errorText']."</td>";
            echo "<td>".$rule['Rule']['required']."</td>";
            echo "<td>".$rule['Rule']['matchIndex']."</td>";

            if(isset($rule['Rule']['matchMethod'])){
                echo "<td>" . $matchMethod[$rule['Rule']['matchMethod']] . "</td>";
            }else{
                echo "<td></td>";
            }
            echo "<td>".$rule['Rule']['headerIndex']."</td>";
            echo "<td>".$rule['Rule']['description']."</td>";
            echo "<td>".$rule['Rule']['example']."</td>";
            echo "<td>".$rule['Rule']['updated']."</td>";
        echo "</tr>";
    }
?>
</table>