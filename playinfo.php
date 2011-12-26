<?php
/* $Id: playinfo.php,v 1.1 2008-11-25 01:22:10 adicvs Exp $
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

/* Get queue status */
$status = get_status();
$play_now = get_track_detail($status['play_now_track_id']);
$play_next = get_queue_detail($status['play_next_queue_id']);

echo "Playing: " . $play_now['artist'] . " - " . $play_now['name'] . "\n";
echo "Up Next: " . $play_next['artist'] . " - " . $play_next['name'] . "\n";

?>
