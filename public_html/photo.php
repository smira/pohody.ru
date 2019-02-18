<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');


$id = nav_get('id', 'integer', -1);
$travel = nav_get('travel', 'integer', -1);
$page = nav_get('page', 'integer', 1);
$fav = nav_get('fav', 'integer', -1);

if ($id != -1 && $travel != -1) // show travel photo
{
	$title = $con->property("SELECT CONCAT(rivers.name,\" '\",pohody.year) ".
	 "FROM pohody INNER JOIN rivers ON rivers.id = pohody.river WHERE pohody.id = $travel");
	html($title, 'photo', 'travel_photo');
	
}
else if ($travel != -1) // show travel gallery
{
	$title = $con->property("SELECT CONCAT(rivers.name,\" '\",pohody.year) ".
	 "FROM pohody INNER JOIN rivers ON rivers.id = pohody.river WHERE pohody.id = $travel");
	html($title, 'photo', 'travel_gallery');
}
else if ($fav != -1 && $id != -1) // show favourite photo
{
	$title = $con->property("SELECT name FROM favorites WHERE id = $fav");
	html($title, 'photo', 'fav_photo');
}
else if ($fav != -1) // show favourite gallery
{
	$title = $con->property("SELECT name FROM favorites WHERE id = $fav");
	html($title, 'photo', 'fav_gallery');
}
else
     html($s_photos, 'photo', 'photos_body');

function photos_body()
{
	global $con, $s_photos_list, $s_by_pohod, $s_favourites;
	?><h1><?=$s_photos_list?></h1>
	<ul class='img2'>
	<li><b><?=$s_by_pohod?></b>:<ul class='img'><?php
	$query = <<<SQL
SELECT pohody.id AS id, pohody.year AS year, rivers.name AS river,
COUNT(photos.id) AS number FROM pohody INNER JOIN rivers ON
pohody.river = rivers.id INNER JOIN photos ON photos.idpohod = pohody.id
GROUP BY photos.idpohod ORDER BY pohody.year, pohody.number
SQL;
 	$r = $con->query_list($query, false);
	foreach ($r as $row)
	{
		?><li><a href='/photo.php?travel=<?=$row['id']?>'><?=$row['river']?> '<?=$row['year']?></a> (<?=$row['number']?>)</li><?php
	}
	?></ul></li>
	<li><b><?=$s_favourites?></b>:<ul class='img'><?php
	$query = <<<SQL
SELECT favorites.id AS id, favorites.name AS name, COUNT(fav_photos.id_photo) AS number FROM favorites
INNER JOIN fav_photos ON favorites.id = fav_photos.id_fav GROUP BY fav_photos.id_fav
ORDER BY favorites.name
SQL;
	$r = $con->query_list($query, false);
	foreach ($r as $row)
	{
		?><li><a href='/photo.php?fav=<?=$row['id']?>'><?=htmlspecialchars($row['name'])?></a> (<?=$row['number']?>)</li><?php
	}
	?></ul></li><?php
	?></ul><?php
}

function travel_gallery()
{
	global $con, $travel, $page;

	$photos = $con->query_list("SELECT id, description, thumb_width, thumb_height FROM ".
	   "photos WHERE idpohod = $travel ORDER BY photos.order_", true);

	show_gallery($photos, "/photo.php?travel=$travel", $page);
}

function travel_photo()
{
	global $con, $travel, $page, $id;

	$photos = $con->query_list("SELECT id, description, width, height FROM ".
	   "photos WHERE idpohod = $travel ORDER BY photos.order_", true);

	show_gallery_picture($photos, "/photo.php?travel=$travel", $page, $id);
}

function fav_gallery()
{
	global $con, $fav, $page;

	$query = <<<SQL
SELECT photos.id, photos.description, thumb_width, thumb_height, CONCAT(rivers.name," '",pohody.year), 
       pohody.id
FROM photos
INNER JOIN fav_photos ON fav_photos.id_photo = photos.id AND fav_photos.id_fav = $fav 
INNER JOIN pohody ON pohody.id = photos.idpohod
INNER JOIN rivers ON rivers.id = pohody.river
ORDER BY fav_photos.order_
SQL;
	$photos = $con->query_list($query,true);
	show_gallery($photos, "/photo.php?fav=$fav", $page);
}

function fav_photo()
{
	global $con, $fav, $page, $id;

	$photos = $con->query_list("SELECT photos.id, description, width, height ".
	    "FROM photos INNER JOIN fav_photos ON fav_photos.id_photo = photos.id ".
	    "AND fav_photos.id_fav = $fav ORDER BY fav_photos.order_",true);

	show_gallery_picture($photos, "/photo.php?fav=$fav", $page, $id);
}

?>
