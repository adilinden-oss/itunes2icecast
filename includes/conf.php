<?php
/* $Id: conf.php,v 1.19 2009-05-25 15:14:01 adicvs Exp $
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

/* Database Configuration */
$CONF['db_host']    = 'localhost';
$CONF['db_user']    = 'streaming';
$CONF['db_pass']    = 'password';
$CONF['db_name']    = 'streaming';

/* Stream Parameters */
$CONF['stream_host'] = 'localhost';
$CONF['stream_port'] = '8000';
$CONF['stream_mount'] = 'stream';
$CONF['stream_user'] = 'iccast';
$CONF['stream_password'] = 'streaming';
$CONF['stream_bitrate'] = '128';

/* iTunes XML file */
$CONF['itunes_xml'] = '/var/samba/itunes/iTunes Library.xml';

/* Play 'button' behaviour 
 * The play button can either add a track to the queue and play it next or
 * clear the queue and play it next. Valid options are 'add' and 'clear'
 */
$CONF['play'] = 'add';      

/* Offer downloads?
 */
$CONF['dl'] = TRUE;

/* iTunes track data to store in db */
$CONF['itunes_col'] = array(
    'track_id'      => 'Track ID',
    'name'          => 'Name',
    'artist'        => 'Artist',
    'album'         => 'Album',
    'genre'         => 'Genre',
    'track'         => 'Track Number',
    'size'          => 'Size',
    'time'          => 'Total Time',
    'location'      => 'Location');

/* iTunes playlists regex to ignore */
$CONF['itunes_ignore'] = array(
    '90.*s Music',
    'Library',
    'Audiobooks',
    '^Music$',
    '^Movies$',
    'Music Videos',
    'Party Shuffle',
    '^Podcasts$',
    'Recently Added',
    'Recently Played',
    'Top 25 Most Played',
    'TV Shows');

/* Regex to turn iTunes path into server path for media files. This
 * expression will be plugged into ereg_replace.
 */
$CONF['itunes_find']    = 'file://localhost/O:/';
$CONF['itunes_replace'] = '/var/samba/music/';

/* Track details for silence */
$CONF['silence']        = array(
            'path'      => '../silence-128k.mp3',
            'artist'    => 'Noone',
            'name'      => 'Silence',
            'queue_id'  => 0,
            'track_id'  => 0);

?>
