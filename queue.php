<?php
/* $Id: queue.php,v 1.10 2008-02-02 18:07:30 adicvs Exp $
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

/* Process remove request */
$f_remove = db_escape_string(get_variable('remove'));
if ($f_remove != '') {

    /* Remove track from queue queue */
    $query = "DELETE FROM queue WHERE queue_id='$f_remove'";
    db_query($query, $result, $rows);
    if ($rows > 0) {
        // $t_message = "Removed track from queue";
    } else {
        $t_message = "Failed to remove track from queue";
    }
}

/* Process play request */
$f_play = db_escape_string(get_variable('play'));
if ($f_play != '') {
    put_status($f_play, 'play_next_queue_id');
}

/* Process action request */
$f_action = db_escape_string(get_variable('action'));

/* Clear queue */
if ($f_action == 'clear') {
    clear_queue();
}

/* Shuffle queue */
if ($f_action == 'shuffle') {

    /* Save next track */
    $status = get_status();
    $play_next = get_queue_detail($status['play_next_queue_id']);

    /* Load queue into array */
    $query = "SELECT queue_id FROM queue ORDER BY queue_id";
    db_query($query, $result, $rows);
    if ($rows > 0) {
        while ($row = db_array($result)) {
            $shuffle[] = $row['queue_id'];
        }
    }

    if ($shuffle) {

        /* Determine offset to use */
        reset($shuffle);
        if (current($shuffle) > sizeof($shuffle)) {
            $shuffle_o = 0;
        } else {
            $shuffle_o = end($shuffle) + 1;
        }

        /* Shuffle the array */
        shuffle($shuffle);

        /* Save new queue_id's to queue table */
        for ($i=0; $i<sizeof($shuffle); $i++) {
            $shuffle_i = $shuffle_o + $i;
            $query = "UPDATE queue "
                   . "SET queue_id='$shuffle_i' "
                   . "WHERE queue_id='" . $shuffle[$i] . "'";
            db_query($query, $result, $rows);
        }

        /* Set AUTO_INCREMENT value */
        $shuffle_i = $shuffle_o + $i;
        $query = "ALTER TABLE queue AUTO_INCREMENT=$shuffle_i";
        db_query($query, $result, $rows);

        /* Set status to saved track */
        put_status(get_queue_id($play_next['track_id']), 'play_next_queue_id');
    }
}

/* Process save request */
$f_save = db_escape_string(get_variable('save'));
if ($f_save != '') {

    /* Make sure playlist exists */
    $query = "SELECT list_id FROM lists WHERE list_id='$f_save'";
    db_query($query, $result, $rows);
    if ($rows == 1) {

        /* Clear Playlist array */
        $query = "DELETE FROM lists_arrays WHERE list_id='$f_save'";
        db_query($query, $result, $rows);

        /* Save queue to playlist */
        $query = "SELECT * FROM queue ORDER BY queue_id";
        db_query($query, $result, $rows);
        if ($rows > 0) {
            while ($row = db_array($result)) {
                add_playlist_array($f_save, $row['track_id']);
            }
        }
    }
}

/* Process saveas request */
$f_saveas = db_escape_string(get_variable('saveas'));
if ($f_saveas != '') {

    /* Add playlist */
    $list_id = add_playlist($f_saveas, 0);
    if ($list_id) {

        /* Save queue to playlist */
        $query = "SELECT * FROM queue ORDER BY queue_id";
        db_query($query, $result, $rows);
        if ($rows > 0) {
            while ($row = db_array($result)) {
                add_playlist_array($list_id, $row['track_id']);
            }
        }
    }
}

/* Get tracks */
$query = "SELECT * FROM queue "
       . "JOIN tracks ON queue.track_id = tracks.track_id "
       . "ORDER BY queue_id";
db_query($query, $result, $rows);
if ($rows > 0) {
    while ($row = db_array($result)) {
        $t_tracks[] = $row;
    }
}

/* Get track count */
$query = "SELECT COUNT(*) FROM queue";
db_query($query, $result, $rows);
$row = db_row($result);
$t_count = $row[0];

/* Get non-iitunes playlists for save to playlist */
$query = "SELECT * FROM lists WHERE itunes='0' ORDER BY list_id";
db_query($query, $result, $rows);
if ($rows > 0) {
    while ($row = db_array($result)) {
        $t_lists[] = $row;
    }
}

/* Get queue status */
$status = get_status();
$play_now = get_track_detail($status['play_now_track_id']);
$play_next = get_queue_detail($status['play_next_queue_id']);

/* Construct query strings */
$t_self = 'queue.php?q=0';

/* Construct template variables */
$t_header_title = "Queue Listing";
$t_title = $t_header_title;

include('templates/header.tpl.php');
include('templates/leftbar.tpl.php');
include('templates/leftbar-queue.tpl.php');
include('templates/leftbar-savequeue.tpl.php');
include('templates/queue.tpl.php');
include('templates/footer.tpl.php');

?>
