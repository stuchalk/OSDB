<script language="JavaScript" type="text/javascript">
    $(document).ready(function () {
        $.plot($("#flot-placeholder"),
            data,
            options);
    });

    var options = {
        series: {
            lines: { show: true },
            points: { show: false }
        }
    };

    var data = [ { label: "Foo", data: [ [10, 1], [17, -14], [30, 5] ] },
        { label: "Bar", data: [ [11, 13], [19, 11], [30, -7] ] }
    ];
</script>

<div id="flot-placeholder" style="width:600px;height:400px">

</div>