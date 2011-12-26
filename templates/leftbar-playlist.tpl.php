<!-- $Id: leftbar-playlist.tpl.php,v 1.5 2008-02-02 18:07:30 adicvs Exp $ -->
<?php
    $t_url_add = $t_self.'&add=all';
    $t_url_add = htmlentities($t_url_add);
    $t_url_play = $t_self.'&play=all&action=clear';
    $t_url_play = htmlentities($t_url_play);
    $t_url_remove = $t_self.'&remove='.$t_list['list_id'];
    $t_url_remove = htmlentities($t_url_remove);

    if ($t_list['list_id'] != '') :
?>
  <div class="leftbar">
  <ul class="toc">
   <li class="header">Playlist<br><?php print htmlentities($t_list['name']); ?></li>
<?php if ($t_list['itunes'] == '0') : ?>
   <li><a href="<?php print $t_url_remove; ?>" onclick="return confirm ('Are you sure you want to remove the playlist \'<?php print htmlentities($t_list['name']); ?>\'?')">Remove</a></li>
<?php endif; ?>
   <li><a href="<?php print $t_url_add; ?>">Add</a></li>
   <li><a href="<?php print $t_url_play; ?>">Play</a></li>
   <li>
  </ul>
  </div>
<?php 
    endif;
    if (sizeof($t_lists_itunes) > 0) : 
?>
  <div class="leftbar">
  <ul class="toc">
   <li class="header">iTunes</li>
<?php 
        for ($i=0; $i<sizeof($t_lists_itunes); $i++) : 
            $t_url = 'playlist.php?list='
                   . $t_lists_itunes[$i]['list_id'];
            $t_link = '<a href="' . $t_url . '">'
                    . $t_lists_itunes[$i]['name']
                    . '</a>';
            $t_indent = str_repeat('-',$t_lists_itunes[$i]['level']);
?>
   <li><?php print $t_indent; ?> <?php print $t_link; ?></li>
<?php
        endfor;
?>
  </ul>
  </div>
<?php
    endif;
    if (sizeof($t_lists_other) > 0) : 
?>
  <div class="leftbar">
  <ul class="toc">
   <li class="header">Playlists</li>
<?php 
        for ($i=0; $i<sizeof($t_lists_other); $i++) : 
            $t_url = 'playlist.php?list='
                   . $t_lists_other[$i]['list_id'];
            $t_link = '<a href="' . $t_url . '">'
                    . $t_lists_other[$i]['name']
                    . '</a>';
?>
   <li><?php print $t_link; ?></li>
<?php
        endfor;
?>
  </ul>
  </div>
<?php
    endif;
?>
