<?php
/**
 * index.php :: All-in-One file
 *
 * Wiki Info
 * Created on August 30th, 2010
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once '../CommonStuff.php';

$revID = '0.0.1';
$revDate = '2010-10-24';

$c['title'] = 'Wiki Info';
$c['baseurl'] = 'http://toolserver.org/~krinkle/WikiInfo/';

/**
 *  Settings
 * -------------------------------------------------
 */
// SQL servers
$dbServerSQL = 'sql';
$dbServerS1 = 'sql-s1-rr';
$dbServerS2 = 'sql-s2-rr';
$dbServerS3 = 'sql-s3-rr';
$dbServerS4 = 'sql-s4-rr';
$dbServerS5 = 'sql-s5-rr';
$dbServerS6 = 'sql-s6-rr';

// Login
$toolserver_mycnf = parse_ini_file('/home/'.get_current_user().'/.my.cnf');
$s['mysql_user'] = $toolserver_mycnf['user'];
$s['mysql_pwd'] = $toolserver_mycnf['password'];
unset($toolserver_mycnf);

krLog('-- Settings done');

/**
 * Functions
 * -------------------------------------------------
 */
function iw_multiarray_sort($a,$subkey,$order='ASC') {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	if(strtoupper($order) == 'ASC'){
		asort($b, SORT_NUMERIC);
	} else {
		arsort($b, SORT_NUMERIC);
	}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}


?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?></title>
	<link rel="stylesheet" href="../main.css">
	<style>
	#page-wrap { width:1000px }

	table.wikitable { width:100% }

	tr:hover td { background:white }

	#data-table td[date],
	#data-table tr.table-head td { white-space:nowrap }
	#data-table tr.closed { background:#DFDFDF }
	#data-table.search-results tr { display:none }
	#data-table.search-results .search-hit { display:table-row }

	#data-table.hide-closed .closed { display:none }
	</style>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script>
	jQuery(function ($) {
		$('body').addClass('JS');
	});
	</script>
</head>
<body>
	<div id="page-wrap">
		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
		<hr />

		<div id="switches" class="msg ns">
			<span class="link opt-toggle-closed">Show closed wikis</span>
			| <label for="search-input">Search: </label>
				<form style="display:inline">
				<input type="text" name="search-input" id="search-input" size="24" />
				<input type="submit" id="search-go" nof value="Go" />
				<input type="button" id="search-clear" nof value="Reset" />
				</form>
		<img src="//upload.wikimedia.org/wikipedia/commons/4/42/Loading.gif" width="18" height="18" alt="" title="Loading..." id="search-load" style="float:right;display:none" />
		</div>
		<h3 id="data">Data</h3>
			<p class="note"><em>Newest wikis are on top</em></p>
		<?php
/**
 *  MySQL Links Connect
 * -------------------------------------------------
 */
$dbLinkSQL = @mysql_connect($dbServerSQL, $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS1  = @mysql_connect($dbServerS1,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS2  = @mysql_connect($dbServerS2,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS3  = @mysql_connect($dbServerS3,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS4  = @mysql_connect($dbServerS4,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS5  = @mysql_connect($dbServerS5,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS6  = @mysql_connect($dbServerS6,  $s['mysql_user'], $s['mysql_pwd']);

if( !$dbLinkSQL AND !$dbLinkS1 AND !$dbLinkS2 AND !$dbLinkS3 ){
	krDie('Error in connecting to database. Please try again later.');
} elseif( !$dbLinkSQL OR !$dbLinkS1 OR !$dbLinkS2 OR !$dbLinkS3 ){
	krError('Connection failed to one or more server. Some wikis may be hidden from the results.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Crystal_Clear_app_error.png/45px-Crystal_Clear_app_error.png', 'msg ns');
}
krLog('-- MySQL Links done');

/**
 *  Get wikis
 * -------------------------------------------------
 */
mysql_select_db('toolserver', $dbLinkSQL);
$dbReturn = mysql_query("
	SELECT
		dbname,
		domain,
		lang,
		family,
		size,
		is_closed,
		server
	FROM wiki
	;
", $dbLinkSQL);
$dbResults = mysql_fetch_all($dbReturn);unset($dbReturn);
if(!$dbResults){
	krDie('Wiki information acquirement failed.');
}


/**
 *  Interate over wikis
 * -------------------------------------------------
 */
$dbActiveLink = false;
$dbAllWikiResults = array();
foreach($dbResults as $wiki){
/* $wiki = Array(
    [dbname] => abwiki_p
    [lang] => ab
    [family] => wikipedia
    [domain] => ab.wikipedia.org
    [size] => 518
    [is_meta] => 0
    [is_closed] => 0
    [is_multilang] => 0
    [is_sensitive] => 0
    [root_category] =>
    [server] => 3
    [script_path] => /w/
)*/
	// Get the right server link
	switch($wiki['server']){
		case '1':
			$dbActiveLink = $dbLinkS1;
			break;
		case '2':
			$dbActiveLink = $dbLinkS2;
			break;
		case '3':
			$dbActiveLink = $dbLinkS3;
			break;
		case '4':
			$dbActiveLink = $dbLinkS4;
			break;
		case '5':
			$dbActiveLink = $dbLinkS5;
			break;
		case '6':
			$dbActiveLink = $dbLinkS6;
			break;
		default:
			$dbActiveLink = false;
			break;
	}

	// If we got a link, continue
	if(!!$dbActiveLink){

		// Get latest edit
		mysql_select_db($wiki['dbname'], $dbActiveLink) or krErrorLine('Database error for '.$wiki['dbname']);
		$dbQuery = " /* LIMIT:10 */
			SELECT
				rc_timestamp,
				rc_this_oldid
			FROM recentchanges
			ORDER BY rc_timestamp DESC
			LIMIT 0,1;
			";

		$dbReturn = mysql_query($dbQuery, $dbActiveLink);
		$dbResult = mysql_fetch_all($dbReturn);unset($dbReturn);
		$wiki['rc_timestamp'] = $dbResult[0]['rc_timestamp'];
		$wiki['rc_this_oldid'] = $dbResult[0]['rc_this_oldid'];

		// Get oldest edit
		$dbQuery = " /* LIMIT:10 */
			SELECT
				rev_id,
				rev_timestamp
			FROM revision
			ORDER BY rev_id ASC
			LIMIT 0,1;
			";

		$dbReturn = mysql_query($dbQuery, $dbActiveLink);
		$dbResult = mysql_fetch_all($dbReturn);unset($dbReturn);
		$wiki['rev_timestamp'] = $dbResult[0]['rev_timestamp'];
		$wiki['rev_id'] = $dbResult[0]['rev_id'];unset($dbResult);

		unset($wiki['server']);
 		$dbAllWikiResults[] = $wiki;

	} else {
		//krLog("No active database link found for ".$wiki['dbname']);
	}
}


/**
 *  Interate over results
 * -------------------------------------------------
 */
//krLog(krEscapeHTML(print_r($dbResult, true)));
//krLog(krEscapeHTML(print_r($dbResult2, true)));
//krLog(krEscapeHTML(print_r($dbAllWikiResults, true)));
$dbAllWikiResults = iw_multiarray_sort($dbAllWikiResults, 'rev_timestamp', 'DESC');

echo '<table class="wikitable sortable hide-closed" id="data-table">';
echo '<tr style="display:table-row !important" class="table-head">';
		echo '<th>Wiki</th>';
		echo '<th>Lang</th>';
		echo '<th>Family</th>';
		echo '<th>Size</th>';
		echo '<th class="closed">Status</th>';
		echo '<th>Most recent edit</th>';
		echo '<th>First edit</th>';
echo "</tr>\n";
foreach($dbAllWikiResults as $result){
	echo '<tr'.($result['is_closed'] == '1' ? ' class="closed"' : '').'>';
		echo '<td><a target="_blank" href="//';
			if( empty($result['domain']) ){
				$result['domain'] = $result['lang'].'.'.$result['family'].'.org';
				echo $result['domain'];
			} else {
				echo $result['domain'];
			}
			echo '">'.($result['domain'] ? $result['domain'] : $result['dbname']).'</a></td>';
		echo '<td>'.$result['lang'].'</td>';
		echo '<td>'.$result['family'].'</td>';
		echo '<td><a target="_blank" href="//'.$result['domain'].'/wiki/Special:Statistics">'.$result['size'].'</a></td>';
		echo '<td class="closed">'.($result['is_closed'] == '0' ? 'open' : 'closed').'</td>';
		echo '<td date><a target="_blank" href="//'.$result['domain'].'/?diff='.$result['rc_this_oldid'].'">'.(!empty($result['rc_timestamp']) ? date('Y-m-d H:i:s',strtotime($result['rc_timestamp'])).' <sup>&raquo;</sup>' : '?').'</a></td>';
		echo '<td date><a target="_blank" href="//'.$result['domain'].'/?diff='.$result['rev_id'].'">'.(!empty($result['rev_timestamp']) ? date('Y-m-d H:i:s',strtotime($result['rev_timestamp'])).' <sup>&raquo;</sup>' : '?').'</a></td>';
	echo "</tr>\n";
}
if(empty($dbAllWikiResults)){
	echo '<div class="item"><em>Nothing found.</em></div>';
}
echo '</table>';


/**
 *  MySQL Links Close
 * -------------------------------------------------
 */
if($dbLinkSQL){ mysql_close($dbLinkSQL);}
if($dbLinkS1) { mysql_close($dbLinkS1); }
if($dbLinkS2) { mysql_close($dbLinkS2); }
if($dbLinkS3) { mysql_close($dbLinkS3); }
if($dbLinkS4) { mysql_close($dbLinkS4); }
if($dbLinkS5) { mysql_close($dbLinkS5); }
if($dbLinkS6) { mysql_close($dbLinkS6); }
krLog('-- MySQL Links closed');

?>

		<h3 id="author">Author</h3>
			<p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail&middot;com</em>, or leave a message on the <a href="http://meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload">Tools feedback page</a>.</p>
	</div>
<script>
/**
 *  Utility functions
 * -------------------------------------------------
 */
	// Returns all GET-parameters as array
	function krParseUrlParams(l) {
		var url = l ? l : document.location.href;
		var match = url.match(/\?[^#]*/);
		if (match === null) return null;
		var query = match[0];
		var ret = {};
		var pattern = /[&?]([^&=]*)=?([^&]*)/g;
		match = pattern.exec(query);
		for (; match !== null; match = pattern.exec(query)) {
			var key = decodeURIComponent(match[1]);
			var value = decodeURIComponent(match[2]);
			ret[key] = value;
		}
		return ret;
	}
	// Check if a variable is 'empty'
	function krEmpty(v){
		var key;

		if (v === "" || v === 0 || v === "0" || v === null || v === false || typeof v === 'undefined'){
			return true;
		}

		if (typeof v == 'object'){
			for (key in v){
				return false;
			}
			return true;
		}

		return false;
	}

/**
 *  When the DOM is ready...
 * -------------------------------------------------
 */
jQuery(function ($) {
	// Show / Only #cvn-sw
	$('#switches .opt-toggle-closed').click(function(){
		if( $(this).text() == 'Hide closed wikis' ){
			$(this).text('Show closed wikis');
			$("#data-table").addClass('hide-closed');
		} else {
			$(this).text('Hide closed wikis');
			$("#data-table").removeClass('hide-closed');
			$('#data + .note').remove();
			$("#data-table tr").eq(0).find('.sortheader').eq(4).click();
		}
	});

	// Search
	sPhrase = '';
	$sLoader = $("#search-loader");
	window.DataTableReset = function(){
		$("#data-table").removeClass('search-results');
		if(typeof $sMatches !== 'undefined'){ $sMatches.removeClass('search-hit'); }
	}
	window.DataTableSearch = function(v, loop){
		DataTableReset();
		if( v === '' || v === 0 || v === '0' || v === null || v === false || typeof v === 'undefined' ){
			v = $("#search-input").val(); v = v.trim();
			if(!loop){
				DataTableSearch(v, true);
				return false;
			}
			DataTableReset();
			return false;
		} else {
			sPhrase = v.replace(/'|"/g, '').trim();
		}
		$sLoader.show();

		$sMatches = $("td:contains('"+sPhrase+"')").parent();
		$sMatches.addClass('search-hit');
		$("#data-table").addClass('search-results');
		$sLoader.hide();
		return true;
	}
	$("#search-go").click(function(){
		DataTableSearch();
		return false;
	});
	$("#search-clear").click(function(){
		DataTableReset();
		$("#search-input").val('');
	});


	// URL actions
	params = krParseUrlParams();
	if( !krEmpty(params) ){

		// Search
		if( !krEmpty(params['q']) ){
			$("#search-input").val(params['q']);
			$("#search-go").click();
		}

		// Hide #cvn-sw
		if( params['hide_closed'] == '0' ){
			$('#switches .opt-toggle-closed').click();
			$("#data-table tr").eq(0).find('.sortheader').eq(-1).click();
		}
	}

	// Remove note if manually sorted
	$('.sortarrow').click(function(){
		$('#data + .note').remove();
		$('.sortarrow').unbind('click');
	});
});
</script>
<!-- <script src="//toolserver.org/~vvv/sortable.js"></script> -->
<script src="sortable.js"></script>
<pre><?php krLogFlush(); ?></pre>
</body>
</html>
