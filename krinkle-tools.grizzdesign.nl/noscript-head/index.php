<?php
header("text/html; charset=UTF-8");
header("Cache-Control: no-cache");
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Noscripts in head supported + external resources ?</title>
	<script type="text/javascript">
	// this file should be download only by JS enabled browsers
	var link = document.createElement("link");
	link.rel = "stylesheet";
	link.media = "screen";
	link.href = "is-js.css";
	document.getElementsByTagName("head")[0].appendChild(link);
	</script>
	<noscript>
		<link rel="stylesheet" href="no-js.css" />
	</noscript>
</head>
<body>
<div id="wrap">
	<h1>Blue (JS)</h1>
	<h1>Noscript-CSS Yes (Green)</h1>
	<h1>Noscript-CSS No (Black)</h1>
</div>
</body>
</html>
