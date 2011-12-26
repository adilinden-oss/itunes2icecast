<!-- $Id: leftbar.tpl.php,v 1.4 2008-02-02 18:07:30 adicvs Exp $ -->
  <div class="leftbar">
  <ul class="toc">
   <li class="header">View</li>
   <li><a href="./tracks.php">Tracks</a></li>
   <li><a href="./playlist.php">Playlists</a></li>
   <li><a href="./queue.php">Queue</a></li>
  </ul>
  </div>
<?php
    $t_url_stream_listen = "icy://" . $CONF['stream_host'] 
                         . ":" . $CONF['stream_port']
                         . "/" . $CONF['stream_mount']; 
    $t_url_stream_listen = htmlentities($t_url_stream_listen);
    $t_url_stream_manage = "http://" . $CONF['stream_host'] 
                  . ":" . $CONF['stream_port'];
    $t_url_stream_manage = htmlentities($t_url_stream_manage);
?>
  <div class="leftbar">
  <ul class="toc">
   <li class="header">Stream</li>
   <li><a href="<?php print $t_url_stream_manage; ?>" target="_blank">Manage</a></li>
   <li><a href="<?php print $t_url_stream_listen; ?>" target="_blank">Listen</a></li>
  </ul>
  </div>
