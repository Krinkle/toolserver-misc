<?php
/**
 * wikimedia-svn-stats.php :: All-in-One file
 *
 * WikimediaSvnStatistics
 * Created on November 1st, 2010
 *
 * Copyright 2010-2011 Krinkle <krinklemail@gmail.com>
 *
 * Wikimedia SVN Statistics by Krinkle is released
 * in the public domain.
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once('CommonStuff.php');

$revID = '0.1.4';
$revDate = '2011-09-02';

$c['title'] = 'Wikimedia SVN Statistics';
$c['baseurl'] = '//toolserver.org/~krinkle/wikimedia-svn-stats.php';

/**
 *  Database connection
 * -------------------------------------------------
 */
$toolserver_mycnf = parse_ini_file('/home/'.get_current_user().'/.my.cnf');
$dbConnect = mysql_connect('sql-s3-rr.toolserver.org', $toolserver_mycnf['user'], $toolserver_mycnf['password']);
if (!$dbConnect) {
        die('dbConnect: ERROR: <br>' . mysql_error());
} else {
        krLog('dbConnect: OK');
}
$dbSelect = mysql_select_db('mediawikiwiki_p', $dbConnect);
if (!$dbSelect) {
        die('dbSelect: ERROR; <br>' . mysql_error());
} else {
        krLog('dbSelect: OK');
}

/**
 *  Functions
 * -------------------------------------------------
 */
function get_stats($dateprefix = false){
        global $dbConnect, $dbSelect;

        $dbQuery = " /* LIMIT:5 */ /* WikimediaSvnStatistics::dbQuery */
        SELECT
                cpc_added,
                count(*) as count
        FROM code_prop_changes
        WHERE cpc_repo_id=1
        AND cpc_attrib='status'
        AND cpc_timestamp LIKE '$dateprefix%'
        GROUP BY cpc_added
        ORDER BY count DESC
        ;";

        $dbResult = mysql_query($dbQuery,$dbConnect);
        if(!!$dbResult){
                krLog("dbQuery_$dateprefix%: OK");
                $dbResult = mysql_fetch_all($dbResult);
                echo '<table class="wikitable"><tr><th>Changed to status</th><th>Total</th></tr>';

                foreach($dbResult as $row){
                        echo '<tr class="mw-codereview-status-'.$row['cpc_added'].'">';
                                echo '<td>'.$row['cpc_added'].'</td>';
                                echo '<td>'.$row['count'].'<small>x</small></td>';
                        echo '</tr>';
                }
                echo '</table>';
        } else {
                echo 'Can not select query: <br>' . mysql_error();
        }
        unset($row,$columns,$dbResult,$dbQuery);
}

function proces_cr_message ( $str = '' ) {
        if ( strlen( $str ) > 80 ) {
                return krEscapeHTML( substr( $str, 0, 80 ) ) . '...';
        } else {
                return krEscapeHTML( $str );
        }
}
function generate_crid_row ( $row ) {
//77109
        $str = '<tr><td><a target="_blank" href="//www.mediawiki.org/wiki/Special:Code/MediaWiki/' . rawurlencode( $row['cr_id'] ) . '">' . $row['cr_id'] . '</a></td>';
        $str .= '<td><a target="_blank" href="//www.mediawiki.org/w/index.php?title=Special:Code/MediaWiki/path&path=' . rawurlencode( $row['cr_path'] ) . '">' . $row['cr_path'] . '</a></td>';
        $str .= '<td>' . proces_cr_message( $row['cr_message'] ) . '</td>';
        $str .= '<td date>' . date( 'H:i, j F Y', strtotime( $row['cr_timestamp'] ) ) . '</td></tr>';
        return $str;
}

function get_fixmes(){
/*
code_rev
        cr_repo_id
        cr_id
        cr_timestamp
        cr_author
        cr_message
        cr_status
        cr_path
*/
        global $dbConnect, $dbSelect;

        $dbQuery = " /* LIMIT:10 NM */  /* WikimediaSvnStatistics::dbQuery */
        SELECT
                cr_id,
                cr_timestamp,
                cr_author,
                cr_message,
                cr_status,
                cr_path
        FROM code_rev
        WHERE cr_repo_id=1
        AND cr_status='fixme'
        ORDER BY cr_author ASC
        ;";

        $dbResult = mysql_query($dbQuery,$dbConnect);
        if(!!$dbResult){
                krLog("dbQuery_getfixmes: OK");
                $dbResult = mysql_fetch_all($dbResult);
                echo '<table class="wikitable" style="width:100%"><tr><th>#</th><th>Author</th></tr>';
                $outputRowsByCount = array();

                $prevAuthorName = $dbResult[0]['cr_author'];
                $prevAuthorCount = 0;
                $prevAuthorRevs = '<table class="miniTable ns">';

                // Generate HTML
                foreach($dbResult as $row){

                        // If it's still the same, process the rev
                        if ( $row['cr_author'] == $prevAuthorName ) {
                                $prevAuthorCount = $prevAuthorCount+1;
                                $prevAuthorRevs .= generate_crid_row( $row );

                        // If it's not the same (anymore) show the table row, reset and proces the first rev
                        } else {

                                // Output row for previous (not finished) author
                                if ( $prevAuthorCount !== 0 ) {
                                        $outputRowsByCount[''.$prevAuthorCount.''][] = '<tr><td>' . $prevAuthorCount . '</td><td><a target="_blank" href="//www.mediawiki.org/wiki/Special:Code/MediaWiki/author/' . rawurlencode( $prevAuthorName ) . '">' . $prevAuthorName . '</a> <div class="collapseWrap collapsed"><a class="collapseWrapToggle" href="#">Show revisions &darr;</a><div class="collapseInner">' . $prevAuthorRevs . '</table></div></div></td></tr>' . "\n";
                                }

                                // Reset and populate for the new author
                                $prevAuthorName = $row['cr_author'];
                                $prevAuthorCount = 1;
                                $prevAuthorRevs =
                                        '<table class="miniTable ns">' . generate_crid_row( $row );
                        }
                }
                // Output the HTML in order of count (descending)
                krsort($outputRowsByCount);
                foreach($outputRowsByCount as $outputRows) {
                        foreach($outputRows as $outputRow) {
                                echo $outputRow;
                        }
                }
                echo '</table>';
        } else {
                echo 'Can not select query: <br>' . mysql_error();
        }
        unset($row,$prevAuthorCount,$prevAuthorName,$dbResult,$dbQuery);
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
        <meta charset="utf-8">
        <title>Krinkle | <?=$c['title']?></title>
        <link rel="stylesheet" href="main.css">
        <style>
.mw-codereview-status-new td {
        background: #ffffc0 !important;
}
.mw-codereview-status-new:hover td {
        background: #dfdfa0 !important;
}

.mw-codereview-status-fixme td {
        background: #ff9999 !important;
}
.mw-codereview-status-fixme:hover td {
        background: #df0000 !important;
        color: white;
}
.mw-codereview-status-fixme:hover td a {
        color: #ff0 !important;
}
.mw-codereview-status-resolved td {
        background: #c0ffc0 !important;
}
.mw-codereview-status-resolved:hover td {
        background: #a0dfa0 !important;
}
.mw-codereview-status-reverted td {
        background: #ddd !important;
        color: #666 !important;
        text-decoration: line-through !important;
}
.mw-codereview-status-reverted:hover td {
        background: #aaa !important;
        text-decoration: line-through !important;
}
.mw-codereview-status-deferred td {
        color: #666;
}
.mw-codereview-status-old td {
        color: #666;
}
td[date] {
        white-space: nowrap;
}
        </style>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="main.js"></script>
</head>

<body>
        <div id="page-wrap" style="width:1012px">

                <h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
                <small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
                <br><small>This page lists the total numbers for <em>each status-change</em>. For counts of all <em>current statuses</em> of revisions instead, go to <a href="//www.mediawiki.org/wiki/Special:Code/MediaWiki/stats">Special:Code/stats</a></small>
                <hr><p><a href="#recentchanges">Recent changes</a> | <a href="#stats-fixme">Fixme's per author</a></p>

                <table style="width:100%" class="v-top" id="recentchanges">
                        <tr>
                                <td>
                                        <h3 id="recent-activity">Recent changes (<?php echo date('F Y');?>)</h3>
                                        <?php get_stats(date('Ym')); ?>
                                </td>
                                <td>
                                        <h3 id="recent-activity">Recent changes (<?php echo date('F Y', strtotime('-1 month'));?>)</h3>
                                        <?php get_stats(date('Ym', strtotime('-1 month'))); ?>
                                </td>
                        </tr>
                        <tr>
                                <td>
                                        <h3 id="recent-activity">Recent changes (<?php echo date('Y');?>)</h3>
                                        <?php get_stats(date('Y')); ?>
                                </td>
                                <td>
                                        <h3 id="recent-activity">Recent changes (<?php echo date('Y', strtotime('-1 year'));?>)</h3>
                                        <?php get_stats(date('Y', strtotime('-1 year'))); ?>
                                </td>
                        </tr>
                </table>

                <h3 id="stats-fixme">Wall of Shame (aka Fixme's per author)</h3>
                <?php get_fixmes(); ?>
        </div>
<pre><?php
mysql_close($dbConnect);
krLogFlush();
?></pre>
</body>
</html
