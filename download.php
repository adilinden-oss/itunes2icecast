<?php
/* $Id: download.php,v 1.1 2009-05-26 00:24:58 adicvs Exp $
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

/* Process track to download */
$f_play = db_escape_string(get_variable('play'));
if ($f_play != '') {
    $query = "SELECT * FROM tracks WHERE track_id='$f_play'";
    db_query($query, $result, $rows);
    if ($rows == 1) {
        $details = (db_assoc($result));

        $file = $details['path'];
        $name = rawurlencode(basename($details['path']));
        $name = basename($details['path']);
        if ($file != '' && $name != '') {
            get_file($file, $name);
            return;
        }
    }
}

/* All done */
echo "File does not exist!";

function get_file($file, $name)
{
    $fp = @fopen($file, 'r');
    if (! $fp) {
        echo "Permission denied";
        return false;
    }

    /* Download all files except .txt */
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // some day in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Content-type: application/x-download");
    header("Content-Disposition: attachment; filename=\"" . $name . "\"");
    header("Content-Transfer-Encoding: binary");

    while (! feof($fp)) {
        print(fread($fp,1024));
        flush();
    }
    fclose($fp);
}

?>
