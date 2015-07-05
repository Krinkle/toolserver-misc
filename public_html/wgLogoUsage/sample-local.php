<?php
$kgTool->setSettings(array(

	// Should point to a checkout of:
	// https://gerrit.wikimedia.org/r/p/operations/mediawiki-config.git
	//
	// This this tool is meant to be used in an environment where many tools are
	// ran all using a common repository checkout, this script will not update
	// the git repository. Instead it is expected that you have a script in
	// place already that periodically updates it (or update it manually when
	// you need etc.)
	'wmfConfigRepoDir' => '/path/to/operations-mediawiki-config',

));
