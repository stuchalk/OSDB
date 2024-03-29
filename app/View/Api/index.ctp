 <?php
echo $this->Html->css('swagger/index');
echo $this->Html->css('swagger/standalone');
echo $this->Html->css('swagger/api-explorer');
echo $this->Html->css('swagger/screen',['media'=>'screen']);
echo $this->Html->script('swagger/lib/jquery.slideto.min');
echo $this->Html->script('swagger/lib/jquery.wiggle.min');
echo $this->Html->script('swagger/lib/jquery.ba-bbq.min');
echo $this->Html->script('swagger/lib/handlebars-2.0.0');
echo $this->Html->script('swagger/lib/underscore-min');
echo $this->Html->script('swagger/lib/backbone-min');
echo $this->Html->script('swagger/swagger-ui.min');
echo $this->Html->script('swagger/lib/highlight.7.3.pack');
echo $this->Html->script('swagger/lib/jsoneditor');
echo $this->Html->script('swagger/lib/marked');
echo $this->Html->script('swagger/lib/swagger-oauth');
echo $this->Html->script('swagger/lib/bootstrap.min');
?>

<script type="text/javascript">
    jQuery.browser = jQuery.browser || {};
    (function () {
        jQuery.browser.msie = jQuery.browser.msie || false;
        jQuery.browser.version = jQuery.browser.version || 0;
        if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
            jQuery.browser.msie = true;
            jQuery.browser.version = RegExp.$1;
        }
    })();
</script>

<script type="text/javascript">
    $(function () {
        var url = window.location.search.match(/url=([^&]+)/);
        if (url && url.length > 1) {
            url = decodeURIComponent(url[1]);
        } else {
            url = "<?php echo Configure::read('url'); ?>" + "/files/swagger.json";
        }

        window.swaggerUi = new SwaggerUi({
            url: url,
            dom_id: "swagger-ui-container",
            supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
            onComplete: function (swaggerApi, swaggerUi) {
                if (typeof initOAuth == "function") {
                    initOAuth({
                        clientId: "ffe7748a-3a3f-4860-a02a-42ab08e4fde2",
                        realm: "realm",
                        appName: "Swagger"
                    });

                }

                $('pre code').each(function (i, e) {
                    hljs.highlightBlock(e)
                });

                if (swaggerUi.options.url) {
                    $('#input_baseUrl').val(swaggerUi.options.url);
                }
                if (swaggerUi.options.apiKey) {
                    $('#input_apiKey').val(swaggerUi.options.apiKey);
                }

                $("[data-toggle='tooltip']").tooltip();

                addApiKeyAuthorization();
            },
            onFailure: function (data) {
                log("Unable to Load SwaggerUI");
            },
            docExpansion: "none",
            sorter: "alpha"
        });

        //function addApiKeyAuthorization() {
        //    var key = encodeURIComponent($('#input_apiKey')[0].value);
        //    if (key && key.trim() != "") {
        //        var apiKeyAuth = new SwaggerClient.ApiKeyAuthorization("Authorization", "Bearer " + key, "header");
        //        window.swaggerUi.api.clientAuthorizations.add("key", apiKeyAuth);
        //        log("added key " + key);
        //    }
        //}

        //$('#input_apiKey').change(addApiKeyAuthorization);
        // if you have an apiKey you would like to pre-populate on the page for demonstration purposes...
        /*
         var apiKey = "myApiKeyXXXX123456789";
         $('#input_apiKey').val(apiKey);
         */

        window.swaggerUi.load();

        function log() {
            if ('console' in window) {
                console.log.apply(console, arguments);
            }
        }
    });
</script>

<script type="text/javascript">

    $(function () {

        $(window).scroll(function () {
            var sticky = $(".sticky-nav");

            i(sticky);
            r(sticky);

            function n() {
                return window.matchMedia("(min-width: 992px)").matches
            }

            function e() {
                n() ? sticky.parents(".sticky-nav-placeholder").removeAttr("style") : sticky.parents(".sticky-nav-placeholder").css("min-height", sticky.outerHeight())
            }

            function i(n) {
                n.hasClass("fixed") || (navOffset = n.offset().top);
                e();
                $(window).scrollTop() > navOffset ? $(".modal.in").length || n.addClass("fixed") : n.removeClass("fixed")
            }

            function r(e) {
                function i() {
                    var i = $(window).scrollTop(), r = e.parents(".sticky-nav");
                    return r.hasClass("fixed") && !n() && (i = i + r.outerHeight() + 40), i
                }

                function r(e) {
                    var t = o.next("[data-endpoint]"), n = o.prev("[data-endpoint]");
                    return "next" === e ? t.length ? t : o.parent().next().find("[data-endpoint]").first() : "prev" === e ? n.length ? n : o.parent().prev().find("[data-endpoint]").last() : []
                }

                var nav = e.find("[data-navigator]");
                if (nav.find("[data-endpoint][data-selected]").length) {
                    var o = nav.find("[data-endpoint][data-selected]"),
                        a = $("#" + o.attr("data-endpoint")),
                        s = a.offset().top,
                        l = (s + a.outerHeight(), r("next")),
                        u = r("prev");
                    if (l.length) {
                        {
                            var d = $("#" + l.attr("data-endpoint")), f = d.offset().top;
                            f + d.outerHeight()
                        }
                        i() >= f && c(l)
                    }
                    if (u.length) {
                        var p = $("#" + u.attr("data-endpoint")),
                            g = u.offset().top;
                        v = (g + p.outerHeight(), 100);
                        i() < s - v && c(u)
                    }
                }
            }

            function s() {
                var e = $(".sticky-nav [data-navigator]"),
                    n = e.find("[data-endpoint]").first();
                n.attr("data-selected", "");
                u.find("[data-selected-value]").html(n.text())
            }

            function c(e) {
                {
                    var n = $(".sticky-nav [data-navigator]");
                    $("#" + e.attr("data-endpoint"))
                }
                n.find("[data-resource]").removeClass("active");
                n.find("[data-selected]").removeAttr("data-selected");
                e.closest("[data-resource]").addClass("active");
                e.attr("data-selected", "");
                sticky.find("[data-selected-value]").html(e.text())
            }
        });

    });
</script>

<script type="text/javascript">
    $(function () {
        $("[data-toggle='tooltip']").tooltip();
    });
</script>

<h2>OSDB API Explorer</h2>
<div id="api2-explorer">
    <div class="swagger-section page-docs" style="zoom: 1;">
        <div class="main-section">
            <div id="swagger-ui-container" class="swagger-ui-wrap">
            </div>
        </div>
    </div>
</div>