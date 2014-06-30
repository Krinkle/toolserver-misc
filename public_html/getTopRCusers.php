<?php
/**
 * Get Top RC Users
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2013
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once('CommonStuff.php');

$is_submit = false;
$revID = '0.2.0';
$revDate = '2013-11-13';

$c['title'] = 'Get Top RC Users';
$c['baseurl'] = '//toolserver.org/~krinkle/getTopRCusers.php';
$c['wiki'] = CacheAndDefault( getParamVar( 'wiki' ) );

if ( $c['wiki'] ) {

        if ( in_array( $c['wiki'], $c['wikis_npp'] ) ){

                $c['type'] = 'new';
                $c['anon_users'] = 'yes';
                $c['reg_users'] = 'yes';
                $c['rtrcparams'] = '&typeedit=off';
                $dbQuery = " /* LIMIT:10 */ /* getTopRCusers( {$c['wiki']} ) */
                        SELECT

                                        rc_user_text,
                                        count(*) as counter

                        FROM recentchanges
                        WHERE rc_type = 1
                        AND rc_patrolled != 1
                        AND rc_user = 0
                        GROUP BY rc_user_text
                        ORDER BY counter DESC
                        LIMIT 15
                ";

        } else if( in_array( $c['wiki'], $c['wikis_rcp'] ) ) {

                $c['type'] = 'edit OR new';
                $c['anon_users'] = 'yes';
                $c['reg_users'] = 'no';
                $c['rtrcparams'] = '&typeedit=on';
                $dbQuery = " /* LIMIT:10 */ /* getTopRCusers( {$c['wiki']} ) */
                        SELECT

                                        rc_user_text,
                                        count(*) as counter

                        FROM recentchanges
                        WHERE (rc_type = 0 OR rc_type = 1)
                        AND rc_patrolled != 1
                        AND rc_user = 0
                        GROUP BY rc_user_text
                        ORDER BY counter DESC
                        LIMIT 15
                ";

                if ( $k ){
                        $dbQuery = " /* LIMIT:10 */ /* getTopRCusers( {$c['wiki']} ) */
                                SELECT

                                                rc_user_text,
                                                count(*) as counter

                                FROM recentchanges
                                WHERE (rc_type = 0 OR rc_type = 1)
                                AND rc_patrolled != 1
                                /* AND rc_user = 0 */
                                GROUP BY rc_user_text
                                ORDER BY counter DESC
                                LIMIT 50
                        ";
                }

        } else {
                        die("Error: Wiki not found.");
        }
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
        <meta charset="utf-8">
        <title>Krinkle | <?=$c['title']?> - <?php echo $c['url'][$c['wiki']]; ?></title>
        <link rel="stylesheet" href="main.css">
</head>
<body>
        <div id="page-wrap">
                <h1><small>Krinkle</small> | <?=$c['title']?></h1>
                <small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
                <hr /><?php echo $c['nav']; ?><hr />
<?php
if ( $c['wiki'] ) {
?>
                <h3 id="result">Most active contributors on <?php echo $c['url'][$c['wiki']]; ?><br /><small>(by unpatrolled contributions)</small></h3>
<?php

        $toolserver_mycnf = parse_ini_file("/home/" . get_current_user() . "/.my.cnf");
        $dbConnect = mysql_connect($c['wiki'].'-p.rrdb.toolserver.org', $toolserver_mycnf['user'], $toolserver_mycnf['password']);
        if (!$dbConnect) {
                die("dbConnect: ERROR: \n" . mysql_error());
        } else {
                krLog("dbConnect: OK");
        }
        $dbSelect = mysql_select_db($c['wiki'].'_p', $dbConnect);
        if (!$dbSelect) {
                die("dbSelect: ERROR; \n" . mysql_error());
        } else {
                krLog("dbSelect: OK");
        }

        $dbResult = mysql_query($dbQuery,$dbConnect);
        if(!!$dbResult){
                krLog("dbQuery: OK");
                echo "<table><tr><th>#&nbsp;&nbsp;&nbsp;</th><th>RTRC quicklinks</th>".($x ? "<th>SpeedUserPatrol quicklinks</th>" : "")."</tr>";
                foreach(mysql_fetch_all($dbResult) as $hit){
                        echo '<tr><td>'.$hit['counter'].'</td><td><a href="//'.$c['url'][$c['wiki']].'.org/wiki/Special:BlankPage/RTRC'
                                . '?' . http_build_query(array(
                                        'opt' => json_encode(array(
                                                'rc' => array(
                                                        'showUnpatrolledOnly' => true,
                                                        'limit' => 10,
                                                        'typeEdit' => true,
                                                        'user' => $hit['rc_user_text'],
                                                ),
                                                'app' => array(
                                                        'autoDiff' => true,
                                                ),
                                        )),
                                        'kickstart' => 1,
                                )) . '">'.$hit['rc_user_text'].'</a></td>'.($x ? '<td><a
href="SpeedUserPatrol.php?wiki='.$c['wiki'].'&user='.$hit['rc_user_text'].'&k&x">SUP</a></td>' : '').'</tr>';

                }
                echo "</table><p>RTRC users: Open the above link, enable MassPatrol, then apply settings.</p>";
        } else {
                echo "Can not select query: \n" . mysql_error();
        }
        mysql_close($dbConnect);

}
?>
                <h3 id="wikilist">List of supported wikis</h3>
                <ul>
                        <li>RC-patrol wikis
                                <ul><?php foreach($c['wikis_rcp'] as $rcpwiki){
                                                echo '<li><a href="'.$c['baseurl'].'?wiki='.$rcpwiki.'">'.$c['url'][$rcpwiki].'</a></li>';
                                } ?>
                                </ul>
                        </li>
                        <li>NewPage-patrol wikis
                                <ul><?php foreach($c['wikis_npp'] as $nppwiki){
                                                echo '<li><a href="'.$c['baseurl'].'?wiki='.$nppwiki.'">'.$c['url'][$nppwiki].'</a></li>';
                                } ?>
                                </ul>
                        </li>
                </ul>

                <h3 id="author">Author</h3>
                <p>Contact me at <em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at"
/>gmail&middot;com</em>, or leave a message on the <a
href="//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload">Tools
feedback page</a>.</p>
        </div>
</body>
</html>

