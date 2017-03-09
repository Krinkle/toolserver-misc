<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Krinkle - MediaWiki / Sanitizer::escapeClass</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>
	<div id="page-wrap">
		<h1><small>Krinkle</small> | escapeClass</h1>
		<small><em>Version 0.0.3 as  uploaded on 2010-05-10 12:15 by Krinkle</em></small>

<?php
if(!empty($_REQUEST['input'])){?>
		<form action="" method="post">
			<h3>Input</h3>
			<textarea id="input" name="input" rows="20" cols="80"><?php echo htmlspecialchars($_REQUEST['input']); ?></textarea>

			<h3>Output</h3>
			<textarea id="output" name="output" rows="20" cols="80"><?php
				echo htmlspecialchars(rtrim(
					preg_replace(
						array('/(^[0-9\\-])|[\\x00-\\x20!"#$%&\'()*+,.\\/:;<=>?@[\\]^`{|}~]|\\xC2\\xA0/','/_+/'),
						'_',
						$_REQUEST['input']
					),
					'_'
				)); ?></textarea>
			<br>
			<input type="submit" value="Submit!">
		</form>
<?php } else {?>
		<form action="" method="post">
			<h3>Input:</h3>
			<textarea id="input" name="input" rows="20" cols="80"></textarea>
			<br>
			<input type="submit" value="Submit!">
		</form>
<?php } ?>
	</div>
</body>
</html>
