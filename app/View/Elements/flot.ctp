<script type="text/javascript">
    $(function() {
        var options = {
            lines: { show: true },
            points: { show: false },
            xaxis: { tickDecimals: 0, tickSize: 100 }};
        var data = [];

        $.plot("#placeholder", data, options);

        // Fetch one series, adding to what we already have
        var alreadyFetched = {};

        $("button.fetchSeries").click(function () {
            var button = $(this);

            // Find the URL in the link right next to us, then fetch the data
            var dataurl = button.attr("id");

            function onDataReceived(series) {

                // Extract the first coordinate pair; jQuery has parsed it, so
                // the data is now just an ordinary JavaScript object

                var firstcoordinate = "(" + series.data[0][0] + ", " + series.data[0][1] + ")";
                button.siblings("span").text("Fetched " + series.label + ", first point: " + firstcoordinate);

                // Push the new data onto our existing data array

                if (!alreadyFetched[series.label]) {
                    alreadyFetched[series.label] = true;
                    data.push(series);
                }

                $.plot("#placeholder", data, options);
            }

            $.ajax({
                url: dataurl,
                type: "GET",
                dataType: "json",
                success: onDataReceived
            });
        });

        // Load the first series by default, so we don't have an empty plot
        $("button.fetchSeries:first").click();
    });
</script>

<div id="placeholder" style="width:600px;height:400px"></div>
<?php $url="/osdb/data/flot/".$xsid."/".$ysid; ?>
<button class="fetchSeries" id="<?php echo $url; ?>">First dataset</button>

