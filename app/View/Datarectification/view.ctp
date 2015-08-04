<div style="width: 100%">
    <div style="width: 40%;display: inline-block;">
        <pre><?php echo $textfile['TextFile']['text']?></pre>

    </div>
    <div style="width: 40%;display: inline-block;">
        <pre><?php pr(json_decode($textfile['TextFile']['extracted_data'],true));?></pre>

    </div>
</div>