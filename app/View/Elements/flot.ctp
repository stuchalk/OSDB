<?php
    // Set up defaults
    if(!isset($w)||$w=="auto") {
        $w="100%";
    } else {
        $w=$w."px";
    }

    if(!isset($h))      { $h=400; }
    if(!isset($title))  { $title="Unknown Spectrum"; }

    $ticksize=1;$tform="";$xlabel="";$ylabel="";
    $lines="true";$bars="false";$points="false";
    if($this->request->host()=="sds.coas.unf.edu") {
        $url="/osdb/data/flot/".$config['xsid']."/".$config['ysid'];
    } else {
        $url="/data/flot/".$config['xsid']."/".$config['ysid'];
    }

    // Now add technique specific changes
    if($config['tech']=='nmr') {
        if(isset($config['freq'])) {
            $scale=floor($config['points']/$w);
            $url.="/0/0/".$scale."/nmrppm/".$config['freq'];
        }
        $tform=", transform: function (v) { return -v; } ";
        $xlabel="Chemical Shift (ppm)";
        $ylabel="Arbitrary Units";
    } elseif($config['tech']=='ms') {
        $lines="false";$bars="true";$points="false";
        $xlabel="Mass-to-Charge Ratio (m/z)";
        $ylabel="Relative Abundance";
    } elseif($config['tech']=='ir') {
        $scale=floor($config['points']/$w);
        $tform=", transform: function (v) { return -v; } ";
        $xlabel="Wavenumber (1/cm)";
        $ylabel="Transmission (%T)";
        $url.="/0/0/".$scale;
    }

    // Scale the x-axis
    $range=$config['maxx']-$config['minx'];
    if($range<10) {
        $ticksize=1;
    } elseif($range<20) {
        $ticksize=2;
    } elseif($range<50) {
        $ticksize=5;
    } elseif($range<100) {
        $ticksize=10;
    } elseif($range<200) {
        $ticksize=20;
    } elseif($range<500) {
        $ticksize=50;
    } elseif($range<1000) {
        $ticksize=100;
    } else {
        $ticksize=1000;
    }
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
        var placeholder = $("#placeholder");

        $("button.fetchSeries").click(function () {

            options = {
                lines: { show: <?php echo $lines; ?> },
                points: { show: <?php echo $points; ?> },
                bars: { show: <?php echo $bars; ?> },
                axisLabels: { show: true },
                xaxis: { tickDecimals: 0,
                    tickSize: <?php echo $ticksize; ?>
                    <?php echo $tform; ?>},
                yaxis: { min: <?php echo $config['miny']; ?> },
                grid: { show: true,
                    color: ["#DDDDDD"],
                    clickable: true,
                    margin: { top: 20, bottom: 20, left: 20, right: 20 }
                },
                xaxes: [{
                    axisLabel: '<?php echo $xlabel; ?>',
                }],
                yaxes: [{
                    position: 'left',
                    axisLabel: '<?php echo $ylabel; ?>',
                }]
            };

            data = [];
            $.plot(placeholder, data, options);
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

                $.plot(placeholder, data, options);
            }

            $.ajax({
                url: dataurl,
                type: "GET",
                dataType: "json",
                success: onDataReceived
            });
        });

        placeholder.bind("plotclick", function (event, pos, item) {
            alert("You clicked at " + pos.x + ", " + pos.y);
            // axis coordinates for other axes, if present, are in pos.x2, pos.x3, ...
            // if you need global screen coordinates, they are pos.pageX, pos.pageY

            if (item) {
                highlight(item.series, item.datapoint);
                alert("You clicked a point!");
            }
        });

        window.onresize = function(event) {
            $.plot(placeholder, data, options);
        }

        // Load the first series by default, so we don't have an empty plot
        $("button.fetchSeries:first").click();
    });
</script>

<div id="placeholder" style="width:<?php echo $w; ?>;height:<?php echo $h; ?>px;border: 1px solid #BBBBBB;box-shadow: 10px 10px 5px #BBBBBB;"></div>
<div class="message"></div>
<button class="fetchSeries" id="<?php echo $url; ?>" style="display: none;"></button>