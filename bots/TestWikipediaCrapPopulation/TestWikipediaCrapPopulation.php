<?php

function flog( $msg = '', $origin = null ) {
	if ( is_string( $origin ) ) {
		echo "$origin> ";
	}
	switch ( gettype( $msg ) ) {
		case 'boolean':
			echo $msg ? 'true' : 'false';
			break;
		case 'integer':
		case 'double':
		case 'string':
			echo (string)$msg;
			break;
		case 'array':
		case 'object':
		case 'resource':
			echo print_r( $msg, true );
			break;
		case 'NULL':
			echo 'NULL';
			break;
		default:
			echo gettype( $msg ) . ' [?]';
	}
	echo "\n";
}

/**
 * @class TestWikipediaCrapPopulationScript
 */
class TestWikipediaCrapPopulationScript {

	static $VERSION = '0.3-20130102';

	/**
	 * @var stdClass
	 */
	protected $conf;

	/**
	 * @var string
	 */
	protected $cookieJar;

	/**
	 * @var bool
	 */
	protected $isGood = false;

	function __construct() {
		$this->conf = new stdClass();
		$this->configureWikiLogin();
		$this->configureWikiData();
	}

	/* Private methods */

	private function configureWikiLogin() {

		// Get login data
		$cnfLogin = parse_ini_file( 'login.cnf' );
		$this->conf->wikiUser = $cnfLogin['user'];
		$this->conf->wikiPass = $cnfLogin['password'];
	}

	private function configureWikiData() {
		$this->conf->wikiApi = 'http://test.wikipedia.org/w/api.php';
		$this->conf->wikiScript = 'http://test.wikipedia.org/w/index.php';
		$this->isGood = false;
		$this->conf->wikiTokens = array(
			'loginToken' => null,
			'editToken' => null,
		);
		$this->conf->wikiTemplates = array();
	}

	/* Public methods */

	/** @return string **/
	public function getFromHttpGet( $url = 0, $query = array() ) {
		if ( !is_string( $url ) || !is_array( $query ) ) {
			return false;
		}

		return file_get_contents( $url . '?' . http_build_query( $query ) );
	}

	/**
	 * @param Array $postdata
	 */
	public function getFromApiPost( $postdata ) {
		if ( !is_array( $postdata ) ) {
			return false;
		}

		$postdata['format'] = 'php';
		
		$ch = curl_init();
		$chError = false;
		curl_setopt_array( $ch, $this->conf->curlOpts );
		curl_setopt( $ch, CURLOPT_URL, $this->conf->wikiApi );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postdata ) );
		$result = @unserialize( curl_exec( $ch ) );

		if ( curl_errno( $ch ) ) {
			return false;
		}

		curl_close($ch);

		if ( !$result ) {
			return false;
		}
		
		return $result;
	}

	/** @return bool **/
	public function initConnection(){
		// User-Agent is required!
		$this->conf->userAgent = 'TestWikipediaCrapPopulation/' . self::$VERSION . ' (Wikimedia Toolserver; u=krinkle) Contact/krinkle@toolserver.org';

		// Cookie file for the session
		$this->cookieJar = tempnam( '/tmp', 'krinkle.curl.cookiejar.' );

		// Common cURL options to avoid repeating ourselfs (for API POST requests)
		$this->conf->curlOpts = array(
			CURLOPT_COOKIEFILE => $this->cookieJar,
			CURLOPT_COOKIEJAR => $this->cookieJar,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT =>$this->conf->userAgent,
			CURLOPT_POST => true,
		);

		// Common settings for file_get_contents (for simple GET requests)
		ini_set( 'user_agent', $this->conf->userAgent );

		// Login
		return (bool)$this->doWikiLogin();
	}

	/**
	 * API Login: https://www.mediawiki.org/wiki/API:Login
	 */
	public function doWikiLogin(){
		$postdataLogin = array(
			'action' => 'login',
			'lgname' => $this->conf->wikiUser,
			'lgpassword' => $this->conf->wikiPass,
		);
		
		$apiResult = $this->getFromApiPost( $postdataLogin );

		// Basic error check + Confirm token
		if ( !$apiResult ) {
			return false;
		}
		
		if ( $apiResult['login']['result'] == 'NeedToken' ) {

			if ( empty( $apiResult['login']['token'] ) ) {
				return false;
			} else {
				
				$this->conf->wikiTokens['loginToken'] = $apiResult['login']['token'];

				$postdataToken = array(
					'action' => 'login',
					'lgname' => $this->conf->wikiUser,
					'lgpassword' => $this->conf->wikiPass,
					'lgtoken' => $this->conf->wikiTokens['loginToken'],
				);
					
				$apiResult = $this->getFromApiPost( $postdataToken );
				
				if ( !$apiResult ) {
					return false;
				}
				if ( $apiResult['login']['result'] == 'Success' ) {
					$this->isGood = true;
					flog( 'wikiOK: true', __METHOD__ );
					return true;
				}
				flog( $apiResult, __METHOD__ );
				return false;

			}
		}

		$this->isGood = true;
		return true;
	}

	/** @return bool **/
	public function doWikiLogout(){
		$postdata = array(
			'action' => 'logout',
		);
		
		if ( $this->getFromApiPost( $postdata ) ) {
			$this->isGood = false;
			return true;
		} else {
			return false;
		}
	}

	/** @return bool **/
	public function closeConnection(){
		$this->doWikiLogout();
		unlink( $this->cookieJar );
		return true;
	}


	/** @return bool **/
	public function getEditToken(){
		$postdata = array(
			'action' => 'query',
			'prop' => 'info',
			'intoken' => 'edit',
			'titles' => 'User:KrinkleBot',	
		);
		$apiResult = $this->getFromApiPost( $postdata );
		$page = array_pop( $apiResult['query']['pages'] );
		$token = $page['edittoken'];
		if ( !empty( $token ) ) {
			$this->conf->wikiTokens['editToken'] = $token;
			return true;
		}
		$this->isGood = false;
		return false;
	}

	/** @return bool **/
	public function getTemplates(){
		if ( !$this->isGood ) {
			return false;
		}

		$crapPage = $this->requireAndUnwrapPre(
			$this->getFromHttpGet( $this->conf->wikiScript, array(
				'action' => 'raw',
				'title' => 'User:Krinkle/CrapTemplate',
			))
		);

		$crapSubCategory = $this->requireAndUnwrapPre(
			$this->getFromHttpGet( $this->conf->wikiScript, array(
				'action' => 'raw',
				'title' => 'User:Krinkle/CrapCategoryTemplate',
			))
		);

		$crapRootCategory = 
"This category will contain all '''crap''' generated by [[User:KrinkleBot]].

Templates:
* [[User:Krinkle/CrapTemplate]]
* [[User:Krinkle/CrapCategoryTemplate]]

Crap will automatically be re-created once a week.

Last run: ~~~~
";
		if ( !$crapPage || !$crapSubCategory ) {
			return false;
		}

		$this->conf->wikiTemplates = array(
			'crapPage' => $crapPage,
			'crapSubCategory' => $crapSubCategory,
			'crapRootCategory' => $crapRootCategory,
		);
		return true;
	}

	/** @return bool **/
	public function requireAndUnwrapPre( $wikitext = '' ) {
		$wikitext = trim( $wikitext );
		if ( substr( $wikitext, 0, 5 ) == '<pre>' && substr( $wikitext, -6 ) == '</pre>' ) {
			return substr( $wikitext, 5, -6 );
		} else {
			return false;
		}
	}

	/** @return bool **/
	public function editPage( $options = array() ) {
		if ( !is_array( $options )
		 || !isset( $options['title'] )
		 || !isset( $options['content'] )
		 || !isset( $options['summary'] )
		) {
			return false;
		}

		if ( !$this->isGood ) {
			return false;
		}

		$postdata = array(
			'action' => 'edit',
			'title' => $options['title'],
			'token' => $this->conf->wikiTokens['editToken'],
			'text' => $options['content'],
			'summary' => $options['summary'],
			'minor' => isset( $options['minor'] ),
			'bot' => isset( $options['bot'] ),
		);
		return $this->getFromApiPost( $postdata );
	}

	/** @return Array|bool **/
	public function populatePages(){
		if ( !$this->isGood ) {
			return false;
		}
		
		$apiActions = array();

		// Pages
		foreach ( range( 'A', 'C' ) as $letter ) {
			foreach ( range( 0, 9 ) as $number ) {
				$apiActions[] = $this->editPage( array(
					'title' => "User:Krinkle/Crap_$letter/$number",
					'content' => $this->conf->wikiTemplates['crapPage'],
					'summary' => "BOT: Resetting part $number of Crap $letter.",
					'minor' => 1,
					'bot' => 1,
				) );
			}
		}

		// Subcategories
		foreach ( range( 'A', 'C' ) as $letter ) {
			$apiActions[] = $this->editPage( array(
				'title' => "Category:Krinkle's Crap $letter",
				'content' => $this->conf->wikiTemplates['crapSubCategory'],
				'summary' => "BOT: Resetting Crap {$letter}'s subcategory page.",
				'minor' => 1,
				'bot' => 1,
			) );
		}

		// Root category
		$apiActions[] = $this->editPage( array(
			'title' => "Category:All crap",
			'content' => $this->conf->wikiTemplates['crapRootCategory'],
			'summary' => "BOT: Resetting the root crap category page.",
			'minor' => 1,
			'bot' => 1,
		) );

		return $apiActions;	
	}
}

/**
 * Config
 */
$script = new TestWikipediaCrapPopulationScript();

/**
 * Init
 */

flog( "\n-- Starting: " . date( 'r' ) );
flog(
	$script->initConnection(),
	'script->initConnection'
);
flog(
	$script->getTemplates(),
	'script->getTemplates'
);
flog(
	$script->getEditToken(),
	'script->getEditToken'
);

flog( "\nPopulating pages..." );

$edits = $script->populatePages();
flog(
	count( $edits ) . ' edits',
	'script->populatePages() count'
);
#flog(
#	$edits,
#	'script->populatePages'
#);

flog( "\nClosing..." );
$script->closeConnection();

flog( "\nDone: " . date( 'r' ) . "\n" );
