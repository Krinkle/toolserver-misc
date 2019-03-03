# Krinkle's Toolserver memorial

* [CommonsCreatorLister](#commonscreatorlister)
* [CommonsMoveReview](#commonsmovereview)
* [CommonsUploadPatrol](#commonsuploadpatrol)
* [Get Top RC Pages](#get-top-rc-pages)
* [Get Top RC Users](#get-top-rc-users)
* [getWikiAPI](#getwikiapi)
* [getWMML](#getwmml)
* [InterfaceFiles](#interfacefiles)
* [Krinkle's Usage](#krinkles-usage)
* [MoreContributions](#morecontributions)
* [MoreWatchlists](#morewatchlists)
* [mwDatabaseSchema](#mwdatabaseschema)
* [SingleAuthorTalk](#singleauthortalk)
* [SiteMatrixChecklist](#sitematrixchecklist)
* [SpeedPagePatrol](#speedpagepatrol)
* [SpeedUserPatrol](#speeduserpatrol)
* [TestWikipediaCrapPopulation](#testwikipediacrappopulation)
* [TSUsers](#tsusers)
* [wgLogoUsage](#wglogousage)
* [WikiInfo](#wikiinfo)
* [wikimedia-svn-search](#wikimedia-svn-search)
* [wikimedia-svn-stats](#wikimedia-svn-stats)
* [wmfBugZillaPortal](#wmfbugzillaportal)

## wikimedia-svn-search
![Decommissioned](https://img.shields.io/badge/status-decommissioned-green.svg)

Superseded by [**github.com/search @wikimedia**](https://github.com/search?q=wgHooks+%40wikimedia&type=Code), and [Legoktm's Codesearch](https://codesearch.wmflabs.org/).

Old portraits:

<img height="355" alt="Screenshot" src="https://user-images.githubusercontent.com/156867/53689232-12dea980-3d49-11e9-88ff-af901deac94f.png">


## wikimedia-svn-stats
![Decommissioned](https://img.shields.io/badge/status-decommissioned-green.svg)

* Source code: [wikimedia-svn-stats.php](./public_html/wikimedia-svn-stats.php)
* Old address: https://toolserver.org/~krinkle/wikimedia-svn-stats.php

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3048135/83ea433c-e141-11e3-9d25-681bdb76fcba.png" height="500" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3048137/8be0c0f2-e141-11e3-8b72-b66024bd24c0.png" height="500" title="Screenshot"/>

## Krinkle's Usage
![Migrated](https://img.shields.io/badge/status-migrated-brightgreen.svg)

Migrated to [**tools.wmflabs.org/usage**](https://tools.wmflabs.org/usage/?action=usage&group=Krinkle).

* Source code: [KrinkleSausage.php](./public_html/KrinkleSausage.php)
* Old address: http://krinkle-tools.grizzdesign.nl/KrinkleSausage.php

Old portrait:

<img src="https://user-images.githubusercontent.com/156867/44926602-e1f5cb80-ad49-11e8-9970-78ef9a418560.png" height="500" title="Screenshot"/>

## Get Top RC Users
![Pending migration](https://img.shields.io/badge/status-pending-orange.svg)

* Status: https://github.com/Krinkle/ts-krinkle-misc/issues/2
* Source code: [getTopRCusers.php](./public_html/getTopRCusers.php)
* Old address: https://toolserver.org/~krinkle/getTopRCusers.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3210496/f9f39488-eec6-11e3-8226-ebdf2af7f3cb.png" height="500" title="Screenshot"/>

## Get Top RC Pages
![Pending migration](https://img.shields.io/badge/status-pending-orange.svg)

* Status: https://github.com/Krinkle/ts-krinkle-misc/issues/3
* Source code: [getTopRCpages.php](./public_html/getTopRCpages.php)
* Old address: https://toolserver.org/~krinkle/getTopRCpages.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3428500/8250a8ee-0041-11e4-849d-3b9b8546dd90.png" height="500" title="Screenshot"/>

## MoreContributions
![Migrated](https://img.shields.io/badge/status-migrated-brightgreen.svg)

* Status: Integrated with [**tools.wmflabs.org/guc**](https://tools.wmflabs.org/guc/)<br/>
  Including:
  * [issue #64499](https://bugzilla.wikimedia.org/show_bug.cgi?id=64499) (wildcard user names)
  * [issue #68358](https://bugzilla.wikimedia.org/show_bug.cgi?id=68358) (sort entries chronologically)
* Source code: [MoreContributions](./public_html/MoreContributions/)
* Old address: https://toolserver.org/~krinkle/MoreContributions/

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3424353/08c29bb0-ffcc-11e3-82ff-42c7f53a738f.png" height="500" title="Screenshot"/>


## MoreWatchlists
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: No longer had the time to look after it and stopped working when `watchlisttoken` was removed from the MediaWiki API via `action=query&meta=userinfo&uiprop=options` (following security improvements). Should be relatively easy to bring back via BotPasswords or OAuth.
* Source code: [MoreWatchlists](./krinkle-tools.grizzdesign.nl/MoreWatchlists/)
* Old adddress: http://krinkle-tools.grizzdesign.nl/MoreWatchlists/

Old portraits:

<img src="https://user-images.githubusercontent.com/156867/48884052-9ae41780-ee1a-11e8-9de5-9b7588ff2830.png" height="221" title="Screenshot of form">
<img src="https://user-images.githubusercontent.com/156867/48884053-9ae41780-ee1a-11e8-9cf4-397126aa5e81.png" height="240" title="Screenshot of feed">

## CommonsCreatorLister
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: Wasn't actively maintained or used.
  <br>File an issue if you're a developer who will co-own. If there's enough intersted users, we can revive and migrate this.
* Source code: [CommonsCreatorLister](./public_html/CommonsCreatorLister/)
* Old address: https://toolserver.org/~krinkle/CommonsCreatorLister/

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3424637/71d72f54-ffda-11e3-962f-842d59011ce2.png" height="230" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3424636/71d68cfc-ffda-11e3-9c6d-1044de958d3e.png" height="500" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3424635/71d6652e-ffda-11e3-9480-eefb8a8c2fd9.png" height="500" title="Screenshot"/>

## TSUsers
![Obsolete](https://img.shields.io/badge/status-obsolete-lightgrey.svg)

* Source code: [TSUsers.php](./public_html/TSUsers.php)
* Old address: https://toolserver.org/~krinkle/TSUsers.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3424695/d8804458-ffde-11e3-86d3-a1974c625f06.png" height="450" title="Screenshot"/>

## wmfBugZillaPortal
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: Wasn't actively maintained or used.
  <br>Code is available for re-use by interested developers.
* Source code: [ts-krinkle-wmfBugZillaPortal.git](https://github.com/Krinkle/ts-krinkle-wmfBugZillaPortal)
* Old address: https://toolserver.org/~krinkle/wmfBugZillaPortal/

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3428015/f6f9b822-003a-11e4-8731-c032e93c0bd7.png" height="500" title="Screenshot"/>

## CommonsMoveReview
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: Wasn't actively maintained or used.
  <br>Code is available for re-use by interested developers.
* Source code: [CommonsMoveReview.php](./public_html/CommonsMoveReview.php)
* Old address: https://toolserver.org/~krinkle/CommonsMoveReview.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3428344/6207f896-003f-11e4-9d85-f763f3dff5e4.png" height="420" title="Screenshot"/>

## CommonsUploadPatrol
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: Unmaintained.
* Source code: Lost in Toolserver heavens.
* Former address: https://toolserver.org/~krinkle/CommonsUploadPatrol/
* Documentation: <https://meta.wikimedia.org/wiki/User:Krinkle/Tools/Commons_Upload_Patrol>
* Project page: <https://commons.wikimedia.org/wiki/Commons:Recent_uploads_patrol>

Portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/11013166/c1c4418c-84f9-11e5-9349-ecf2ba3078de.png" height="500" title="Screenshot"/>


## SpeedUserPatrol
![Obsolete](https://img.shields.io/badge/status-obsolete-brightgreen.svg)

Superseded by [**RTRC** (gadget)](https://meta.wikimedia.org/wiki/RTRC).

* Source code: [SpeedUserPatrol.php](./public_html/SpeedUserPatrol.php)
* Old address: https://toolserver.org/~krinkle/SpeedUserPatrol.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3428453/ead06cc0-0040-11e4-980f-17a941faeb65.png" height="500" title="Screenshot"/>

## SpeedPagePatrol
![Decommissioned](https://img.shields.io/badge/status-decommissioned-red.svg)

* Status: No longer worked due to changes in the MediaWiki software.
  <br>RTRC might incorporate this feature, track https://github.com/Krinkle/mw-gadget-rtrc/issues/35.
* Source code: [SpeedPagePatrol.php](./public_html/SpeedPagePatrol.php)
* Old address: https://toolserver.org/~krinkle/SpeedPagePatrol.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3455462/798fccb6-01e1-11e4-95b5-cfa32c92eddd.png" height="500" title="Screenshot"/>

## InterfaceFiles
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Source code: [InterfaceFiles.php](./public_html/InterfaceFiles.php)
* Old address: https://toolserver.org/~krinkle/InterfaceFiles.php

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3428555/259d63de-0042-11e4-8bcb-a908140048ab.png" height="500" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3428556/259dd33c-0042-11e4-8be2-ca48a25ba121.png" height="500" title="Screenshot"/>

## mwDatabaseSchema
![Migrated](https://img.shields.io/badge/status-migrated-brightgreen.svg)

Migrated to [**github.com/Krinkle/ts-krinkle-misc#mwDatabaseSchema**](https://github.com/Krinkle/ts-krinkle-misc/tree/master/public_html/mwDatabaseSchema).

* Source code: [mwDatabaseSchema](./public_html/mwDatabaseSchema/)
* Old address: https://toolserver.org/~krinkle/mwDatabaseSchema/

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3428684/56bcd128-0044-11e4-9d60-eed4dcdfe75d.png" height="500" title="Screenshot"/>

## getWikiAPI
![Migrated](https://img.shields.io/badge/status-migrated-brightgreen.svg)

Migrated to [**tools.wmflabs.org/wikiinfo**](https://tools.wmflabs.org/wikiinfo/).

* Source code: [ts-krinkle-getWikiAPI.git](https://github.com/Krinkle/wmf-tool-wikiinfo/tree/v0.3.0)
* Old address: https://toolserver.org/~krinkle/getWikiAPI/

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3429548/cfad0204-0055-11e4-8946-29e0d90ab3bb.png" height="300" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3429547/cfac9a08-0055-11e4-94a8-c5dff0ad99be.png" height="450" title="Screenshot"/>

## SingleAuthorTalk
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Source code: [SingleAuthorTalk.php](./public_html/SingleAuthorTalk.php)
* Old address: https://toolserver.org/~krinkle/SingleAuthorTalk.php

Old portrait:

<img src="https://cloud.githubusercontent.com/assets/156867/3432461/01fe2066-0075-11e4-89fa-6b9c5b2ea4f9.png" height="450" title="Screenshot"/>

## SiteMatrixChecklist
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Source code: [SiteMatrixChecklist](./public_html/SiteMatrixChecklist/)
* Old address: https://toolserver.org/~krinkle/SiteMatrixChecklist/

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3432901/fa7477d2-0079-11e4-9b4e-93260bd97350.png" height="450" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3432917/20a51600-007a-11e4-91e0-ba2257e72967.png" height="300" title="Screenshot"/>

## wgLogoUsage
![Decommissioned](https://img.shields.io/badge/status-decommissioned-green.svg)

Obsolete now that project logos are stored the [operations/mediawiki-config](https://github.com/wikimedia/operations-mediawiki-config) repository.

* Source code: [wgLogoUsage](./public_html/wgLogoUsage/)
* Old address: https://toolserver.org/~krinkle/wgLogoUsage/

## WikiInfo
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Source code: [WikiInfo](./public_html/WikiInfo/)
* Old address: https://toolserver.org/~krinkle/WikiInfo/

Old portraits:

<img src="https://cloud.githubusercontent.com/assets/156867/3433133/6c5c5db8-007c-11e4-8b25-dd711a40078b.png" height="450" title="Screenshot"/>
<img src="https://cloud.githubusercontent.com/assets/156867/3433135/6c5cef08-007c-11e4-9e20-dc0a08e11240.png" height="350" title="Screenshot"/>
<table><tr>
<th>Size</th>
<th>Most recent edit</th>
<th>First edit</th>
</tr><tr>
<td><img width="150" title="Screenshot" src="https://cloud.githubusercontent.com/assets/156867/3433134/6c5ce42c-007c-11e4-906d-bfa8b79a1175.png"/></td>
<td><img width="150" title="Screenshot" src="https://cloud.githubusercontent.com/assets/156867/3433136/6c5d6ad2-007c-11e4-85fc-7d72c98a8382.png"/></td>
<td><img width="150" title="Screenshot" src="https://cloud.githubusercontent.com/assets/156867/3433132/6c5bdcee-007c-11e4-8109-952b11bf17d5.png"/></td>
</tr></table>

## TestWikipediaCrapPopulation
![Decommissioned](https://img.shields.io/badge/status-decommissioned-lightgrey.svg)

* Status: Taken down as was no longer useful.
  <br>Code is available for re-use by interested developers.
* Source code: [TestWikipediaCrapPopulation](./bots/TestWikipediaCrapPopulation/)
* Bot actions: [Sample edits on test.wikipedia.org](https://test.wikipedia.org/wiki/Special:Contributions/KrinkleBot?year=2013&month=2&namespace=2)

## getWMML
![Migrated](https://img.shields.io/badge/status-migrated-brightgreen.svg)

Migrated to [**tools.wmflabs.org/list**](https://tools.wmflabs.org/list/).

* Source code: [getWMML.php](https://github.com/Krinkle/wmf-tool-list/blob/v0.0.2/getWMML.php)
