<!-- $Id: queue.tpl.php,v 1.10 2009-05-26 00:24:58 adicvs Exp $ -->
<div id="queue">
 <h4><?php print $t_title; ?></h4>
 <br>Playing: <?php print $play_now['artist'] . " - " . $play_now['name']; ?>
 <br>Up Next: <?php print $play_next['artist'] . " - " . $play_next['name']; ?>
<?php if ($t_message != '') : ?>
<p>
  <span class="error_msg"><?php print $t_message; ?></span>
<?php endif; ?>
<p>
  Tracks: <?php print $t_count; ?>
<p>
  <table id="queue_table" cellpadding="4" cellspacing="0">
    <tr class="header">
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>Name</td>
      <td>Time</td>
      <td>Artist</td>
      <td>Album</td>
      <td>Genre</td>
<?php if ($CONF['dl']) : ?>
      <td>&nbsp;</td>
<?php endif; ?>
    </tr>
<?php
    if (sizeof($t_tracks) > 0) :
        for ($i=0; $i<sizeof($t_tracks); $i++) :
            $t_time = format_time($t_tracks[$i]['time']);
            $t_url_play = $t_self . '&play=' . $t_tracks[$i]['queue_id'];
            $t_url_play = htmlentities($t_url_play);
            $t_url_remove = $t_self . '&remove=' . $t_tracks[$i]['queue_id'];
            $t_url_remove = htmlentities($t_url_remove);
?>
<?php if ($t_tracks[$i]['track_id'] == $play_now['track_id'] ) : ?>
    <tr class="hiliteplayoff" onMouseOver="className='hiliteplayon';" onMouseOut="className='hiliteplayoff';">
<?php else : ?>
    <tr class="hiliteoff" onMouseOver="className='hiliteon';" onMouseOut="className='hiliteoff';">
<?php endif; ?>
      <td><a href="<?php print $t_url_remove; ?>">Remove</a></td>
      <td><a href="<?php print $t_url_play; ?>">Play</a></td>
      <td><?php print $t_tracks[$i]['name']; ?></td>
      <td><?php print $t_time; ?></td>
      <td><?php print $t_tracks[$i]['artist']; ?></td>
      <td><?php print $t_tracks[$i]['album']; ?></td>
      <td><?php print $t_tracks[$i]['genre']; ?></td>
<?php if ($CONF['dl']) : ?>
      <td><a href="download.php?play=<?php print $t_tracks[$i]['track_id']; ?>">d/l</a></td>
<?php endif; ?>
    </tr>
<?php
        endfor;
    else :
?>
    <tr>
      <td colspan="7">No tracks exist!</td>
    </tr>
<?php
    endif;
?>
  </table>
</div>
