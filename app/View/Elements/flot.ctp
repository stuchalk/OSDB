<?php
    // Set up defaults
    if(!isset($w)||$w=="auto") {
        $w="100%";
        $pixels=1000;
    } else {
        $pixels=$w;
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
            $scale=floor($config['points']/$pixels);
            $url.="/0/0/".$scale."/nmrppm/".$config['freq'];
        }
        $tform=", transform: function (v) { return -v; } ";
        $xlabel="Chemical Shift (ppm)";
        $ylabel="Arbitrary Units";
    } elseif($config['tech']=='ms') {
        $lines="false";$bars="true";$points="false";
        $xlabel="Mass-to-Charge Ratio (m/z)";
        $ylabel="Relative Abundance";
    } elseif($config['tech']=='uv') {
        $xlabel="Wavelength (nm)";
        if(!isset($config['ylabel'])) {
            $ylabel="Absorbance";
        } else {
            $ylabel=$config['ylabel'];
        }
    } elseif($config['tech']=='ir') {
        $scale=floor($config['points']/$pixels);
        if($scale==0) { $scale=1; }
        $tform=", transform: function (v) { return -v; } ";
        $xlabel="Wavenumber (1/cm)";
        if(!isset($config['ylabel'])) {
            $ylabel="Transmission (%T)";
        } else {
            $ylabel=$config['ylabel'];
        }
        $url.="/0/0/".$scale;
    }

    // Scale the x-axis
    if(isset($config['maxx'])&&isset($config['minx'])) {
        $range=$config['maxx']-$config['minx'];
    } elseif(isset($config['firstx'])&&isset($config['lastx'])) {
        $range=abs($config['firstx']-$config['lastx']);
    } else {
        $range=9;
    }

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
        var xlabel='<?php echo $xlabel; ?>';
        var ylabel='<?php echo $ylabel; ?>';
        var x;
        var y;

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
                    hoverable: true,
                    clickable: true,
                    margin: { top: 20, bottom: 20, left: 20, right: 20 }
                },
                xaxes: [{
                    axisLabel: xlabel,
                }],
                yaxes: [{
                    position: 'left',
                    axisLabel: ylabel,
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

        // TODO: plothover not working at all and causes error for MS spectra (only)
        //placeholder.bind("plothover", function (event, pos, item) {
        //    if (item) {
        //        alert(item);
        //        x = item.datapoint[0].toFixed(0),
        //            y = item.datapoint[1].toFixed(0);
        //
        //        $("#tooltip").html(item.series.label + " of " + x + " = " + y)
        //            .css({top: item.pageY + 5, left: item.pageX + 5})
        //            .fadeIn(200);
        //    } else {
        //        $("#tooltip").hide();
        //    }
        //});

        placeholder.bind("plotclick", function (event, pos, item) {
            alert(xlabel + " " + Math.abs(pos.x.toFixed(1)) + ", " + ylabel + " = " + pos.y.toFixed(1));
            // axis coordinates for other axes, if present, are in pos.x2, pos.x3, ...
            // if you need global screen coordinates, they are pos.pageX, pos.pageY

        });

        window.onresize = function(event) {
            $.plot(placeholder, data, options);
        };

        // Load the first series by default, so we don't have an empty plot
        $("button.fetchSeries:first").click();
    });
</script>

<div id="placeholder" style="width:<?php echo $w; ?>;height:<?php echo $h; ?>px;border: 1px solid #BBBBBB;box-shadow: 10px 10px 5px #BBBBBB;"></div>
<div class="message"></div>
<div id="tooltip" style="position: absolute; border: 1px solid rgb(255, 221, 221); padding: 2px; background-color: rgb(255, 238, 238); opacity: 0.8; top: 462px; left: 631px; display: none;"></div>
<button class="fetchSeries" id="<?php echo $url; ?>" style="display: none;"></button>
<!--<code>
    <?php //pr($config); ?>
</code>-->