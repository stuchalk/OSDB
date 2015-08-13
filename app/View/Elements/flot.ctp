<?php
    // $config has a lot of data about the spectrum in it
    $w=600;$h=400;
    $scale=floor($config['points']/$w);
    $url="/osdb/data/flot/".$config['xsid']."/".$config['ysid'];
    $range=$config['maxx']-$config['minx'];
    $ticksize=1;
    //pr($config);
?>
<script type="text/javascript">
    $(function() {

        // Fetch one series, adding to what we already have
        var alreadyFetched={};
        var data;
        var options;
        var button;
        var dataurl;
        var firstcoordinate;

        $("button.fetchSeries").click(function () {

            options = {
                lines: { show: true },
                points: { show: false },
                xaxis: { tickDecimals: 0,
                    tickSize: <?php echo $ticksize; ?>,
                    transform: function (v) { return -v; }
                },
                yaxis: { min: <?php echo $config['miny']; ?> },
                grid: { show: true,
                    color: ["#DDDDDD"],
                    clickable: true,
                    margin: { top: 20, bottom: 20, left: 20, right: 20 }
                }
            };

            data = [];
            $.plot("#placeholder", data, options);
            button = $(this);

            // Find the URL in the link right next to us, then fetch the data
            dataurl = button.attr("id");

            function onDataReceived(series) {

                // Extract the first coordinate pair; jQuery has parsed it, so
                // the data is now just an ordinary JavaScript object

                firstcoordinate = "(" + series.data[0][0] + ", " + series.data[0][1] + ")";
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

        $("#placeholder").bind("plotclick", function (event, pos, item) {
            alert("You clicked at " + pos.x + ", " + pos.y);
            // axis coordinates for other axes, if present, are in pos.x2, pos.x3, ...
            // if you need global screen coordinates, they are pos.pageX, pos.pageY

            if (item) {
                highlight(item.series, item.datapoint);
                alert("You clicked a point!");
            }
        });

        // Load the first series by default, so we don't have an empty plot
        $("button.fetchSeries:first").click();
    });
</script>

<div id="placeholder" style="width:<?php echo $w; ?>px;height:<?php echo $h; ?>px;border: 1px solid #BBBBBB;box-shadow: 10px 10px 5px #BBBBBB;"></div>
<p>&nbsp;</p>
<button class="fetchSeries" id="<?php echo $url."/0/0/".$scale."/nmrppm/".$config['freq']; ?>" style="display: none;"></button>
