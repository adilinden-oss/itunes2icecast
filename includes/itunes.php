<?php
/* $Id: itunes.php,v 1.4 2008-01-29 02:00:06 adicvs Exp $
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

$it_in_key = false;
$it_in_value = false;
$it_in_tracks = false;
$it_in_playlists = false;
$it_in_playlist_dict = false;
$it_in_array = false;
$it_current_key = '';
$it_cache = array();
$it_handle_track = '';
$it_handle_playlist = '';

function it_parse($file, 
                  $track_function = 'it_handle_track',
                  $playlist_function = 'it_handle_playlist')
{
    global $it_in_key, $it_in_value, $it_in_tracks, $it_in_playlists;
    global $it_in_array, $it_current_key, $it_cache;
    global $it_handle_track, $it_handle_playlist;

    $it_handle_track = $track_function;
    $it_handle_playlist = $playlist_function;

    if (!$parse = xml_parser_create('UTF-8')) {
        die('Could not create XML parse');
    }

    xml_set_element_handler($parse, 'it_start_element', 'it_end_element');
    xml_set_character_data_handler($parse, 'it_char_data');

    if (!($fp = fopen($file, 'r'))) {
        die('Could not open input file');
    }

    while ($read = fread($fp, 4096)) {
        if (!xml_parse($parse, $read, feof($fp))) {
            die (sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($parse)),
                    xml_get_current_line_number($parse)));
        }
    }

    xml_parser_free($parse);
}

function it_start_element($parser, $element, $attrs)
{
    global $it_in_key, $it_in_value, $it_in_tracks, $it_in_playlists;
    global $it_in_playlist_dict, $it_in_array, $it_current_key, $it_cache;

    $element = strtolower($element);

    if ($element == 'key') {
        $it_current_key = '';
        $it_in_key = true;
    }

    if ($element == 'string' || $element == 'integer' || $element == 'date') {
        $it_in_value = true;
    }

    if ($element == 'dict' && $it_in_playlists && !$it_in_array) {
        $it_in_playlist_dict = true;
    }
}

function it_char_data($parser, $value)
{
    global $it_in_key, $it_in_value, $it_in_tracks, $it_in_playlists;
    global $it_in_playlist_dict, $it_in_array, $it_current_key, $it_cache;

    if ($it_in_key) {
        $it_current_key .= $value;
    }

    if ($it_in_value && !$it_in_array && $it_current_key != '') {
        $it_cache[$it_current_key] .= $value;
    }

    if ($it_in_array && $it_in_value && $it_current_key == 'Track ID') {
        $it_cache[$it_current_key][] = $value;
    }
}

function it_end_element($parser, $element)
{
    global $it_in_key, $it_in_value, $it_in_tracks, $it_in_playlists;
    global $it_in_playlist_dict, $it_in_array, $it_current_key, $it_cache;
    global $it_handle_track, $it_handle_playlist;

    $element = strtolower($element);

    if ($element == 'key') {
        $it_in_key = false;

        if ($it_current_key == 'Tracks') {
            $it_in_tracks = true;
            $it_cache = array();
        }

        if ($it_current_key == 'Playlists') {
            $it_in_tracks = false;
            $it_in_playlists = true;
            $it_cache = array();
        }
    }

    if ($element == 'string' || $element == 'integer' || $element == 'date') {
        $it_in_value = false;
    }

    if ($element == 'dict' && $it_in_tracks) {
        $it_handle_track($it_cache);
        $it_cache = array();
    }

    if ($element == 'array' && $it_in_playlists && $it_in_array) {
        $it_in_array = false;
    }

    if ($element == 'dict' && $it_in_playlists && !$it_in_array) {
        $it_in_playlist_dict = false;
        $it_handle_playlist($it_cache);
        $it_cache = array();
    }

    if ($it_current_key == 'Playlist Items' && $it_in_playlists) {
        $it_in_array = true;
    }
}

function it_handle_track($cache)
{
    /* Handle track */
    print "<pre>";
    print_r($cache);
    print "</pre>\n";
}

function it_handle_playlist($cache)
{
    /* Handle playlist */
    print "<pre>";
    print_r($cache);
    print "</pre>\n";
}

?>
