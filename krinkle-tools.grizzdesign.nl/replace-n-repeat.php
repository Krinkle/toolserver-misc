<?php

	error_reporting(0);
	//error_reporting(E_ALL); //debug

	$is_submit = false;
	$revID = "0.0.2";
	$revDate = '2010-03-24';

	// SETTINGS
		$replaceTimes = 10;
		$splitter = !empty($_REQUEST['splitter']) ? stripslashes($_REQUEST['splitter']) : "\n";

	function spit($value){
		return htmlentities(stripslashes($value), ENT_NOQUOTES, "UTF-8");
	}

	// CHECK TO SEE IF THIS IS A SUBMIT
	if (isset($_REQUEST['input'])){

		$input = $_REQUEST['input'];
		$find = $_REQUEST['find'];

		for ($i = 1; $i <= $replaceTimes; $i++) {
			$replace[$i] = $_REQUEST['replace'.$i];
			if($replace[$i]) $output[$i] = str_replace($find, $replace[$i], $input);
		}

		// Set Succesfull
		$is_submit = true;

	}

?>
<!DOCTYPE html">
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Krinkle - Replace 'n' Repeat</title>

	<link rel="stylesheet" href="HTML2Wiki/style.css" type="text/css" media="all" />
</head>

<body>
	<div id="page-wrap">

		<h1><small>Krinkle</small> | Replace 'n' Repeat</h1>
		<small><em>Version <?php echo $revID; ?> as  uploaded on <?php echo $revDate; ?> by Krinkle</em></small>


		<?php if ($is_submit) {
		?>
			<p class="msg thanks">Succesfully submitted the data on <?php echo date('l, d F Y - H:i'); ?><br />
			<a href="#Krinkle_ReplaceNRepeat">Do another one !</a></p>
			<p>Code (output) :</p>
			<div id="output-wrap">
			<?php if($_REQUEST['combine'] == "on"){ ?>

					<label for="output">Output combined:</label>
					<textarea style="font-family: monospace;white-space: pre;" cols="100%" name="output<?php echo $piece ?>" id="output<?php echo $piece ?>" rows="20"><?php 		foreach($output as $piece => $value){
						echo spit($value).$splitter;
					} ?></textarea>

			<?php } else { ?>
					<?php foreach($output as $piece => $value){ ?>
						<label for="output<?php echo $piece ?>">Output #<?php echo $piece ?>:</label>
						<textarea style="font-family: monospace;white-space: pre;" cols="100%" name="output<?php echo $piece ?>" id="output<?php echo $piece ?>" rows="5"><?php echo spit($value); ?></textarea>
					<?php } ?>
			<?php } ?>
		</div>
		<?php } ?>

		<form action="replace-n-repeat.php" method="post" id="Krinkle_ReplaceNRepeat">

				<label for="input">Code (input) <span class="req">*</span> :</label>
				<input type="submit" value="Submit data!" />
				<textarea cols="100%" rows="10" id="input" name="input" class="required" minlength="2"><?php echo spit($input); ?></textarea>
				<label for="combine">Combine in 1 string:</label><input type="checkbox" name="combine" id="combine" value="on" <?php if($_REQUEST['combine']){ echo "checked='checked'";} ?> /><br /><label for="splitter">Splitter (if combined):</label><textarea cols="10" rows="3" style="display:inline-block" name="splitter" id="splitter"><?php echo spit($_REQUEST['splitter']) ?></textarea>default: (new line)
				<br />
				<label for="find">Find:</label><input type="text" name="find" id="find" value="<?php echo spit($_REQUEST['find']) ?>"/>
				<br />
				<?php for ($i = 1; $i <= $replaceTimes; $i++) { echo "<br /><label for='replace$i'>Replace #$i:</label><input type='text' name='replace$i' id='replace$i' value='".spit($_REQUEST['replace'.$i])."' />";} ?>
				<br />


		</form>
		<h3 id="todos">Todo's</h3>
		<ul>
			<li>More options toward 1-click generation for a sitematrix in wikicode (Input for subjectprefixes ("Houses in") and prefixes (location usually) and for the wrapping ([[:Category: ]] usually).) And generate a table from it. </li>
		</ul>

</body>
</html>
