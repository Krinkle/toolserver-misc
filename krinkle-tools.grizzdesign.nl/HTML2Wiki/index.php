<?php

	error_reporting(0);
	//error_reporting(E_ALL); //debug

	$is_submit = false;
	$revID = "0.1.8";
	$revDate = '2010-02-07 15:00';

	function aD(){
		echo "<pre>".$htmloutput."</pre>";die;
	}


	function writeLog($message){
		if ($message){
			echo '<p><strong style="color: red;">'.$message.'</strong></p>';
		} else {
			echo '<!-- writeLog message undefined-->';
		}
	}

/*
 *	strip_attributes()
 *
 *	@author	http://phpfunda.blogspot.com/2007/08/function-strip-attributes.html
 *
 *	@param	$msg	string				- The text you want to strip attributes from.
 *	@param 	$tag	string				- The tag you want to strip attributes fom (p, for instancee).
 *	@param	$attr	string (optional)	- An array with the name of the attributes you want to strip (leaving the rest intact).
 *										- If the array is empty, the function will strip 	all attributes.
 *	@param	$suffix	string (optional)	- An optional text to append to the tag. It may be a new attribute, for instance.
 *
 */
function strip_attributes($msg, $tag, $attr = false, $suffix = ""){
	$lengthfirst = 0;
	while (strstr(substr($msg, $lengthfirst), "<$tag ") != ""){
		$tag_start = $lengthfirst + strpos(substr($msg, $lengthfirst), "<$tag ");
		$partafterwith = substr($msg, $tag_start);
		$img = substr($partafterwith, 0, strpos($partafterwith, ">") + 1);
		$img = str_replace(" =", "=", $img);
		$out = "<$tag";
		for($i=0; $i < count($attr); $i++){
			if (empty($attr[$i])) {
				continue;
			}
			$long_val =
			(strpos($img, " ", strpos($img, $attr[$i] . "=")) === FALSE) ?
			strpos($img, ">", strpos($img, $attr[$i] . "=")) - (strpos($img, $attr[$i] . "=") + strlen($attr[$i]) + 1) :
			strpos($img, " ", strpos($img, $attr[$i] . "=")) - (strpos($img, $attr[$i] . "=") + strlen($attr[$i]) + 1);
			$val = substr($img, strpos($img, $attr[$i] . "=" ) + strlen($attr[$i]) + 1, $long_val);
			if (!empty($val)){
				$out .= " " . $attr[$i] . "=" . $val;
			}
		}
		if (!empty($suffix)) {
			$out .= " " . $suffix;
		}
		$out .= ">";
		$partafter = substr($partafterwith, strpos($partafterwith,">") + 1);
		$msg = substr($msg, 0, $tag_start). $out. $partafter;
		$lengthfirst = $tag_start + 3;
	}
	return $msg;
}

	// CHECK TO SEE IF THIS IS A SUBMIT
	if (isset($_POST['htmlinput'])){

		//
		// CONFIG
		//

			// Building a whitelist array with keys which will send through the form, no others would be accepted later on
			$whitelist = array('htmlinput', 'wikitable', 'cleaner', 'rmbreak', 'copy2clipboard');

			// Building an array with the $_POST-superglobal
			foreach ($_POST as $key=>$item){

				// Check if the value $key (fieldname from $_POST) can be found in the whitelisting array,
				// if not, die with a short message to the hacker
				if (!in_array($key, $whitelist)){
					writeLog('Unknown form fields');
					die('Hack-Attempt detected. Please use only the fields in the form');
				}
			}

		//
		// INITIALISE
		//
			//Get input and trim whitespace around
			$htmloutput = trim($_POST['htmlinput']);

			// Multi space to space
			$htmloutput = preg_replace('/\ +/',' ',$htmloutput);

			// Eliminate any space in front of tags
			$htmloutput = preg_replace('/\ +\</','<',$htmloutput);

			// Convert all tags to lowercase
			$htmloutput = preg_replace('/<(.+?)>/ies','strtolower("<\\1>")',$htmloutput);

		//
		// option :: cleaner
		//

			if ($_POST['cleaner'] == 'on'){

				$cleaner = "on";

				$htmloutput = strip_attributes($htmloutput, "table");
				$htmloutput = strip_attributes($htmloutput, "th");
				$htmloutput = strip_attributes($htmloutput, "td");
				$htmloutput = strip_attributes($htmloutput, "tr");

			} else {
				$cleaner = "off";
			}

		//
		// HTML2Wiki :: set variables
		//

			// Static replacements
			$newTableHTMLfirstHead_ = '<table>\r\n <tr>';
			$newTableHTMLfirstHead = '<table>\r\n<tr>';
			$newTableHTML = '<table>';						$newTableWiki = '{|';

			$newHeadHTML = '<th>';							$newHeadWiki = '! ';

			$newCellHTML = '<td>';							$newCellWiki = '| ';

			$newRowHTML_ = '\n<tr>';						$newRowWiki = '|-';
			$newRowHTML = '<tr>';							$newRowWiki = '|-';

			$closeTableHTML_ = '\r\n</table>';				$closeTableWiki = '|}';
			$closeTableHTML = '</table>';					$closeTableWiki = '|}';

			// To strip
			$endTagTH = '</th>';
			$endTagTD = '</td>';
			$endTagTR = '</tr>';
			$endTagLB = '\r\n\r\n';

		//
		// option :: wikitable
		//

			if ($_POST['wikitable'] == 'on'){

				$wikitable = "on";

				// Initialise wikitable
				$newTableWiki = '{| class="wikitable"';

				// strip style from heading
				$htmloutput = strip_attributes($htmloutput, "th");


				// convert grey-cells to heading
				$colorCel_efefef = '<td style=\"background:#efefef;\">';
				$htmloutput =  str_replace($colorCel_efefef, $newHeadWiki, $htmloutput);


			} else {
				$wikitable = "off";
			}

		//
		// optoin :: copy2clipboard
		//

			if ($_POST['copy2clipboard'] == 'on'){
				$copy2clipboard = "on";
			} else {
				$copy2clipboard = "off";
			}

		//
		// HTML2Wiki :: Convert attributes
		//

			$TDattrHTML = '/<th\ (.*?)\>/';
			$TDattrWiki = '! \\1| ';
			$htmloutput =  preg_replace($TDattrHTML, $TDattrWiki, $htmloutput);

			$TDattrHTML = '/<td\ (.*?)\>/';
			$TDattrWiki = '| \\1| ';
			$htmloutput =  preg_replace($TDattrHTML, $TDattrWiki, $htmloutput);

			$TDattrHTML = '/<tr\ (.*?)\>/';
			$TDattrWiki = '|- \\1 ';
			$htmloutput =  preg_replace($TDattrHTML, $TDattrWiki, $htmloutput);

		//
		// HTML2Wiki :: Do it !
		//

			// Replacements
			$htmloutput = str_replace($newTableHTMLfirstHead_, $newTableWiki, $htmloutput);
			$htmloutput = str_replace($newTableHTMLfirstHead, $newTableWiki, $htmloutput);
			$htmloutput = str_replace($newTableHTML, $newTableWiki, $htmloutput);

			$htmloutput = str_replace($newHeadHTML, $newHeadWiki, $htmloutput);

			$htmloutput = str_replace($newCellHTML, $newCellWiki, $htmloutput);

			$htmloutput = str_replace($newRowHTML_, $newRowWiki, $htmloutput);
			$htmloutput = str_replace($newRowHTML, $newRowWiki, $htmloutput);

			$htmloutput = str_replace($closeTableHTML_, $closeTableWiki, $htmloutput);
			$htmloutput = str_replace($closeTableHTML, $closeTableWiki, $htmloutput);
			// Strip
			$htmloutput = str_replace($endTagTH, '', $htmloutput);
			$htmloutput = str_replace($endTagTD, '', $htmloutput);
			$htmloutput = str_replace($endTagTR, '', $htmloutput);
			$htmloutput = str_replace($endTagLB, '', $htmloutput);

		//
		// option :: rmbreak
		//

			if ($_POST['rmbreak'] == 'on'){

				$rmbreak = "on";
				$htmloutput =  preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $htmloutput);

			} else {
				$rmbreak = "off";
			}

		// Set Succesfull
		$is_submit = true;

	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />

	<title>Krinkle - HTML2Wiki Tables</title>

	<link rel="stylesheet" href="style.css" type="text/css" media="all" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="ZeroClipboard.js"></script>
	<script type="text/javascript" src="html2wiki.php?copyJS=<?php echo $copy2clipboard; ?>"></script>
</head>
<body>
	<div id="copied-notice2"><div id="copied-notice"><img src="clipboard-64px.png" style="vertical-align:middle" alt="" />Copied to clipboard!</div></div>
	<div id="page-wrap">

		<h1><small>Krinkle</small> | HTML2Wiki Tables</h1>
		<small><em>Version <?php echo $revID; ?> as  uploaded on <?php echo $revDate; ?> by Krinkle</em></small>


		<div style="background-color:yellow;">
		</div>

		<?php if ($is_submit) {
			date_default_timezone_set('Europe/Amsterdam');
		?>
			<p class="msg thanks">Succesfully submitted the data on <?php echo date('l, d F Y - H:i'); ?><br />
			<a href="#Krinkle_HTML2Wiki">Do another one !</a></p>
			<p>Code (output) :</p>
			<p class="msg">	<?php
			if ($wikitable == "on") echo "Wikitable is <span class='thanks'>on</span>";
			else echo "Wikitable is <span class='error'>off</span>";
			?>
			<br /><?php
			if ($rmbreak == "on") echo "Remove empty lines is <span class='thanks'>on</span>";
			else echo "Remove empty lines is <span class='error'>off</span>";
			?>
			<br /><?php
			if ($cleaner == "on") echo "Style-stripper is <span class='thanks'>on</span>";
			else echo "Style-stripper is <span class='error'>off</span>";
			?>
			<br />
			<br /><?php /*
			if ($copy2clipboard == "on") {
				echo "Output <span class='thanks'>is copied</span> to clipboard !";
			}
			else {
				echo "<span style='font-weight: normal;'>Output is not copied to clipboard</span>";
			}
			*/ ?>
			</p>
			<div id="htmloutput-wrap">
				<pre id="htmloutput"><?php echo htmlentities(stripslashes($htmloutput), ENT_NOQUOTES, "UTF-8"); ?></pre>
			</div>
		<?php } ?>

		<form action="index.php" method="post" id="Krinkle_HTML2Wiki">

				<label for="htmlinput">Code (input) <span class="req">*</span> :</label>
				<input type="submit" value="Submit data!" />
				<textarea cols="100%" rows="40" id="htmlinput" name="htmlinput" class="required" minlength="2"><?php echo htmlentities(stripslashes($htmlinput), ENT_NOQUOTES, "UTF-8"); ?></textarea>
				<input type="checkbox" name="wikitable" <?php if (!($wikitable == "off")) echo 'checked="checked"'; ?> value="on" />
				<label for="wikitable">Convert to <code>class="wikitable"</code></label>
				<br />
				<input type="checkbox" name="cleaner" <?php if (!($cleaner == "off")) echo 'checked="checked"'; ?> value="on" />
				<label for="cleaner">Enable style-stripper (strips any style, class, or border attributes)</label>
				<br />
				<input type="checkbox" name="rmbreak" <?php if (!($rmbreak == "off")) echo 'checked="checked"'; ?> value="on" />
				<label for="rmbreak">Remove empty lines</label>
				<br />
				<br />
				<!-- <input type="checkbox" name="copy2clipboard" <?php if (!($copy2clipboard == "off")) echo 'checked="checked"'; ?> value="on" />
				<label for="copy2clipboard">Copy output to clipboard</label> -->
				<input type="hidden" name="copy2clipboard" value="on" />
				<br />
				<input type="submit" value="Submit data!" />

		</form>


		<h3 id="known-issues">Known Issues</h3>
		<ul>
			<li>Redundant <code>|-</code> stays on top of the table (doesn't hurt, but may be stripped)</li>
		</ul>


		<h3 id="whats-new">What's New ?</h3>
		<ul>
			<li>2010-01-16 - Added function to remember previous choosen options</li>
			<li>2010-01-16 - Added function-button to copy the output to clipboard.</li>
			<li>2010-01-15 - Added option "Remove emptylines" (rmbreak)</li>
			<li>2010-01-15 - Added option "Style-stripper" (cleaner)</li>
			<li>2010-01-15 - Added option "wikitable" (wikitable)</li>
			<li>2010-01-14 - Fixed issue with special characters (like &icirc; and &acute;)</li>
			<li>2010-01-14 - Added sample code</li>
			<li>2010-01-14 - Initial version</li>
		</ul>


		<h3 id="todos">Todo's</h3>
		<ul>
			<li>Add option for copy2clipboard when submitting. Output will be copied directly.</li>
			<li>AJAX submit</li>
		</ul>


		<h3 id="author">Author</h3>
		<p><a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0;margin:0 auto;display:block;" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /></a><span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type">HTML2Wiki</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://nl.wikipedia.org/wiki/Gebruiker:Krinkle" property="cc:attributionName" rel="cc:attributionURL">Krinkle</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share Alike 3.0 Unported License</a>.</p>
		<hr />
		<p>If you have any suggestions, bug reports or feature requests. Or questions regarding the re-use of this tool feel free to contact me at <em>krinklemail<img src="http://upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail&middot;com</em>, or leave a message at <a href="http://nl.wikipedia.org/w/index.php?title=Overleg_gebruiker:Krinkle&action=edit&section=new&preload=Overleg_gebruiker:Krinkle/newPreload&uselang=en&editintro=Overleg_gebruiker:Krinkle/EditnoticeENG">my Wikipedia Talk-page here</a>.</p>

		<div id="cc">
			<h4 id="attr-code">Attribution-code</h4>
			<code style="font-size: 80%;">
			&lt;a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"&gt;&lt;img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /&gt;&lt;/a&gt;&lt;br /&gt;&lt;span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type"&gt;HTML2Wiki&lt;/span&gt; by &lt;a xmlns:cc="http://creativecommons.org/ns#" href="http://nl.wikipedia.org/wiki/Gebruiker:Krinkle" property="cc:attributionName" rel="cc:attributionURL"&gt;Krinkle&lt;/a&gt; is licensed under a &lt;a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"&gt;Creative Commons Attribution-Share Alike 3.0 Unported License&lt;/a&gt;.&lt;br /&gt;Based on a work at &lt;a xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://krinkle-tools.grizzdesign.nl/HTML2Wiki/" rel="dc:source"&gt;krinkle-tools.grizzdesign.nl&lt;/a&gt;.&lt;br /&gt;Permissions beyond the scope of this license may be available at &lt;a xmlns:cc="http://creativecommons.org/ns#" href="http://krinkle-tools.grizzdesign.nl/HTML2Wiki/#author" rel="cc:morePermissions"&gt;http://krinkle-tools.grizzdesign.nl/HTML2Wiki/#author&lt;/a&gt;.
			</code>
			<h4 id="preview">Preview</h4>
			<div><a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /></a><br /><span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type">HTML2Wiki</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://nl.wikipedia.org/wiki/Gebruiker:Krinkle" property="cc:attributionName" rel="cc:attributionURL">Krinkle</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share Alike 3.0 Unported License</a>.<br />Based on a work at <a xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://krinkle-tools.grizzdesign.nl/HTML2Wiki/" rel="dc:source">krinkle-tools.grizzdesign.nl</a>.<br />Permissions beyond the scope of this license may be available at:<br /> <a xmlns:cc="http://creativecommons.org/ns#" href="http://krinkle-tools.grizzdesign.nl/HTML2Wiki/#author" rel="cc:morePermissions">http://krinkle-tools.grizzdesign.nl/HTML2Wiki/#author</a>.</div>
		</div>


		<h3 id="sample">Sample code</h3>
		<textarea cols="100%" rows="40">&lt;TABLE&gt;
&lt;TR&gt;
    &lt;TH STYLE=&quot;BACKGROUND:#EFEFEF;&quot;&gt;     multiple     spaces   &lt;/TH&gt;
    &lt;TH STYLE=&quot;BACKGROUND:#EFEFEF;&quot;&gt;Land&lt;/TH&gt;
    &lt;TH STYLE=&quot;BACKGROUND:#EFEFEF;&quot;&gt;Artiest(en)&lt;/TH&gt;
    &lt;TH STYLE=&quot;BACKGROUND:#EFEFEF;&quot;&gt;Lied&lt;/TH&gt;
    &lt;TH STYLE=&quot;BACKGROUND:#EFEFEF;&quot;&gt;Punten&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR STYLE=&quot;BACKGROUND:#FFD700;&quot;&gt;
    &lt;TD&gt;1&lt;/TH&gt;
    &lt;TD&gt;{{LU}}&lt;/TH&gt;
    &lt;TD&gt;[[Anne-Marie David]]&lt;/TH&gt;
    &lt;TD&gt;''Tu te reconna&icirc;tras''&lt;/TH&gt;
    &lt;TD&gt;129&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;2&lt;/TH&gt;
    &lt;TD&gt;{{ES-1939}}&lt;/TH&gt;
    &lt;TD&gt;[[Mocedades]]&lt;/TH&gt;
    &lt;TD&gt;''Eres t&uacute;''&lt;/TH&gt;
    &lt;TD&gt;125&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;3&lt;/TH&gt;
    &lt;TD&gt;{{GB}}&lt;/TH&gt;
    &lt;TD&gt;[[Cliff Richard]]&lt;/TH&gt;
    &lt;TD&gt;''Power to all our friends''&lt;/TH&gt;
    &lt;TD&gt;123&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;4&lt;/TH&gt;
    &lt;TD&gt;{{IL}}&lt;/TH&gt;
    &lt;TD&gt;[[Ilanit]]&lt;/TH&gt;
    &lt;TD&gt;''Ey-sham''&lt;/TH&gt;
    &lt;TD&gt;97&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;5&lt;/TH&gt;
    &lt;TD&gt;{{SE}}&lt;/TH&gt;
    &lt;TD&gt;[[Nova and The Dolls]]&lt;/TH&gt;
    &lt;TD&gt;''You're summer''&lt;/TH&gt;
    &lt;TD&gt;94&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;6&lt;/TH&gt;
    &lt;TD&gt;{{FI}}&lt;/TH&gt;
    &lt;TD&gt;[[Marion Rung]]&lt;/TH&gt;
    &lt;TD&gt;''Tom tom tom''&lt;/TH&gt;
    &lt;TD&gt;93&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;7&lt;/TH&gt;
    &lt;TD&gt;{{NO}}&lt;/TH&gt;
    &lt;TD&gt;[[Bendik Singers]]&lt;/TH&gt;
    &lt;TD&gt;''It's just a game''&lt;/TH&gt;
    &lt;TD&gt;89&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;8&lt;/TH&gt;
    &lt;TD&gt;{{WD-esf}}&lt;/TH&gt;
    &lt;TD&gt;[[Gitte H&eacute;nning|Gitte]]&lt;/TH&gt;
    &lt;TD&gt;''Junger Tag''&lt;/TH&gt;
    &lt;TD&gt;85&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;8&lt;/TH&gt;
    &lt;TD&gt;{{MC}}&lt;/TH&gt;
    &lt;TD&gt;[[Marie (zangeres)|Marie]]&lt;/TH&gt;
    &lt;TD&gt;''Un train qui part''&lt;/TH&gt;
    &lt;TD&gt;85&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;10&lt;/TH&gt;
    &lt;TD&gt;{{IE}}&lt;/TH&gt;
    &lt;TD&gt;[[Maxi]]&lt;/TH&gt;
    &lt;TD&gt;''Do I dream?''&lt;/TH&gt;
    &lt;TD&gt;80&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;10&lt;/TH&gt;
    &lt;TD&gt;{{PT}}&lt;/TH&gt;
    &lt;TD&gt;[[Fernando Tordo]]&lt;/TH&gt;
    &lt;TD&gt;''Tourada''&lt;/TH&gt;
    &lt;TD&gt;80&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;12&lt;/TH&gt;
    &lt;TD&gt;{{CH}}&lt;/TH&gt;
    &lt;TD&gt;[[Patrick Juvet]]&lt;/TH&gt;
    &lt;TD&gt;''Je vais me marier, Marie''&lt;/TH&gt;
    &lt;TD&gt;79&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;13&lt;/TH&gt;
    &lt;TD&gt;{{IT}}&lt;/TH&gt;
    &lt;TD&gt;[[Massimo Ranieri]]&lt;/TH&gt;
    &lt;TD&gt;''Chi sare con te''&lt;/TH&gt;
    &lt;TD&gt;74&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;14&lt;/TH&gt;
    &lt;TD&gt;{{NL}}&lt;/TH&gt;
    &lt;TD&gt;[[Ben Cramer]]&lt;/TH&gt;
    &lt;TD&gt;''De oude muzikant''&lt;/TH&gt;
    &lt;TD&gt;69&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;15&lt;/TH&gt;
    &lt;TD&gt;{{YU}}&lt;/TH&gt;
    &lt;TD&gt;[[Zdravko Colic|Zdravko &#268;oli&#263;]]&lt;/TH&gt;
    &lt;TD&gt;''Gori Vatra''&lt;/TH&gt;
    &lt;TD&gt;65&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;15&lt;/TH&gt;
    &lt;TD&gt;{{FR}}&lt;/TH&gt;
    &lt;TD&gt;[[Martine Cl&eacute;menceau]]&lt;/TH&gt;
    &lt;TD&gt;''Sans toi''&lt;/TH&gt;
    &lt;TD&gt;65&lt;/TH&gt;
&lt;/TR&gt;
&lt;TR&gt;
    &lt;TD&gt;17&lt;/TH&gt;
    &lt;TD&gt;{{BE}}&lt;/TH&gt;
    &lt;TD&gt;[[Nicole &amp; Hugo]]&lt;/TH&gt;
    &lt;TD&gt;''Baby baby''&lt;/TH&gt;
    &lt;TD&gt;58&lt;/TH&gt;
&lt;/TR&gt;
&lt;/TABLE&gt;
</textarea>

	</div>
<a href="./" style="display:none;" id="home">Reload</a>
</body>
</html>
