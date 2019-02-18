<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');


$id = nav_get('id', 'integer', -1);
if ($id == -1)
	html($s_rivers, 'rivers', 'rivers_body');
else
{
	$row = $con->qquery("SELECT rivers.id AS id, rivers.name AS name, ".
	    "rivers.description AS description FROM rivers ".
	    "WHERE rivers.id = $id");
	html($row['name'], 'rivers', 'rivers_info');
}

function rivers_body()
{
	global $con, $s_rivers_list;
	?><h1><?=$s_rivers_list?></h1><?php
 	$r = $con->query("SELECT rivers.id, rivers.name FROM rivers ".
	    "ORDER BY rivers.name");
	?><ul class='img'><?php
	while ($row = $r->fetch_array())
	{
		?><li><a href='/rivers.php?id=<?=$row[0]?>'><?=$row[1]?></a><?php
	}
	?></ul><?php
}

function rivers_info()
{
	global $row, $s_river_pohody, $s_photos, $con;
	?><div class="vynos"><table cellspacing=0 cellpadding=0><tr><td><?php
	$info = tmpl_river_pohody($row);
	if ($info != '')
		block($s_river_pohody, $info,"100%");

        $images = $con->query_list("SELECT id, description, thumb_width, thumb_height, ".
            "width, height FROM photos ".
            "WHERE idriver = {$row['id']} ORDER BY order_", false);

        if (count($images) > 0)
        {
		?><br><?php
		$images = array_map('tmpl_thumbnail', $images);
		block($s_photos, join('<br>',$images),"100%","center");
	}
	?></td></tr></table></div><?php
	// main part - description
	$lines = explode("\n",htmlspecialchars($row['description']));
	foreach ($lines as $line)
		echo "<p>".reformat($line)."</p>\n";
}

?>
