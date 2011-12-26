<?php
/* $Id: db.php,v 1.4 2008-01-29 02:00:06 adicvs Exp $
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
 * db_query
 *
 * Sends a query to database to obtain query result and number of rows.
 */
function db_query ($query, &$result, &$rows, &$auto=false)
{
    global $CONF;
    $result = '';
    $rows = 0;

    /* Open database connection */
    $db = @mysql_connect($CONF['db_host'], $CONF['db_user'], $CONF['db_pass']);
    if (! $db) {
        die("Error connecting to db: " . mysql_error());
    }

    /* Select database */
    if (! @mysql_select_db($CONF['db_name'], $db)) {
        die("Error selecting database:" . $CONF['db_name']);
    }

    /* Run query */
    $result = @mysql_query($query, $db);
    if (! $result) {
        die("Error sending query: " . mysql_error());
    }

    /* Number of rows affected */
    if ( preg_match("/^(insert|delete|update|replace|alter)\s+/i",$query) ) {
        $rows = mysql_affected_rows($db);
    } else {
        $rows = mysql_num_rows($result);
    }

    /* A hack to return the last value added into an auto_increment columns.
     * Since LAST_INSERT_ID() has to be called from the same SQL connection
     * we cannot use a seperate query outside of this function.
     */
    if ($auto && preg_match("/^(insert)\s+/i",$query)) {
        $auto_result = @mysql_query("SELECT LAST_INSERT_ID()", $db);
        $auto_row = mysql_fetch_row($auto_result);
        $auto = $auto_row[0];
    }

    /* Close database connection */
    mysql_close($db);

    return true;
}

/*
 * db_array
 *
 * Returns an array of strings that corresponds to the fetched row, or FALSE  
 * if there are no more rows. You'll get an array with both associative and 
 * number indices.
 */
function db_array ($result)
{
    return mysql_fetch_array($result, MYSQL_BOTH);
}

/*
 * db_assoc
 *
 * Returns an associative array that corresponds to the fetched row and moves 
 * the internal data pointer ahead.
 */
function db_assoc ($result)
{
    return mysql_fetch_assoc($result);
    //return mysql_fetch_array($result, MYSQL_ASSOC);
}

/*
 * db_row
 *
 * Returns a numerical array that corresponds to the fetched row and moves the 
 * internal data pointer ahead.
 */
function db_row ($result)
{
    return mysql_fetch_row($result);
    //return mysql_fetch_array($result, MYSQL_NUM);
}

/*
 * db_escape_string
 *
 * Escape a string to be database safe.
 */
function db_escape_string ($str)
{
    global $CONF;

    /* Open database connection */
    $db = @mysql_connect($CONF['db_host'], $CONF['db_user'], $CONF['db_pass']);
    if (! $db) {
        die("Error connecting to db: " . mysql_error());
    }

    if (get_magic_quotes_gpc() == 0) {
        return mysql_real_escape_string($str);
    } else {
        $str = stripslashes($str);
        return mysql_real_escape_string($str);
    }

    /* Close database connection */
    mysql_close($db);
}

?>
