<?php
/**
 * SpeedPagePatrol_hack.php :: Tmp version that only lists revisions since the action=markpatrolled
 *  now requires a token so we can't attack it via simple get requests in an iframe. -- 2011-04-02
 *
 * @package SpeedPagePatrol
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$is_submit = !empty($_GET['title']) ? true : false;
$revID = "0.0.4b";
$revDate = '2011-04-02';

$c['title'] = "SpeedPagePatrol";
$c['baseurl'] = "//toolserver.org/~krinkle/SpeedPagePatrol.php";
$_GET['namespace'] = (int)trim($_GET['namespace']);
$c['wiki'] = CacheAndDefault( getParamVar( 'wiki' ), 'commonswiki' );

if ( in_array( $c['wiki'], $c['wikis_rcp'] ) ) {
	if ( $is_submit ) {

		$c['pagetitle'] = trim($_GET['title']);
		$c['namespace'] = is_int($_GET['namespace']) ? $_GET['namespace'] : "0";
	}
} else {
	die("Error: Wiki not found.");
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?> - <?php echo $c['url'][$c['wiki']]; ?> / <?php echo $c['namespace'].":".$c['pagetitle']; ?></title>
	<link rel="stylesheet" href="main.css">
</head>
<body>
	<div id="page-wrap">
		<h1><small>Krinkle</small> | <s><?=$c['title']?></s>UnpatrolledEditsDiffsPerPage</h1>
		<hr />
<?php
krMsg('Tool <tt>' . $c['title'] . '</tt> is temporarily disabled for maintenance. The diffs are listed below.');
if($is_submit){ // if submitted:
?>
		<h3 id="result"><?php echo $c['namespace'].":".$c['pagetitle']; ?> on <?php echo $c['url'][$c['wiki']]; ?></h3>
	<?php

	$toolserver_mycnf = parse_ini_file("/home/".get_current_user()."/.my.cnf");
	$dbConnect = mysql_connect($c['wiki'].'-p.rrdb.toolserver.org', $toolserver_mycnf['user'], $toolserver_mycnf['password']);
	if (!$dbConnect) {
		die('dbConnect: ERROR: \n' . mysql_error());
	} else {
		krLog("dbConnect: OK");
	}
	$dbSelect = mysql_select_db($c['wiki'].'_p', $dbConnect);
	if (!$dbSelect) {
		die ("dbSelect: ERROR; \n" . mysql_error());
	} else {
		krLog("dbSelect: OK");
	}
	$dbQuery = "
	SELECT

		rc_id,
		rc_namespace,
		rc_this_oldid,
		rc_last_oldid,
		rc_timestamp as date

	FROM recentchanges
	WHERE (rc_type = 0 OR rc_type = 1)
	AND rc_patrolled != 1
	AND rc_user = 0
	AND rc_title = '".mysql_real_escape_string($c['pagetitle'])."'
	AND rc_namespace = ".$c['namespace']."
	ORDER BY date DESC";
	krLog("dbQuery: \n".$dbQuery);
	$dbResult = mysql_query($dbQuery,$dbConnect);
	if ( !!$dbResult ) {

		krLog("dbQuery: OK");

		$rcid_list = '';

		foreach ( mysql_fetch_all( $dbResult ) as $hit ) {

				$link = "//commons.wikimedia.org/w/index.php?"
				. "oldid={$hit['rc_last_oldid']}&diff={$hit['rc_this_oldid']}&rcid={$hit['rc_id']}&diffonly=1";

				$rcid_list .=
				  "<li><a href=\"$link\">"
				. "diff: {$hit['rc_this_oldid']}; rcid: {$hit['rc_id']}"
				. "</a></li>\n";
				$scripts[] = "window.open(" .json_encode($link) . ");\n";

		}

		if ( trim($rcid_list) == '' ) {
			echo '<p class="error">There are no unpatrolled edits to the choosen page.</p>';
		} else {
			echo "<ul class=ns>$rcid_list</ul>";
		}
	} else {
		echo "Can not select query: \n" . mysql_error();
	}
	mysql_close($dbConnect);
	?>


<?php
} else { // if not submitted:
?>
		<h3 id="result">wiki, title and or namespace undefined</h3>
			<p>In order to use the SpeedPagePatrol tool you need to pre-define the three above mentioned settings. You can do so by using quicklinks such as the one in <a href="getTopRCpages.php?wiki=<?=$c['wiki']?>">Get Top RC Pages</a> and <a href="//meta.wikimedia.org/wiki/User:Krinkle/Tools/Real-Time_Recent_Changes">Real-Time Recent Changes</a>.</p>




<?php } //endif submited
if( krDebug() ){
	echo "<hr /><pre>## DEBUG:\n\n".$krSandbox['log']."</pre><hr id='debug' />";
}

?>
		<h3 id="wikilist">List of supported RC-patrol wikis</h3>
			<ul><?php foreach($c['wikis_rcp'] as $rcpwiki){
				echo ' <li>'.$rcpwiki.': '.$c['url'][$rcpwiki]."</li>\n";
			} ?>
			</ul>
	</div>
<script>function aba(){ <?php echo join(' ',$scripts); ?> }</script>
</body>
</html>
