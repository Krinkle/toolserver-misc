<?php
/**
 * getTopRCpages.php :: All-in-One file
 *
 * Get Top RC Pages
 * Created on August 28th, 2010
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2013
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$is_submit = false;
$revID = "0.2.1";
$revDate = '2012-09-25';

$c['title'] = "Get Top RC Pages";
$c['baseurl'] = "//toolserver.org/~krinkle/getTopRCpages.php";
$_GET['namespace'] = (int)trim($_GET['namespace']);
$c['wiki'] = CacheAndDefault( getParamVar( 'wiki' ) );


if ( $c['wiki'] ) {
	if( in_array( $c['wiki'], $c['wikis_rcp'] ) ) {

		$c['type'] = "edit OR new";
		$c['anon_users'] = "yes";
		$c['reg_users'] = "no";
		$c['rtrcparams'] = "&typeedit=on";
		$dbQuery = " /* LIMIT:10 */ /* getTopRCpages( {$c['wiki']} ) */
	SELECT

	rc_title,
	rc_namespace,
	count(*) as counter

	FROM recentchanges
	WHERE (rc_type = 0 OR rc_type = 1)
	AND rc_patrolled != 1
	AND rc_user = 0
	GROUP BY rc_title
	ORDER BY counter DESC
	LIMIT 15
	";
		if ( $k ){
			$dbQuery = " /* LIMIT:10 */ /* getTopRCpages( {$c['wiki']} ) */
				SELECT

				rc_title,
				rc_namespace,
				nsname.ns_name as _namespace_name
				COUNT(*) as counter

				FROM recentchanges rc, toolserver.namespacename nsname
				WHERE (rc_type = 0 OR rc_type = 1)
				AND rc_patrolled != 1
				/* AND rc_user = 0 */
					AND nsname.dbname = 'commonswiki_p'
					AND nsname.ns_is_favorite = 1
					AND nsname.ns_id = rc.rc_namespace
				GROUP BY rc_title
				ORDER BY counter DESC
				LIMIT 15
				";
			}
			$dbQuery2 = " /* LIMIT:10 */ /* getTopRCpages( {$c['wiki']} ) */
				SELECT ns_id, ns_name
				FROM namespacename
				WHERE dbname = 'commonswiki_p'
				AND ns_is_favorite = 1;
				";

	} else {
		die("Error: Wiki not found.");
	}
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?> - <?php echo $c['url'][$c['wiki']]; ?></title>
	<link rel="stylesheet" href="main.css">
	<link rel="stylesheet" href="shadowbox/shadowbox.css">
	<style>
	ul#rcid_list li { background-color:#F9F9F9; border: 2px solid #AAA; list-style:none; padding:2px }
	ul#rcid_list li.error { background-color:#FFF2F2; border: 2px solid red }
	ul#rcid_list li.ok { background-color:#DFD; border:2px solid green; padding:0; margin:0 0 1px; font-size:smaller }
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="shadowbox/shadowbox.js"></script>
	<script>
	Shadowbox.init({
		modal: true,
		overlayOpacity: 0.7
	});
	</script>
</head>

<body>
	<div id="page-wrap">

		<h1><small>Krinkle</small> | <?=$c['title']?></h1>
		<small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
		<hr /><?php echo $c['nav']; ?><hr />
<?php
if ( $c['wiki'] ) {
?>
		<h3 id="result">Most active pages on <?php echo $c['url'][$c['wiki']]; ?><br /><small>(by unpatrolled anonymous contributions)</small></h3>
<?php if( krDebug() ){ ?><pre>
  wiki= <?=$c['wiki']?>

  type= <?=$c['type']?>

  anon_users= <?=$c['anon_users']?>

  reg_users= <?=$c['reg_users']?>

  patrolled= no
</pre><?php } ?>

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

$dbResult = mysql_query($dbQuery, $dbConnect);

$dbSelect = mysql_select_db('toolserver', $dbConnect);
$dbResult2 = mysql_query($dbQuery2, $dbConnect);

$namespacePrefixes = array();
if ($dbResult2) {
	foreach (mysql_object_all($dbResult2) as $ns) {
		$namespacePrefixes[$ns->ns_id] = $ns->ns_name . ':';
	}
	$namespacePrefixes['0'] = '';
}

if ($dbResult){
	krLog("dbQuery: OK");
	echo "<table><tr><th>#&nbsp;&nbsp;&nbsp;</th><th>SpeedPagePatrol quicklinks</th></tr>";
	foreach (mysql_fetch_all($dbResult) as $hit) {
		echo '<tr><td>' . $hit['counter']
			. '</td><td><a href="SpeedPagePatrol.php?wiki='.$c['wiki'].'&title='.$hit['rc_title'].'&namespace='.$hit['rc_namespace'].'">'
			. htmlspecialchars( $namespacePrefixes[$hit['rc_namespace']] . $hit['rc_title'] )
			. '</a></td></tr>';

	}
	echo "</table><p>Patrollers: Make sure that you're logged in on ".$c['url'][$c['wiki']]."!</p>";
} else {
	echo "Can not select query: \n" . mysql_error();
}

mysql_close($dbConnect);

if ( krDebug() ){
	echo "<hr /><pre>## DEBUG:\n\n".$krSandbox['log']."</pre><hr id='debug' />";
}

}

?>
		<h3 id="wikilist">List of supported RC-patrol wikis</h3>
			<ul><?php foreach($c['wikis_rcp'] as $rcpwiki){
				echo '<li><a href="'.$c['baseurl'].'?wiki='.$rcpwiki.'">'.$c['url'][$rcpwiki].'</a></li>';
			} ?>
			</ul>

		<h3 id="author">Author</h3>
			<p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail&middot;com</em>, or leave a message on the <a href="//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload">Tools feedback page</a>.</p>
	</div>

</body>
</html>
