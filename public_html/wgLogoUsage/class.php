<?php
/**
 * class.php
 *
 * @package wmf-tool-wikilogos
 */
class KrWgLogoUsage extends KrToolBaseClass {

	protected $settingsKeys = array(
		'wmfConfigRepoDir',
	);

	/**
	 * @return bool|array: Boolean false on failure or an array with wgLogo values,
	 * keyed by wikidb.
	 */
	protected function getWgLogoValues() {
		$cluster = 'sandbox';
		$wgConf = new stdClass();
		$repo = $this->getSetting( 'wmfConfigRepoDir' );
		$initSettingsFile = $repo . '/wmf-config/InitialiseSettings.php';
		if ( !is_readable( $initSettingsFile ) ) {
			return false;
		}
		// Surpress E_NOTICE about NS_MAIN, NS_TEMPLATE etc.
		@require($initSettingsFile);
		if ( !isset( $wgConf->settings ) || !isset( $wgConf->settings['wgLogo'] ) ) {
			return false;
		}

		return $wgConf->settings['wgLogo'];
	}

	public function getLogoFileNamesByHostWiki() {
		$logos = $this->getWgLogoValues();
		if ( !$logos ) {
			return false;
		}
		// Format: https://upload.wikimedia.org/<project>/<site>/<hash 1>/<has 1-2>/<filename>
		// Example: https://upload.wikimedia.org/wikipedia/commons/a/a9/Example.jpg
		$wikiFullPattern = '#^'
			. preg_quote('//upload.wikimedia.org/')
			. '([^/]+)'
			. preg_quote('/')
			. '([^/]+)'
			. preg_quote('/')
			. '[^/]{1}'
			. preg_quote('/')
			. '[^/]{2}'
			. preg_quote('/')
			. '([^/]+)'
			. '$#';
		// Format: https://upload.wikimedia.org/<project>/<site>/thumb/<hash 1>/<has 1-2>/<filename>/<thumbname>
		// Example: https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Example.jpg/116px-Example.jpg
		$wikiThumbPattern = '#^'
			. preg_quote('//upload.wikimedia.org/')
			. '([^/]+)'
			. preg_quote('/')
			. '([^/]+)'
			. preg_quote('/thumb/')
			. '[^/]{1}'
			. preg_quote('/')
			. '[^/]{2}'
			. preg_quote('/')
			. '([^/]+)'
			. '$#';
		// $1: project, $2: site, $3: filename

		$filesByWiki = array();

		foreach ( $logos as $logo ) {
			$m = null;
			if ( preg_match( $wikiThumbPattern, $logo, $m ) || preg_match( $wikiFullPattern, $logo, $m ) ) {
				$filesByWiki[$m[1] . '/' . $m[2]][] = $m[3];
			}
		}

		return $filesByWiki;
	}

	public function getCommonsAutoprotectGallery() {
		$filesByWiki = $this->getLogoFileNamesByHostWiki();
		$wikitext = '{{Auto-protected files gallery}}<gallery widths="80" heights="80">';
		if ( isset( $filesByWiki['wikipedia/commons'] ) ) {
			foreach ( $filesByWiki['wikipedia/commons'] as $filename ) {
				$wikitext .= "\nFile:$filename";
			}
		}
		$wikitext .= "\n</gallery>";
		return $wikitext;
	}

}
