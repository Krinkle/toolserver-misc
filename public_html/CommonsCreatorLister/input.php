<?php
/**
 * input.php :: Input / front-end settings
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
require_once 'header.php';

/**
 *  Database connection
 * -------------------------------------------------
 */
$toolserver_mycnf = parse_ini_file( '/home/' . get_current_user() . '/.my.cnf' );
$dbConnect = mysql_connect( 'sql.toolserver.org', $toolserver_mycnf['user'], $toolserver_mycnf['password'] );
if ( !$dbConnect ) {
	die( 'dbConnect (SQL): ERROR: <br />' . mysql_error() );
} else {
	krLog( 'dbConnect (SQL): OK' );
}
mysql_select_db( 'toolserver', $dbConnect );
$dbReturn_wikis = mysql_query( "SELECT * FROM wiki WHERE is_closed = 0", $dbConnect );
$dbResults_wikis = mysql_fetch_all($dbReturn_wikis);
unset($dbReturn_wikis);
if (!$dbResults_wikis) {
	krDie('Wiki information retrieval failed.');
}
$nsn_wiki = $settings['wikidb'];
$dbReturn_nsn = mysql_query( "SELECT * FROM namespacename WHERE dbname = '" . sql_clean( $nsn_wiki ) . "' AND ns_type='primary' AND ns_id >= 0 ORDER BY ns_id ASC", $dbConnect );
$dbResults_nsn = mysql_fetch_all($dbReturn_nsn);
unset($dbReturn_nsn);
if (!$dbResults_nsn) {
	krDie('Namespace information retrieval failed.');
}
if ($dbConnect) { mysql_close($dbConnect); }

?>
		<h3 id="rawinput">Input</h3>
		<form id="editform" name="editform" method="post" class="ns colly" action="index.php">
			<fieldset>
				<legend>Settings</legend>

				<table>
				<?php /* No support for other wikis yet,
				<tr>
					<td>
						<label for="wikidb">Wiki:</label>
						<select id="wikidb" name="wikidb">
							<?php
							$outputA = '';
							$outputB = '';
							$sel = ' selected="selected"';
							foreach ( $dbResults_wikis as $wiki ) {
								if ( $wiki['dbname'] == 'commonswiki_p' ) {
									$outputA .= '<option value="' . $wiki['dbname'] . '" ' . ( $settings['wikidb'] == $wiki['dbname'] ? $sel : '' ) . ' >' . $wiki['dbname'] . '</option>';
								} else {
									$outputB .= '<option value="' . $wiki['dbname'] . '" ' . ( $settings['wikidb'] == $wiki['dbname'] ? $sel : '' ) . ' >' . $wiki['dbname'] . '</option>';
								}

							}
							echo $outputA.'<optgroup label="All wikis alphabetically">'.$outputB.'</optgroup>';
							?></select><br />
					</td>
				</tr>
				*/ ?>
				<tr>
					<td>
						<label for="transclude-namespace">Transclusion namespace:</label>
						<select id="transclude-namespace" name="transclude-namespace">
							<?php
							$output = '';
							$sel = ' selected="selected"';
							foreach ( $dbResults_nsn as $namespacename ) {
								if ( $namespacename['ns_id'] >= -1 ) {
									if ( empty( $namespacename['ns_name'] ) ) {
										$namespacename['ns_name'] = '(Main)';
									}
									$output .= '<option value="' . $namespacename['ns_id'].'" '.($settings['transclude-namespace'] == $namespacename['ns_id'] ? $sel : '' ) . ' >' . $namespacename['ns_name'] . '</option>';
								}

							}
							echo $output;
							?></select><br />
					</td>
					<td>
						<label for="transclude-name">Transclusion title:</label>
						<input type="text" name="transclude-name" id="transclude-name" value="<?=krEscapeHTML($settings['transclude-name'])?>" />
						<br />
					</td>
				</tr>
				</table>

				<label></label>
				<input type="submit" name="go" value="Go" />

			</fieldset>
		</form>
<script>
$(function () {
	$('#wikidb').change(function () {
		$('#editform').attr('action', 'input.php').submit();
	});
});
</script>
<?php
require_once 'footer.php');
