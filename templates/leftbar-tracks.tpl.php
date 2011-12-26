<!-- $Id: leftbar-tracks.tpl.php,v 1.3 2008-02-01 01:03:50 adicvs Exp $ -->
  <div class="leftbar">
  <form name="search" method="get" action="tracks.php">
  <ul class="toc">
   <li class="header">Search</li>
   <li><input type="text" name="search" size="10"></li>
   <li>
     <select name="column">
       <option value="All">All</option>
       <option value="Name">Name</option>
       <option value="Artist">Artist</option>
       <option value="Album">Album</option>
       <option value="Genre">Genre</option>
     </select>
   </li>
   <li><input type="submit" value="Search"></li>
  </ul>
  </form>
  </div>
