<?php
/* $Id: playlist.php,v 1.5 2008-02-02 18:07:30 adicvs Exp $
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

require('includes/conf.php');
require('includes/db.php');
require('includes/functions.php');

$t_message = false;

/* Process action request */
$f_action = db_escape_string(get_variable('action'));
if ($f_action == 'clear') {
    clear_queue();
}

/* Process remove request */
$f_remove = db_escape_string(get_variable('remove'));
if ($f_remove != '') {

    /* Do not delete iTunes lists */
    $query = "SELECT * FROM lists WHERE list_id='$f_remove'";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $row = db_assoc($result);
        if ($row['itunes'] == '0') {

            /* Remove list */
            clear_playlists_lists($f_remove);
            clear_playlists_lists_arrays($f_remove);
        } else {
            $t_message = "NOT removing iTunes playlist!";
        }
    }
}

/* Process playlist selection */
$list_id = db_escape_string(get_variable('list'));
if ($list_id == '') {

    /* Pick first playlist */
    $query = "SELECT list_id FROM lists ORDER BY list_id LIMIT 1";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $row = db_assoc($result);
        $list_id = $row['list_id'];
    } else {
        $list_id = 0;
    }
}

/* Process add to queue request */
$f_add = db_escape_string(get_variable('add'));
if ($f_add != '') {

    /* Handle request to add entire playlist */
    if ($f_add == 'all') {

        /* Get our playlist tracks */
        $query = "SELECT * FROM lists_arrays "
               . "WHERE list_id='$list_id' "
               . "ORDER BY array_id";
        db_query($query, $result, $rows);
        if ($rows > 0) {
            while ($row = db_array($result)) {
                /* Add tracks to queue */
                add_queue($row{'track_id'}, $dummy);
            }
        }
    } else {

        /* Add track to queue */
        add_queue($f_add, $t_message);
    }
}

/* Process play request */
$f_play = db_escape_string(get_variable('play'));
if ($f_play != '') {

    /* Handle request to play entire playlist */
    if ($f_play == 'all') {

        /* Get our playlist tracks */
        $query = "SELECT * FROM lists_arrays "
               . "WHERE list_id='$list_id' "
               . "ORDER BY array_id";
        db_query($query, $result, $rows);
        if ($rows > 0) {
            while ($row = db_array($result)) {
                /* Add tracks to queue */
                add_queue($row{'track_id'}, $dummy);
            }
        }

        /* Jump to first song in queue */
        $query = "SELECT queue_id FROM queue ORDER BY queue_id LIMIT 1";
        db_query($query, $result, $rows);
        if ($rows == 1) {
            $row = db_row($result);
            $queue = $row[0];
            put_status($queue, 'play_next_queue_id');
        } else {
            put_status(0, 'play_next_queue_id');
        }
    } else {

        /* Add track to queue */
        add_queue($f_play, $dummy);

        /* Get queue_id for the track we just added */
        if ($queue = get_queue_id($f_play)) {
            put_status($queue, 'play_next_queue_id');
        }
    }
}

/* Get playlist details */
$query = "SELECT * FROM lists "
       . "WHERE list_id='$list_id'"
       . "ORDER BY list_id";
db_query($query, $result, $rows);
if ($rows == 1) {
    $t_list = db_assoc($result);

    /* Get playlist tracks */
    $query = "SELECT * FROM lists_arrays "
           . "JOIN tracks ON lists_arrays.track_id = tracks.track_id "
           . "WHERE lists_arrays.list_id='$list_id' "
           . "ORDER BY lists_arrays.array_id";
    db_query($query, $result, $rows);
    if ($rows > 0) {
        for ($i=0; $row = db_array($result); $i++) {
            $t_tracks[$i] = $row;

            /* Get queue_id if track is in queue */
            $t_tracks[$i]['queue_id'] = get_queue_id($t_tracks[$i]['track_id']);
        }
    }
} else {
    $t_message = "Please select a playlist!";
}

/* Get track count */
$query = "SELECT COUNT(*) FROM lists_arrays WHERE list_id='$list_id'";
db_query($query, $result, $rows);
$row = db_row($result);
$t_count = $row[0];

/* Get iitunes playlists to show in sidebar */
$t_lists_itunes = array();
get_playlist_children('', 0, 1, $t_lists_itunes);

/* Get non-iitunes playlists to show in sidebar */
$query = "SELECT * FROM lists WHERE itunes='0' ORDER BY list_id";
db_query($query, $result, $rows);
if ($rows > 0) {
    while ($row = db_array($result)) {
        $t_lists_other[] = $row;
    }
}

/* Get queue status */
$status = get_status();
$play_now = get_track_detail($status['play_now_track_id']);
$play_next = get_queue_detail($status['play_next_queue_id']);

/* Construct query strings */
$t_self = 'playlist.php?';
if ($list_id) {
    $t_self .= 'list='.$list_id;
}

/* Construct template variables */
$t_header_title = "Playlist - " . $t_list['name'];
$t_header_title = htmlentities($t_header_title);
$t_title = "Playlist: " . $t_list['name'];
$t_title = htmlentities($t_title);

include('templates/header.tpl.php');
include('templates/leftbar.tpl.php');
include('templates/leftbar-playlist.tpl.php');
include('templates/playlist.tpl.php');
include('templates/footer.tpl.php');

?>
