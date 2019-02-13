<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');

$action = nav_get('action', 'string', '');

html($site_title, 'main', 'main_body');


function main_body()
{
   ?><table cols='2' width='100%'>
   <tr><td colspan='2'><?php
   tmpl_index_news();
   ?></td></tr>
   <tr valign='top'><td width='50%'><?php
   //tmpl_index_search();
   tmpl_index_adv();
   ?><br><?php
   tmpl_index_pohody();
   ?></td><td width='50%'><?php
   tmpl_index_people();
   ?><br><?php
   tmpl_index_rivers();
   ?></td></tr></table><?php
}

?>
