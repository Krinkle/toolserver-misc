<?php
/**
 * SpeedUserPatrol.php :: All-in-One file
 *
 * SpeedUserPatrol
 * Created on August 29th, 2010
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$is_submit = !empty($_GET['user']) ? true : false;
$revID = "EXPERIMENTAL";
$revDate = '2008-08-29';

$c['title'] = "SpeedUserPatrol";
$c['baseurl'] = "//toolserver.org/~krinkle/SpeedUserPatrol.php";
$_GET['namespace'] = (int)trim($_GET['namespace']);
$c['wiki'] = CacheAndDefault( getParamVar( 'wiki' ), 'commonswiki' );


if ( in_array( $c['wiki'], $c['wikis_all'] ) ) {
	if ( $is_submit ) {

		$c['user'] = trim( $_GET['user'] );
	}
} else {
	die("Error: Wiki not found.");
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script src="shadowbox/shadowbox.js"></script>
	<link rel="stylesheet" href="shadowbox/shadowbox.css">
	<link rel="stylesheet" href="main.css">
	<title>Krinkle | <?=$c['title']?> - <?php echo $c['url'][$c['wiki']]; ?> / <?php echo $c['user']; ?></title>
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
		<small><em>Version <span class="error"><?=$revID?></span> as  uploaded on <?=$revDate?> by Krinkle</em></small>
		<hr><?php echo $c['nav']; ?><hr>
<?php
if($is_submit){ // if submitted:
?>
		<h3 id="result">SpeedUserPatrol: <?php echo $c['user']; ?> on <?php echo $c['url'][$c['wiki']]; ?></h3>
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
	$dbQuery = " /* LIMIT:20 */ /* SpeedUserPatrol( {$c['wiki']} ) */
	SELECT

		rc_id,
		rc_this_oldid,
		rc_last_oldid,
		rc_timestamp as date

	FROM recentchanges
	WHERE (rc_type = 0 OR rc_type = 1)
	AND rc_patrolled != 1
	AND rc_user_text = '" . mysql_real_escape_string( $c['user'] ) . "'
	ORDER BY date DESC
	LIMIT 500
";
	krLog("dbQuery: \n".$dbQuery);
	$dbResult = mysql_query($dbQuery,$dbConnect);
	if(!!$dbResult){
		krLog("dbQuery: OK");
		$most_recent = true;
		$rcids = array();
		$oldest_patrolled = 0;
		$last_unpatrolled = 0;
		$rcid_list = "<div id='rcid_list_anchor' style='display:none'><p style='text-align:center'><input class='rcid_startbutton' type='button' onclick='SUP_Init();' style='font-size:larger;margin:5px;padding:5px' value='Click here to start patrolling !' /></p><p class='rcid_list_status'></p><ul id='rcid_list' class='ns'>";
		foreach(mysql_fetch_all($dbResult) as $hit){
			if($most_recent){
				$rcids[] = $hit['rc_id'];
				$rcid_list .= "<li rcid=".$hit['rc_id'].">".$hit['rc_id']."</li>";
				$last_unpatrolled = $hit['rc_this_oldid'];
				$oldest_patrolled = empty($hit['rc_last_oldid']) ? $hit['rc_this_oldid'] : $hit['rc_last_oldid']; // save first one as sample
				$most_recent = false;
			} elseif($hit) {
				$rcids[] = $hit['rc_id'];
				$rcid_list .= "<li rcid=".$hit['rc_id'].">".$hit['rc_id']."</li>";
			}
		}
		$rcid_list .= "</ul></div>";
		$overal_difflink = "<h4>Step 1 : Check sample</h4><a rel='shadowbox;title=Check an unpatrolled sample' href='//".$c['url'][$c['wiki']].".org/?oldid=$oldest_patrolled&diff=$last_unpatrolled'>Click here to view a sample</a><h4>Step 2 : Patrol</h4><a href='#rcid_list_anchor' id='step_2_patrol_loadlink' rel='shadowbox;height=900'>Load the list</a><h4>Step 3 : Done !</h4><p>You're done ! Now close this window, <a onclick='history.back()'>go back</a> or check out <a href='getTopRCpages.php?wiki=".$c['wiki']."'>Get Top RC Pages</a>.";
		$output = "<p>Patrollers: Make sure that you're logged in on ".$c['url'][$c['wiki']]."! See below: ".($c['wiki'] == "commonswiki" ? "(<a href='//commons.wikimedia.org/w/index.php?title=Special:Log&type=patrol&withJS=MediaWiki:Mypage.js' target='_blank'>Open your patrol log &raquo;</a>)" : "")."</p>";
		$output .= "<iframe src='//".$c['url'][$c['wiki']].".org/wiki/Special:Preferences' width='100%' height='50'></iframe>";
		$output .= $overal_difflink;
		$output .= $rcid_list;
		$output .= "<script>window.SUP_RcidArray = ".json_encode($rcids).";</script>";
		if($oldest_patrolled == 0 || $last_unpatrolled == 0 || $most_recent == true){
			echo '<p class="error">There are no unpatrolled edits to the choosen page.</p><!--';
			var_dump($GLOBALS);
			echo "-->";
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
		<h3 id="result">wiki and/or user undefined</h3>
			<p>In order to use the SpeedUserPatrol tool you need to pre-define the two above mentioned settings.</p>


<?php } //endif submited
if( krDebug() ){
	echo "<hr /><pre>## DEBUG:\n\n".$krSandbox['log']."</pre><hr id='debug' />";
}

?>
		<h3 id="wikilist">List of supported wikis</h3>
			<ul><?php foreach($c['wikis_all'] as $wiki){
				echo '<li>'.$wiki.': '.$c['url'][$wiki].'</li>';
			} ?>
			</ul>

		<h3 id="author">Author</h3>
			<p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail&middot;com</em>, or leave a message on the <a href="//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload">Tools feedback page</a>.</p>
	</div>

<script>
Shadowbox.init({
	'overlayOpacity' : 0.7
});

var RcidLoader = [];
var RcidItem = "";
var RcidTotal = SUP_RcidArray.length;
var t = 0;
function SUP_Init(){
	$(".rcid_startbutton").attr("disabled", "disabled");
	SUP_RcidQueue = SUP_RcidArray.concat();
	t = RcidTotal - SUP_RcidQueue.length;
	$(".rcid_list_status").html("Prepating to patrol "+RcidTotal+" revisions...");
	SUP_Next();
}
function SUP_Next(){
	RcidItem = SUP_RcidQueue.shift();
	t = RcidTotal - SUP_RcidQueue.length;
	$(".rcid_list_status").html("Patrolling "+RcidTotal+" revisions... "+t+"/"+RcidTotal);
	if(RcidItem){
		RcidLoader[RcidItem] = new Image();
		RcidLoader[RcidItem].onload = function(){
			$("#rcid_list  li[rcid='"+RcidItem+"']").addClass("ok");
			SUP_Next();
		}
		RcidLoader[RcidItem].onerror = function(){
			$("#rcid_list  li[rcid='"+RcidItem+"']").addClass("ok");
			SUP_Next();
		}
		RcidLoader[RcidItem].src = "<?php echo "http://".$c['url'][$c['wiki']].".org"; ?>/?action=markpatrolled&rcid="+RcidItem;
	} else {
		$(".rcid_list_status").html("Done patrolling "+RcidTotal+" revisions !").addClass("ok");
	}
}
</script>
</body>
</html>
