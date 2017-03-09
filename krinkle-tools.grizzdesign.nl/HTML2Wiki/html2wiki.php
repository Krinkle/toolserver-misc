<?php
    header('Content-Type: text/javascript; charset=utf-8');

    $copyJS = $_GET['copyJS'];
?>
$(function(){
	$("body").addClass("JS");
	<?php if ($copyJS == "on") { ?>
    $("pre#htmloutput").each(function() {

        var clip = new ZeroClipboard.Client();
        var thisObj = $(this);
        clip.glue(thisObj[0]);
        var txt = $(this).text();
        clip.setText(txt);

        clip.addEventListener('complete', function(client, text) {
            $("#copied-notice").fadeIn(250, function(){
	           $(this).fadeOut(1500);
            });
        });

    });

    $("pre#htmloutput").addClass("js-copy");
    <?php } else { echo "/* copyJS off */"; } ?>

    $("#cc code, #cc div").slideToggle();

    $("#cc h4#attr-code, #cc h4#preview").append(' <small><a href="#">show/hide code</a></small>')

    $("#cc h4 a").click(function(){
    	$("#cc code, #cc div").slideToggle();
    	return false;
    });

    $("h1").click(function(){
    	window.location=jQuery("a#home").attr("href");
		return false;
    });

});
