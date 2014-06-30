<?php
/**
 * header.php :: Configuration and HTML head
 *
 * Created on December 4th, 2010
 *
 * @package CommonsCreatorLister
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once '../CommonStuff.php';

$revID = '0.0.3';
$revDate = '2010-12-05';

$c['title'] = 'Commons Creator Lister';
$c['baseurl'] = 'http://toolserver.org/~krinkle/CommonsCreatorLister/';

/**
 *  Settings
 * -------------------------------------------------
 */
//$settings['wikidb'] = empty( $_REQUEST['wikidb'] ) ? 'commonswiki_p' : $_REQUEST['wikidb'];
$settings['wikidb'] = 'commonswiki_p'; // No support for other wikis yet
$settings['transclude-namespace'] = empty( $_REQUEST['transclude-namespace'] ) ? '100' : (int)$_REQUEST['transclude-namespace'];
$settings['transclude-name'] = empty( $_REQUEST['transclude-name'] ) ? '' : $_REQUEST['transclude-name'];

krLog( print_r( $settings, true ) );

// Permalink
$c['permalink'] = generatePermalink( $settings );

if ( $c['hide_html'] !== true ) :
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?></title>
	<link rel="stylesheet" href="../main.css">
	<?php krLoadjQuery(); ?>
</head>
<body>
	<div id="page-wrap">
		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em><?php if($c['permalink']) echo '| <a href="'.$c['permalink'].'">Permalink to results</a>';?> | <a href="<?php echo generatePermalink( $settings, $c['baseurl'] . 'input.php' ); ?>">Settings</a></small>
		<hr>

<?php
endif;
