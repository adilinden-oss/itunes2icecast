<?php
/* $Id: itunes2db.php,v 1.11 2009/05/03 20:14:50 adicvs Exp $
 * 
 * Copyright (C) 2005 Adi Linden <adi@adis.on.ca>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* Allow script to run longer then 30 seconds */
ini_set('max_execution_time', '600');

require('includes/conf.php');
require('includes/db.php');
require('includes/itunes.php');
require('includes/functions.php');

$count_track_add = 0;
$count_track_fail = 0;
$count_track_skip = 0;
$count_list_add = 0;
$count_list_fail = 0;
$count_list_ignore = 0;
$playlist_persistent_id = array();
$parent_persistent_id = array();

$track_skip = "";
$track_fail = "";

/* Remove existing data from database */
clear_tracks();
clear_status();
clear_queue();
clear_playlists();

/* Parse iTunes XML and store in database */
it_parse($CONF['itunes_xml'], 'handle_track', 'handle_playlist');

/* Establish hierachies */
handle_hierachy($playlist_persistent_id, $parent_persistent_id);

echo "Added tracks: $count_track_add <br>\n";
echo "Failed tracks: $count_track_fail <br>\n";
echo "Skipped tracks: $count_track_skip <br>\n";
echo "Added playlists: $count_list_add <br>\n";
echo "Failed playlists: $count_list_fail <br>\n";
echo "Ignored playlists: $count_list_ignore <br>\n";
echo "<br>\n";

echo "Skipped tracks: <br>" . $track_skip;
echo "<br>\n";

echo "Failed tracks: <br>" . $track_fail;
/*
 * Store hierachy in database
 */
function handle_hierachy($playlist_persistent_id, $parent_persistent_id)
{
    /* Iterate through child array (referencing parent) */
    foreach ($parent_persistent_id as $child_id => $persistent_id) {

        /* Find parent with matching persistent id */
        $parent_id = array_search($persistent_id, $playlist_persistent_id);
        if ($parent_id) {

            /* Add parent to database */
            $query = "UPDATE lists "
                   . "SET parent_id='$parent_id' "
                   . "WHERE list_id='$child_id'";
            db_query($query, $result, $rows);
            if ($rows != 1) {
                echo "Oops, messed up hierachies...<br>\n";
            }
        }
    }
}

/*
 * Place tracks data in database
 */
function handle_track($cache)
{
    global $CONF;
    global $count_track_add, $count_track_fail, $count_track_skip;
    global $track_fail, $track_skip;

    /* Get the iTunes tags we desire */
    foreach ($CONF['itunes_col'] as $col => $tag) {
        $db[$col] = db_escape_string($cache[$tag]);
    }

    /* Exclude tracks without Track ID or Location */
    if ($db['track_id'] == '' || $db['location'] == '') {
        return;
    }

    /* Build server path for media file */
    $path = rawurldecode($cache['Location']);
    //$path = utf8_decode($path);
    $path = ereg_replace($CONF['itunes_find'],$CONF['itunes_replace'],$path);
    $path = stripslashes($path);

    /* Deal with broken iTunes XML */
	$path = itunes_xml_hack($path);

    /* Exclude tracks that do not match our desired file type */
    if (!preg_match('/\.(mp3|m4a)$/i', $path)) {
        $count_track_skip++;
	$track_skip .= $path . "<br>";
	return false;
    }

    /* Make sure we have a valid path */
    if (!is_readable($path)) {
        $count_track_fail++;
	$track_fail .= "Invalid path... " . $path . "<br>";
        return false;
    }
    
    /* Make sure path is sql safe */
    $path = db_escape_string($path);

    /* Fill track into db */
    $query = "INSERT INTO tracks "
           . "(track_id,name,artist,album,genre,track,size,time,location,path) "
           . "VALUES ('"
           . $db['track_id'] . "','"
           . $db['name'] . "','"
           . $db['artist'] . "','"
           . $db['album'] . "','"
           . $db['genre'] . "','"
           . $db['track'] . "','"
           . $db['size'] . "','"
           . $db['time'] . "','"
           . $db['location'] . "','"
           . $path 
           . "')";
    db_query($query, $result, $rows);
    if ($rows != 1) {
        $count_track_fail++;
	$track_fail .= "Not SQL safe... " . $path . "<br>";
        return;
    } else {
        $count_track_add++;
    }
}

/*
 * Place playlist data in database
 */
function handle_playlist($cache)
{
    global $CONF;
    global $count_list_add, $count_list_fail, $count_list_ignore;
    global $playlist_persistent_id, $parent_persistent_id;

    /* Get the playlist tags we desire */
    $db['name'] = db_escape_string($cache['Name']);

    /* Exclude ignored playlists */
    foreach ($CONF['itunes_ignore'] as $ignore) {
        if (ereg($ignore, $cache['Name'])) {
            $count_list_ignore++;
            return;
        }
    }

    /* Exclude playlists that are empty or defective */
    if ($db['name'] == '' || count($cache['Track ID']) < 1) {
        $count_list_fail++;
        return;
    }

    /* Fill playlist info into db */
    $list_id = add_playlist($db['name'], 1);
    if (!$list_id) {
        $count_list_fail++;
        return;
    }
    $count_list_add++;

    /* Temporary persistent ID */
    if ($cache['Playlist Persistent ID'] != '') {
        $playlist_persistent_id[$list_id] = $cache['Playlist Persistent ID'];
    }
    if ($cache['Parent Persistent ID'] != '') {
        $parent_persistent_id[$list_id] = $cache['Parent Persistent ID'];
    }

    /* Fill playlist array into db */
    foreach ($cache['Track ID'] as $track) {
        if ($track != '') {
            if (!add_playlist_array($list_id, $track)) {
                echo "Ooops, failed adding playlist track...<br>\n";
            }
        }
    }
}

/*
 * Handle iTunes xml file where special characters are represented by '_'
 * instead of url and/or utf8 encoded.
 */
function itunes_xml_hack($path)
{
    /* Make sure file is readable */
    if (!is_readable($path)) {

        /* Hack to replace iTunes _ with a ? wildcard character.
         * See if the glob matches 
         */
        $path = ereg_replace('_', '?', $path);
        $glob = glob($path);

        /* Make sure we found exactly one match that is readable */
        if (count($glob) == 1) {
            return $glob[0];
        }
    }
    return $path;
}

?>
