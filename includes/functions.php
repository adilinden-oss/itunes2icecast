<?php
/* $Id: functions.php,v 1.10 2008-02-02 18:07:30 adicvs Exp $
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

/*
 * Retrieve children of playlist parents. Used recursively
 * to populate array with iTunes hierachical playlists.
 */
function get_playlist_children($parent_id, $level, $itunes, &$lists)
{
    /* Retrieve all children of parent */
    $query = "SELECT * FROM lists "
           . "WHERE itunes='$itunes' AND parent_id='$parent_id' "
           . "ORDER BY name";
    db_query($query, $result, $rows);
    if ($rows > 0) {
        while ($row = db_array($result)) {
            $row['level'] = $level;
            $lists[] = $row;

            /* Recurse to find this childs children */
            get_playlist_children($row['list_id'], $level+1, $itunes, $lists);
        }
    }
}

/*
 * Add track to queue
 */
function add_queue($track_id, &$message)
{
    /* Check for duplicate track in queue */
    $query = "SELECT * FROM queue WHERE track_id='$track_id'";
    db_query($query, $result, $rows);
    if ($rows != 0) {
        $message = "Ignoring duplicate track";
    } else {
        $query = "INSERT INTO queue (track_id) VALUES ($track_id)";
        db_query($query, $result, $rows);
        if ($rows != 1) {
            $message = "Unable to add track to queue";
        } else {
            // $message = "Added track to queue";
        }
    }
}

/*
 * Get queue id for a track (if it exists in queue)
 */
function get_queue_id($track_id)
{
    /* Get queue id for track */
    $query = "SELECT * FROM queue WHERE track_id='$track_id'";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $row = db_assoc($result);
        return $row['queue_id'];
    } else {
        return false;
    }
}

/*
 * Clear tracks table
 */
function clear_tracks()
{
    /* Remove all tracks */
    $query = 'DELETE FROM tracks';
    db_query($query, $result, $rows);
}

/*
 * CLear status table
 */
function clear_status()
{
    /* Remove status and init */
    $query = 'DELETE FROM status';
    db_query($query, $result, $rows);
    init_status();
}

/*
 * Clear queue table
 */
function clear_queue()
{
    /* Remove queue */
    $query = 'DELETE FROM queue';
    db_query($query, $result, $rows);

    /* Set auto_increment */
    $query = 'ALTER TABLE queue AUTO_INCREMENT=1';
    db_query($query, $result, $rows);
}

/*
 * Clear playlists
 */
function clear_playlists()
{
    /* Find itunes playlists */
    $query = "SELECT list_id FROM lists WHERE itunes='1'";
    db_query($query, $result, $rows);
    if ($rows > 0) {

        /* Process each list_id */
        while ($row = db_array($result)) {
            $list_id = $row['list_id'];

            /* Remove matching tracks from lists_arrays table */
            clear_playlists_lists_arrays($list_id);

            /* Remove matching playlist from lists table */
            clear_playlists_lists($list_id);
        }
    }
}

function clear_playlists_lists($list_id)
{
    /* Remove playlist from lists table */
    $query = "DELETE FROM lists WHERE list_id='$list_id'";
    db_query($query, $result, $rows);
    if ($rows != 1) {
        return false;
    }
    return true;
}

function clear_playlists_lists_arrays($list_id)
{
    /* Remove tracks from lists_arrays table */
    $query = "DELETE FROM lists_arrays WHERE list_id='$list_id'";
    db_query($query, $result, $rows);
    if ($rows == 0) {
        return false;
    }
    return true;
}

function add_playlist($name, $itunes=0)
{
    $query = "INSERT INTO lists "
           . "(name,itunes,parent_id) "
           . "VALUES ('"
           . $name . "','"
           . $itunes . "','"
           . "''"
           . "')";
    $list_id = true;   /* The db query will return the auto_increment id */
    db_query($query, $result, $rows, $list_id);
    if ($rows != 1) {
        return false;
    } else {
        return $list_id;
    }
}

function add_playlist_array($list_id, $track_id)
{
    $query = "INSERT INTO lists_arrays "
           . "(list_id,track_id) "
           . "VALUES ('"
           . $list_id . "','"
           . $track_id
           . "')";
    db_query($query, $result, $rows);
    if ($rows != 1) {
        return false;
    } else {
        return true;
    }
}

function get_queue_detail($queue_id)
{
    $query = "SELECT queue_id,queue.track_id,artist,name,path FROM queue "
           . "JOIN tracks ON queue.track_id = tracks.track_id "
           . "WHERE queue_id='$queue_id'";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $details = (db_assoc($result));
        return $details;
    } else {
        return false;
    }
}

function get_track_detail($track_id)
{
    $query = "SELECT track_id,artist,name,path FROM tracks "
           . "WHERE track_id='$track_id'";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $details = (db_assoc($result));
        return $details;
    } else {
        return false;
    }
}

function init_status()
{
    /* Make sure we have a clean status table */
    $query = 'DELETE FROM status';
    db_query($query, $result, $rows);

    /* Initialize our status table */
    $query = "INSERT INTO status (play_now_track_id,play_now_queue_id,"
           . "play_next_queue_id,play_random) "
           . "VALUES ('0','0','0','0')";
    db_query($query, $result, $rows);
}

function get_status()
{
    $query = "SELECT * FROM status";
    db_query($query, $result, $rows);
    if ($rows != 1) {
        init_status();
        die("Status table corrupt, fixed it\n");
    }
    $status = (db_assoc($result));

    return $status;
}

function put_status($value, $column='play_next_queue_id')
{
    $query = "UPDATE status SET $column='$value'";
    db_query($query, $result, $rows);
}

/*
 * Get form variable value from GET request.
 */
function get_variable($variable)
{
    $val = '';
    $method = '';

    if ($method = 'GET') {
        if (isset($_GET[$variable])) {
            $val = trim($_GET[$variable]);
        }
    }
    return $val;
}

/*
 * Format iTunes time paramter into a more suitable format
 */
function format_time($time, $format='short')
{
    $time = round((abs($time)/1000),0);

    if ($format == 'long') {
        /* x d x h x min x sec */
        $days = round($time/86400, 0);
        $hours = round(($time % 86400)/3600, 0);
        $minutes = round((($time % 86400) % 3600) / 60, 0);
        $seconds = round((($time % 86400) % 3600) % 60, 0);
        $time = "";

        if ($days)
            $time .= "$days d ";
        if ($hours)
            $time .= "$hours h ";
        if ($minutes)
            $time .= "$minutes min ";
        if ($seconds)
            $time .= "$seconds sec";
    } else {
        /* x:xx */
        $minutes = round($time / 60, 0);
        $seconds = round($time % 60, 0);

        $time = sprintf('%d:%02d', $minutes, $seconds);
    }

    return $time;
}

?>
