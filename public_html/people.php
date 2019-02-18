<?php


chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('./system/core.php');


$id = nav_get('id', 'integer', -1);
if ($id == -1)
	html($s_people, 'people', 'people_body');
else
{
	$row = $con->qquery("SELECT people.id AS id, people.name AS name, people.fullname AS fullname, ".
	    "people.email AS email, people.homepage AS homepage, photos.id AS photo, photos.width AS width, photos.height AS height ".
	    "FROM people LEFT JOIN photos ON photos.idman = people.id ".
	    "WHERE people.id = $id");
	html($row['name'], 'people', 'people_info');
}

function people_body()
{
	global $con, $s_people_list;
	?><h1><?=$s_people_list?></h1><?php
 	$r = $con->query("SELECT people.id, people.name FROM people ".
	    "ORDER BY people.name");
	?><ul class='img'><?php
	while ($row = $r->fetch_array())
	{
		?><li><a href='/people.php?id=<?=$row[0]?>'><?=$row[1]?></a><?php
	}
	?></ul><?php
}

function people_info()
{
	global $row, $s_info, $s_photo, $s_pohody_uch;
	?><h1><?=$row['fullname']?></h1><?php
	?><table><tr valign='top'><td><?php
	$info = tmpl_people_info($row);
	if ($info != '')
		block($s_info,$info,"100%");
	?></td><td rowspan='2'><?php
	if ($row['photo'] > 0)
		block($s_photo, "<img src='/images/".img_name($row['photo'])."' witdh='{$row['width']}' height='{$row['height']}' />","100%");
	?></td></tr><tr><td><?php
	$info = tmpl_people_pohody($row);
	if ($info != '')
		block($s_pohody_uch,$info,"100%");
	?></td></tr></table><?php
}

?>
