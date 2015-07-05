<?php
/**
 * wgLogo Usage
 *
 * @author Timo Tijhof, 2012
 * @package wmf-tool-wikilogos
 * @license http://krinkle.mit-license.org/
 */

/**
 * Configuration
 * -------------------------------------------------
 */
// BaseTool & Localization
require_once __DIR__ . '/../ts-krinkle-basetool/InitTool.php';

// Class for this tool
require_once __DIR__ . '/class.php';
$kgTool = new KrWgLogoUsage();

// Local settings
require_once __DIR__ . '/local.php';

$toolConfig = array(
	'displayTitle'     => 'wgLogoUsage',
	'remoteBasePath'   => $kgConf->getRemoteBase() . '/wgLogoUsage/',
	'localBasePath'    => __DIR__,
	'revisionId'       => '0.1.0',
	'revisionDate'     => '2012-06-21',
);

$kgBaseTool = BaseTool::newFromArray( $toolConfig );
$kgBaseTool->setSourceInfoGithub( 'Krinkle', 'wmf-tool-wikilogos', __DIR__ );

$kgBaseTool->doHtmlHead();
$kgBaseTool->doStartBodyWrapper();


/**
 * Setup
 * -------------------------------------------------
 */


/**
 * Output
 * -------------------------------------------------
 */
$kgBaseTool->addOut( 'Auto-protected gallery for Commons', 'h2' );
$kgBaseTool->addOut( $kgTool->getCommonsAutoprotectGallery(), 'pre' );
$kgBaseTool->addOut( 'Raw data by wiki', 'h2' );
$kgBaseTool->addOut( json_encode(
	$kgTool->getLogoFileNamesByHostWiki(),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
), 'pre' );


/**
 * Close up
 * -------------------------------------------------
 */
$kgBaseTool->flushMainOutput();
