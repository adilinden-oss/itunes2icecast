<!-- $Id: leftbar-savequeue.tpl.php,v 1.3 2008-02-01 01:06:10 adicvs Exp $ -->
  <div class="leftbar">
<?php  
    if (sizeof($t_lists) > 0) :
?>
  <form name="save" method="get" action="queue.php">
  <ul class="toc">
   <li class="header">Save</li>
   <li>
     <select name="save">
<?php
        for ($i=0; $i<sizeof($t_lists); $i++) :
?>
       <option value="<?php print $t_lists[$i]['list_id']; ?>"><?php print $t_lists[$i]['name']; ?></option>
<?php 
        endfor; 
?>
     </select>
   </li>
   <li><input type="submit" value="Save"></li>
  </ul>
  </form>
<?php
    endif;
?>
  <form name="saveas" method="get" action="queue.php">
  <ul class="toc">
   <li class="header">Save As</li>
   <li><input type="text" name="saveas" size="10"></li>
   <li><input type="submit" value="Save"></li>
  </ul>
  </form>
  </div>
