<?php
/* $Id: stream.php,v 1.11 2008-11-25 01:09:00 adicvs Exp $
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

require('../includes/conf.php');
require('../includes/db.php');
require('../includes/functions.php');

/* ezstream specifics */
$CONF['ez_bin']         = '/usr/bin/ezstream';
$CONF['ez_pid']         = '/var/run/stream.php.pid';
$CONF['ez_sym']         = '/tmp/file_ezstream';
$CONF['ez_cfg']         = '/tmp/stream_ezstream.xml';
$CONF['ez_tpl']         = 'ezstream.tpl.xml';
$CONF['ez_meta_cfg']    = '/tmp/meta_ezstream.sh';
$CONF['ez_meta_tpl']    = 'meta.tpl.sh';

/* Do NOT run this script through a browser */
if (isset ($_SERVER["SERVER_ADDR"])) {
    die("<br><strong>This script is cannot be run through browser!</strong>");
}

/* Handle comamnd line args */
$args = $_SERVER['argv'];
for ($i = 1; $i < sizeof($args); $i++) {
    switch ($args[$i]) {
        case '-d':
            $daemon = 1;
            break;
        case '-u':
            $user = $args[++$i];
            break;
        default:
    }
}

/* Change user */
if ($user) {
    if (!ezstream_setuid($user)) {
        die("Could not change to user: '$user'\n");
    }
}

/* Run as daemon */
if ($daemon) {
    ezstream_daemon($daemon);
    exit(0);
}

/* Run on console */
ezstream_run('');
exit(0);

function ezstream_daemon($daemon)
{
    global $CONF;

    /* Fork */
    $pid = pcntl_fork();
    switch ($pid) {

        /* Child, continues detached */
        case 0:
            posix_setsid();
            ezstream_run($daemon);
            exit(0);

        /* Error, exit with error */
        case -1:
            exit(1);

        /* Parent, exits without error */
        default:
            if ($CONF['ez_pid']) {
                file_put_contents($CONF['ez_pid'], $pid . "\n");
            }
            exit(0);
    }
}

function ezstream_run($daemon)
{
    global $CONF;

    /* Loop endlessly */
    while (true) {

        /* Get status */
        $status = get_status();

        /* Retrieve track to play from status table */
        $details = get_queue_detail($status['play_next_queue_id']);
        if ($details && is_readable($details['path'])) {
            $play_now = $details;
        } else {
            $play_now = $CONF['silence'];
        }

        /* Retrieve next track to play from queue */
        $query = "SELECT * FROM queue " 
               . "WHERE queue_id>'" . $status['play_next_queue_id'] . "' "
               . "ORDER BY queue_id "
               . "LIMIT 1";
        db_query($query, $result, $rows);
        if ($rows == 1) {
            $row = (db_assoc($result));
            $play_next = get_queue_detail($row['queue_id']);
        } else {
            $query = "SELECT * FROM queue ORDER BY queue_id LIMIT 1";
            db_query($query, $result, $rows);
            if ($rows == 1) {
                $row = (db_assoc($result));
                $play_next = get_queue_detail($row['queue_id']);
            } else {
                $play_next = $CONF['silence'];
            }
        }

        /* Update status */
        put_status($play_now['queue_id'],'play_now_queue_id');
        put_status($play_now['track_id'],'play_now_track_id');
        put_status($play_next['queue_id'],'play_next_queue_id');

        /* Be verbose */
        if ($daemon == '') {
            echo "----\n";
            echo "Playing: ".$play_now['artist']." - ".$play_now['name']."\n";
            echo "Path: ".$play_now['path']."\n";
        }

        /* Create symlink for ezstream to access the media file */
        $ext = substr($play_now['path'], strrpos($play_now['path'], '.') + 1);
        $ext = strtolower($ext);
        $link = $CONF['ez_sym'] . '.' . $ext;

        @unlink($link);
        if (!symlink($play_now['path'], $link)) {
            die("Failed to create symlink to media file\n");
        }

        /* Create the ezstream config file */
        $parameters = array();
        $parameters['PATH']     = htmlspecialchars($link);
        $parameters['ARTIST']   = htmlspecialchars($play_now['artist']);
        $parameters['NAME']     = htmlspecialchars($play_now['name']);
        $parameters['META']     = htmlspecialchars($CONF['ez_meta_cfg']);
        $parameters['URL']      = htmlspecialchars(
                                  "http://" . $CONF['stream_host'] 
                                . ":" .$CONF['stream_port'] 
                                . "/" . $CONF['stream_mount']
                                  );
        $parameters['USER'] = htmlspecialchars($CONF['stream_user']);
        $parameters['PASSWORD'] = htmlspecialchars($CONF['stream_password']);
        $parameters['BITRATE'] = htmlspecialchars($CONF['stream_bitrate']);
        if ($daemon != '') {
            $parameters['QUIET']   = ' 2>/dev/null';
        } else {
            $parameters['QUIET']   = '';
        }
        ezstream_config($CONF['ez_tpl'], $CONF['ez_cfg'], $parameters);
        ezstream_config($CONF['ez_meta_tpl'], $CONF['ez_meta_cfg'], $parameters);
        chmod($CONF['ez_meta_cfg'], 0755);

        /* Execute ezstreamer */
        $cmd  = $CONF['ez_bin'] . " -c " . $CONF['ez_cfg'];
        if ($daemon != '') {
            $cmd .= " >/dev/null 2>/dev/null";
        }
        system($cmd);
    }
}

function ezstream_config($in_file, $out_file, $parameters)
{
    /* Get template file */
    if (!($fp = fopen($in_file, 'r'))) {
        die("Could not open input file\n");
    }
    $content = fread($fp, filesize($in_file));
    fclose($fp);

    /* Replace parameters */
    foreach ($parameters as $key => $val) {
        $key = "\{".$key."\}";
        $content = ereg_replace($key, $val, $content);
    }

    /* Write template file */
    if (!($fp = fopen($out_file, 'w'))) {
        die("Could not open output file\n");
    }
    fwrite($fp, $content);
    fclose($fp);
}

function ezstream_setuid($user)
{
    $pwd = posix_getpwnam($user);
    if ($pwd['uid'] == '') {
        return false;
    }
    if (!posix_setuid($pwd['uid'])) {
        return false;
    }

    return true;
}

?>
