<?php
/**
 * SpeedPagePatrol.php :: All-in-One file
 *
 * SpeedPagePatrol
 * Created on August 29th, 2010
 *
 * @package SpeedPagePatrol
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once('CommonStuff.php');

$is_submit = !empty($_GET['title']) ? true : false;
$revID = "0.0.4";
$revDate = '2010-08-29';

$c['title'] = "SpeedPagePatrol";
$c['baseurl'] = "http://toolserver.org/~krinkle/SpeedPagePatrol.php";
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
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?> - <?php echo $c['url'][$c['wiki']]; ?> / <?php echo $c['namespace'].":".$c['pagetitle']; ?></title>
	<link rel="stylesheet" href="shadowbox/shadowbox.css">
	<link rel="stylesheet" href="main.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="shadowbox/shadowbox.js"></script>
	<style>
	.rcid_list_status { color:white; font-weight:bold }
	ul#rcid_list li { background-color:#F9F9F9; border: 2px solid #AAA; list-style:none; padding:2px; color:black }
	ul#rcid_list li.error, .error { background-color:#FFF2F2; border: 2px solid red; color:black }
	ul#rcid_list li.ok, .ok { background-color:#DFD; border:2px solid green; padding:0; margin:0 0 1px; font-size:smaller; color:black }
	.rcid_list_status.ok { font-size:larger; margin:5px; padding:5px }
	</style>
</head>
<body>
	<div id="page-wrap">
		<h1><small>Krinkle</small> | <?=$c['title']?></h1>
		<small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
		<hr />
<?php
krMsg('Tool <tt>' . $c['title'] . '</tt> is temporarily closed for maintenance.');
krQuit();
if($is_submit){ // if submitted:
?>
		<h3 id="result">SpeedPagePatrol: <?php echo $c['namespace'].":".$c['pagetitle']; ?> on <?php echo $c['url'][$c['wiki']]; ?></h3>
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
	ORDER BY date DESC
";
	krLog("dbQuery: \n".$dbQuery);
	$dbResult = mysql_query($dbQuery,$dbConnect);
	if(!!$dbResult){
		krLog("dbQuery: OK");
		$most_recent = true;
		$rcids = array();
		$oldest_patrolled = 0;
		$last_unpatrolled = 0;
		$rcid_list = "<div id='rcid_list_anchor' style='display:none'><p style='text-align:center'><input class='rcid_startbutton' type='button' onclick='SPP_Init();' style='font-size:larger;margin:5px;padding:5px' value='Click here to start patrolling !' /></p><p class='rcid_list_status'></p><ul id='rcid_list' class='ns'>";
		foreach(mysql_fetch_all($dbResult) as $hit){
			if($most_recent){
				$rcids[] = $hit['rc_id'];
				$rcid_list .= "<li rcid=".$hit['rc_id'].">".$hit['rc_id']."</li>";
				$last_unpatrolled = $hit['rc_this_oldid'];
				$oldest_patrolled = empty($hit['rc_last_oldid']) ? $hit['rc_this_oldid'] : $hit['rc_last_oldid']; // if there is only 1 revision in total, take the previous revision as the oldest one
				$most_recent = false;
			} elseif($hit) {
				$rcids[] = $hit['rc_id'];
				$rcid_list .= "<li rcid=".$hit['rc_id'].">".$hit['rc_id']."</li>";
				$oldest_patrolled = empty($hit['rc_last_oldid']) ? $hit['rc_this_oldid'] : $hit['rc_last_oldid'];
				// fallback to rc_this_oldid if rc_last_oldid is empty (this is the case when the oldest unpatrolled entry is the page creation, there is no 'last' one before that
			}
		}
		$rcid_list .= "</ul></div>";
		$overal_difflink = "<h4>Step 1 : Compare</h4><a rel='shadowbox;title=Compare the overal difference between the last patrolled and the last unpatrolled revision' href='http://".$c['url'][$c['wiki']].".org/?oldid=$oldest_patrolled&diff=$last_unpatrolled'>Click here to view the <strong>difference between last patrolled and last unpatrolled revision</strong></a><br /><a rel='shadowbox;title=Compare the overal difference between the last patrolled and the current revision' href='http://".$c['url'][$c['wiki']].".org/?oldid=$oldest_patrolled&diff=curr'>Click here to view the <strong>difference between last patrolled and the current revision</strong></a><h4>Step 2 : Patrol</h4><a href='#rcid_list_anchor' rel='shadowbox;height=900'>Load the list</a><h4>Step 3 : Done !</h4><p>You're done ! Now close this window, <a onclick='history.back()'>go back</a> or check out <a href='getTopRCpages.php?wiki=".$c['wiki']."'>Get Top RC Pages</a>.";
		$output = ($c['wiki'] == "commonswiki" ? "<p style='text-align:right'><a href='http://commons.wikimedia.org/w/index.php?title=Special:Log&type=patrol&withJS=MediaWiki:Mypage.js' target='_blank'>Open your patrol log &raquo;</a></p>" : "")."<div class='msg'><p>&rArr; If you're not logged in and/or don't have the patrol-right the patrolling will fail (eventhough it may say, \"Done patrolling 20/20 contributions.\"<br /><small>(due to security restrictions the tool can't know wether or not you are a patroller)</small></p><hr /><p>Patrollers: Make sure that you're logged in on ".$c['url'][$c['wiki']]."! See below: </p></div>";
		$output .= "<iframe src='http://".$c['url'][$c['wiki']].".org/' width='100%' height='50'></iframe>";
		$output .= $overal_difflink;
		$output .= $rcid_list;
		$output .= "<script>window.SPP_RcidArray = ".json_encode($rcids).";</script>";
		if($oldest_patrolled == 0 || $last_unpatrolled == 0 || $most_recent == true){
			echo '<p class="error">There are no unpatrolled edits to the choosen page.</p>';
			//var_dump($GLOBALS);
		} else {
			echo $output;
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
				echo '<li>'.$rcpwiki.': '.$c['url'][$rcpwiki].'</li>';
			} ?>
			</ul>

		<h3 id="author">Author</h3>
			<p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail&middot;com</em>, or leave a message on the <a href="//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&amp;action=edit&amp;section=new&amp;preload=User_talk:Krinkle/Tools/Preload">Tools feedback page</a>.</p>
	</div>

<script>
Shadowbox.init({
	'overlayOpacity' : 0.7
});

var RcidLoader = [];
var RcidItem = "";
var RcidTotal = SPP_RcidArray.length;
var t = 0;
function SPP_Init(){
	$(".rcid_startbutton").attr("disabled", "disabled");
	SPP_RcidQueue = SPP_RcidArray.concat();
	t = RcidTotal - SPP_RcidQueue.length;
	$(".rcid_list_status").html("Prepating to patrol "+RcidTotal+" revisions...");
	SPP_Next();
}
function SPP_Next(){
	RcidItem = SPP_RcidQueue.shift();
	t = RcidTotal - SPP_RcidQueue.length;
	$(".rcid_list_status").html("Patrolling "+RcidTotal+" revisions... "+t+"/"+RcidTotal);
	if(RcidItem){
		RcidLoader[RcidItem] = new Image();
		RcidLoader[RcidItem].onload = function(){
			$("#rcid_list  li[rcid='"+RcidItem+"']").addClass("ok");
			SPP_Next();
		}
		RcidLoader[RcidItem].onerror = function(){
			$("#rcid_list  li[rcid='"+RcidItem+"']").addClass("ok");
			SPP_Next();
		}
		RcidLoader[RcidItem].src = "<?php echo "http://".$c['url'][$c['wiki']].".org"; ?>/?action=markpatrolled&rcid="+RcidItem;
	} else {
		$(".rcid_list_status").html("Done patrolling "+RcidTotal+" revisions !").addClass("ok");
	}
}
</script>
</body>
</html>
