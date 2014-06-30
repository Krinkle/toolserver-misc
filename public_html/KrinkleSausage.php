<?php
/**
 * KrinkleSausage.php :: All-in-One file
 *
 * Created on May 11th, 2010
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */
$c['debug'] = isset( $_GET['debug'] );
/**
 *  Configuration
 * -------------------------------------------------
 */

session_start();
if($c['debug']){ error_reporting(-1); } else {error_reporting(0); }
date_default_timezone_set("UTC");
define("BR", "\n");

function CacheAndDefault($variable = false, $default = false, $cache = false){
	if ( !empty($variable) ) {
		return $variable;
	} elseif ( !empty($cache) ) {
		return $cache;
	} else {
		return $default;
	}
}
function wikiurl($s){
	return str_replace("%2F", "/", rawurlencode($s));
}
$is_submit = false;
$revID = "0.2.6";
$revDate = '2010-07-27';

$c['title'] = "Krinkle's Usage";
$c['baseurl'] = "http://krinkle-tools.grizzdesign.nl/KrinkleSausage.php";
$c['filenames'] = array(
"File:Krinkle_AjaxPatrolLinks.js",
"File:Krinkle_Global_SUL.js",
"File:Krinkle_CVNSimpleOverlay.js",
"File:Krinkle_CVNSimpleOverlay_wiki.js",
"File:Krinkle_CommonsCreatorLister.js",
"File:Krinkle_CommonsUploadPatrol.js",
"File:Krinkle_Countervandalism.js",
"File:Krinkle_InsertWikiEditorButton.js",
"File:Krinkle_OneClickCommoniser.js",
"File:Krinkle_PopCategoryDisplay.js",
"File:Krinkle_RTRCdev.js",
"File:Krinkle_SpecialAbuseLog_HistLink.js",
"File:Krinkle_TinEye.js",
"File:Krinkle_VectorSearchNav.js",
"File:Krinkle_Vector_LTR.js",
"File:Krinkle_WhatLeavesHere.js",
"File:Krinkle_addDeleteReasons.js",
"File:Krinkle_insertVectorButtons.js",
"File:Krinkle_mwEditsectionZero.js",
"File:Krinkle_toggleRevDel.js",
);
sort($c['filenames']);
array_unshift($c['filenames'], "File:Krinkle_RTRC.js")


?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<meta charset="utf-8">
<title>Krinkle - <?=$c['title']?></title>
<link rel="stylesheet" href="//toolserver.org/~krinkle/main.css?v=2"/>
<style>
	h4 { border-bottom:1px solid #DFDFDF }
	.box {display:block;background:rgb(249, 249, 249);background:rgba(245, 245, 245, 0.8);border:1px solid #DFDFDF;font-size:13px;font-weight:bold}
	.box img {margin:1px 1px 3px 1px}
	h3 b,h4 b,h5 b {font-weight:normal}
	#toc ul li ul li {font-weight:normal;font-size:10px;line-height:1}
	#toc li a.current {font-weight:bold}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
<script src="//toolserver.org/~krinkle/main.js?v=3"></script>
</head>
<body>
<div id="page-wrap">

	<div class="box" style="float:right;text-align:center"><a href="//commons.wikimedia.org/wiki/File:Bar-b-que-sausages.jpg" target="_blank"><img src="//upload.wikimedia.org/wikipedia/commons/thumb/b/be/Bar-b-que-sausages.jpg/222px-Bar-b-que-sausages.jpg" alt="" title="'Bar-B-Que sausages' (Photo by User:Salimfadhley / CC-BY-SA 3.0)"/></a><br/>Grilling sausages were used !<br/><small>( none were harmed&nbsp;<img src="//upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Face-tongue.svg/20px-Face-tongue.svg.png" alt="" title=":-P"/>)</small></div>
	<h1>Krinkle'<em><big>s</big></em><sup><em><big>a</big></em></sup><small>U</small>sage</h1>
	<small><em>Version <?=$revID?> as uploaded on <?=$revDate?> by Krinkle</em></small>
	<hr/>
	<div id="toc" style="position:fixed;top:260px;right:10px;padding:0px 8px 8px 20px;overflow:scroll;height:80%" class="box nonvisitedlinks">
	<h4>Table of Contents</h4>
	<ul>
		<li><a href="#perfile">Per script</a><ul id="toc-perfile"></ul></li>
		<li><a href="#perwiki">Per page</a><ul id="toc-perwiki"></ul></li>
	</ul>
	</div>

<?php
	if($c['debug']) {
		echo "Config:<br/><pre>";
		foreach($c as $key=>$val){ echo " ".$key.": "; print_r($val); echo "<br/>"; }
		echo "</pre>";
	}

	$a['cur'] = "";
	$a['perfileoutput'] = "";
	$a['totalfileusagecount']['perwiki'] = 0;
	$a['totalfileusagecount']['perfile'] = 0;
	//$a['perwiki']

	function Spit_perfile($filename){
		global $c, $a;
		$searchURL = "http://commons.wikimedia.org/w/api.php?format=php&action=query&prop=globalusage&gulimit=500&titles=".$filename;
		if($c['debug']) echo "Loading: ".htmlentities($searchURL)."<hr/><br/>";

		ini_set('user_agent', 'KrinkleTools/0.1; krinklemail [at] gmail [.] com');
		$search = file_get_contents($searchURL);
		$search = unserialize($search);
		if($c['debug']) print_r($search);

		foreach($search["query"]["pages"] as $page){
			$a['perfileoutput'] .= BR.'<h4 id="perfile_'.$page['title'].'">'.($page['title'])." <b>(".count($page['globalusage']).'x)</b></h4>'.BR;
			$a['perfileoutput'] .= '<script>$(function(){ $("#toc-perfile").append("<li><a href=\'#perfile_'.$page['title'].'\'>'.$page['title'].'</a></a>"); });</script><ul>'.BR;
			$a['totalfileusagecount']['perfile'] += count($page['globalusage']);
			foreach($page['globalusage'] as $hit){
				$a['perwiki'][$hit["wiki"]][$hit["title"]][] = $page['title'];

				if($a['cur'] !== $hit["wiki"]){
					$a['cur'] = $hit["wiki"];
					$a['perfileoutput'] .= '</ul>'.BR.'<h5 id="perfile_'.$page['title'].'_'.$a['cur'].'">'.$a['cur'].'</h5>'.BR.'<ul class="mw-collapsible mw-collapsed">'.BR;
					//$a['perfileoutput'] .= '<script>$(function(){ $("#toc-perfile").append("<li><a href=\'#perfile_'.$page['title'].'_'.$a['cur'].'\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$a['cur'].'</a>"); });</script>';
				}
				$a['perfileoutput'] .= "\t<li><a href='".$hit["url"]."' target='_blank'>".($hit["title"])."</a></li>\n";

			}
			$a['perfileoutput'] .= "</ul>";
		}
	}
	foreach($c['filenames'] as $filename){
		Spit_perfile($filename); // queries and outputs
	}

	echo "\n\n\n<h3 id='perfile'>Per script</h3><ul id='perfile-list'>\n";
		echo $a['perfileoutput'];

	echo "\n\n\n<h3 id='perwiki'>Per page</h3><ul id='perwiki-list'>\n";

	foreach($a['perwiki'] as $wikikey => $wikiarray){
		echo '</ul><h4 id="perwiki_'.$wikikey.'">'.$wikikey.' <b>('.count($wikiarray).'x)</b></h4><ul class="mw-collapsible mw-collapsed">'."\n";
		echo '<script>$(function(){ $("#toc-perwiki").append("<li><a href=\'#perwiki_'.$wikikey.'\'>'.$wikikey.'</a></li>"); });</script>'."\n";
		$a['totalfileusagecount']['perwiki'] += count($wikiarray);
		foreach($wikiarray as $titlekey => $filenames){
			echo "\t<li><a href='//".$wikikey."/wiki/".wikiurl($titlekey)."'>".$titlekey."</a> (<small> ";

			$filename_last = array_pop($filenames);
			if (empty($filenames)) echo str_replace("File:Krinkle ", "", $filename_last);
			else echo implode(', ', str_replace("File:Krinkle ", "", $filenames)).' and '.str_replace("File:Krinkle ", "", $filename_last);

			echo " </small>)</li>\n";
		}
	}

?></ul>

		<h3 id="author">Author</h3>
			<p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at"/>gmail&middot;com</em>, or leave a message on the <a href="//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload">Tools feedback page</a>.</p>


</div>
<a href="<?=$c['baseurl']?>" style="display: none;" id="home">Reload</a>
<span id="perwiki-total" style="display: none;"><?=$a['totalfileusagecount']['perwiki']?></span>
<span id="perfile-total" style="display: none;"><?=$a['totalfileusagecount']['perfile']?></span>
<script type="text/javascript">
$( '.mw-collapsible' ).each(function(){
	var $el = $(this),
		nr = $el.find('> li').size();
	if ( nr > 2 ) {
		$el.makeCollapsible();
		$el.find('> li:first').prepend( '<em>' + nr + ' items </em>&nbsp; &nbsp; ')
	}
});
$( '#toc' ).before(
	$( '<a href="#">Expand all</a>' ).click( function( e ) {
		e.preventDefault();
		$( '.mw-collapsible-toggle-collapsed').click().remove().size();
		$(this).closest( 'p' ).remove();
	} ).wrap( '<p/>' ).before( '&nbsp;[' ).after( ']&nbsp;' ).parent()
);
$("h3#perwiki").append(" <b>("+$("#perwiki-total").text()+")</b>");
$("h3#perfile").append(" <b>("+$("#perfile-total").text()+")</b>");
$lastTocTarget = false;
$("#toc li a").live('click', function(){
	if( $lastTocTarget ) {
		$lastTocTarget.removeClass('current');
	}
	$lastTocTarget = $(this);
	$(this).addClass('current');
});

</script>
</body>
</html><?php die(); ?>
