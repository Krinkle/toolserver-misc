<?php
/**
 * input.php :: User may enter the settings
 *
 * @package MoreContributions
 * Created on August 30th, 2010
 *
 * Copyright © 2010 Krinkle <krinklemail@gmail.com>
 *
 * MoreContributions by Krinkle [1] is licensed under
 * a Creative Commons Attribution-Share Alike 3.0 Unported License [2]
 *
 * [1] commons.wikimedia.org/wiki/User:Krinkle
 * [2] creativecommons.org/licenses/by-sa/3.0/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
$c['pagetitle'] = 'input';
require_once 'header.php';

/**
 *  Get wikis
 * -------------------------------------------------
 */
$dbLinkSQL = @mysql_connect($dbServerSQL, $s['mysql_user'], $s['mysql_pwd']);
mysql_select_db('toolserver', $dbLinkSQL);
$dbReturn = mysql_query("SELECT * FROM wiki WHERE is_closed = 0", $dbLinkSQL);
$dbResults = mysql_fetch_all($dbReturn);
unset($dbReturn);
if (!$dbResults) {
	krDie('Wiki information acquirement failed.');
}
if ($dbLinkSQL) { mysql_close($dbLinkSQL); }

?>
		<h3 id="rawinput">Input</h3>
		<form id="editform" name="editform" method="get" class="ns colly" action="index.php">
			<fieldset>
				<legend>Settings</legend>

				<table>
				<tr>
					<td>
						<label for="username">Username:</label>
						<input type="text" id="username" name="username" value="<?=krEscapeHTML($_POST['username'])?>"/>
						<span>May also contain an asterisk to match multiple names<br />
							or a range of IP-addresses. Like "<em>JohnDo*</em>" or "<em>80.100.19*</em>".</span>
						<br />
					</td>
				</tr>
				<tr>
					<td>
						<label for="wikidb">Wiki:</label>
						<select id="wikidb" name="wikidb">
							<option value="">(all wikis)</em></option><?php
							//krEscapeHTML($_POST['wikidb'])
							$outputA = '';
							$outputB = '';
							$sel = ' selected="selected"';
							foreach($dbResults as $wiki){
								if( $wiki['dbname'] == 'enwiki_p' || $wiki['dbname'] == 'commonswiki_p' || $wiki['dbname'] == 'nlwiki_p' || $wiki['dbname'] == 'dewiki_p' ){
									$outputA .= '<option value="'.$wiki['dbname'].'" '.($_POST['wikidb'] == $wiki['dbname'] ? $sel : '').' >'.$wiki['dbname'].'</option>';
								} else {
									$outputB .= '<option value="'.$wiki['dbname'].'" '.($_POST['wikidb'] == $wiki['dbname'] ? $sel : '').' >'.$wiki['dbname'].'</option>';
								}

							}
							echo '<optgroup label="Most used wikis">'.$outputA.'</optgroup><optgroup label="All wikis alphabetically">'.$outputB.'</optgroup>';
							?></select><br />
					</td>
					<td>
						<label for="allwikis">All wikis:</label>
						<input type="checkbox" id="allwikis" name="allwikis" value="on" <?php echo $_POST['allwikis'] == 'on' ? 'checked="checked"' : ''; ?> />
						<br />
					</td>
				</tr>
				</table>

				<label></label>
				<input type="submit" name="submit" value="Go" />

			</fieldset>
		</form>
<script>
$(function () {
	$("#allwikis").click(function () {
		if ($(this).is(':checked')) {
			$("#wikidb").val('');
		}
	});
});
</script>
<?php require_once('footer.php'); ?>
