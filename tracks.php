<?php
/* $Id: tracks.php,v 1.12 2008-02-02 18:07:30 adicvs Exp $
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

/* Process add request */
$f_add = db_escape_string(get_variable('add'));
if ($f_add != '') {

    /* Add track to queue */
    add_queue($f_add, $t_message);
}

/* Process play request */
$f_play = db_escape_string(get_variable('play'));
if ($f_play != '') {

    /* Add track to queue */
    add_queue($f_play, $dummy);

    /* Get queue_id for the track we just added */
    if ($queue = get_queue_id($f_play)) {
        put_status($queue, 'play_next_queue_id');
    }
}

/* Process pagination */
$f_start = db_escape_string(get_variable('start'));
if ($f_start == '') {
    $f_start = 0;
}
$f_count = db_escape_string(get_variable('count'));
if ($f_count == '') {
    $f_count = 20;
}

/* Process search */
$f_search = db_escape_string(get_variable('search'));
$f_column = db_escape_string(get_variable('column'));

/* Construct db query */
$query_order = "ORDER BY artist,album,track ";
$query_limit = "LIMIT $f_start,$f_count ";

$search = false;
$query_meat = "FROM tracks ";
if ($f_search != '') {
    switch ($f_column) {
        case 'All':
            $search = true;
            $query_meat = "FROM tracks "
                        . "WHERE "
                        . "name LIKE '%$f_search%' "
                        . "OR artist LIKE '%$f_search%' "
                        . "OR album LIKE '%$f_search%' "
                        . "OR genre LIKE '%$f_search%' ";
            break;
        case 'Name':
            $search = true;
            $query_meat = "FROM tracks "
                        . "WHERE "
                        . "name LIKE '%$f_search%' ";
            break;
        case 'Artist':
            $search = true;
            $query_meat = "FROM tracks "
                        . "WHERE "
                        . "artist LIKE '%$f_search%' ";
            break;
        case 'Album':
            $search = true;
            $query_meat = "FROM tracks "
                        . "WHERE "
                        . "album LIKE '%$f_search%' ";
            break;
        case 'Genre':
            $search = true;
            $query_meat = "FROM tracks "
                        . "WHERE "
                        . "genre LIKE '%$f_search%' ";
            break;
        default:
            $search = false;
            $query_meat = "FROM tracks ";
    }
}

/* Get tracks */
$query = "SELECT * " . $query_meat . $query_order . $query_limit;
db_query($query, $result, $rows);
if ($rows > 0) {

//    while ($row = db_array($result)) {
//        $t_tracks[] = $row;
//    }

    for ($i=0; $row = db_array($result); $i++) {
        $t_tracks[$i] = $row;

        /* Get queue_id if track is in queue */
        $t_tracks[$i]['queue_id'] = get_queue_id($t_tracks[$i]['track_id']);
    }
}

/* Get track count */
$query = "SELECT COUNT(*) " . $query_meat;
db_query($query, $result, $rows);
$row = db_row($result);
$track_count = $row[0];

/* Get queue status */
$status = get_status();
$play_now = get_track_detail($status['play_now_track_id']);
$play_next = get_queue_detail($status['play_next_queue_id']);

/* Construct query strings */
$t_self = 'tracks.php?';
if ($search) {
    $t_self .= 'search='.urlencode(stripslashes($f_search)).'&column='.$f_column.'&';
}

$start_p = $f_start - $f_count;
$start_n = $f_start + $f_count;
if ($start_p >= 0) {
    $t_prev = $t_self."start=$start_p&count=$f_count";
    $t_prev = htmlentities($t_prev);
    $t_link_prev = '<a href="'.$t_prev.'">Prev</a>';
} else {
    $t_link_prev = 'Prev';
}
if ($start_n < $track_count) {
    $t_next = $t_self."start=$start_n&count=$f_count";
    $t_next = htmlentities($t_next);
    $t_link_next = '<a href="'.$t_next.'">Next</a>';
} else {
    $t_link_next = 'Next';
}

$t_self .= "start=$f_start&count=$f_count";

/* Template variables */
$t_header_title = "Track Listing";
$t_title = $t_header_title;
$t_column = $f_column;
$t_search = stripslashes($f_search);
$t_count  = $track_count;

include('templates/header.tpl.php');
include('templates/leftbar.tpl.php');
include('templates/leftbar-tracks.tpl.php');
include('templates/tracks.tpl.php');
include('templates/footer.tpl.php');

?>
