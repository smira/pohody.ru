<?

chdir($HTTP_SERVER_VARS["DOCUMENT_ROOT"]."/..");
require('system/core.php');


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
	?><h1><?=$s_people_list?></h1><?
 	$r = $con->query("SELECT people.id, people.name FROM people ".
	    "ORDER BY people.name");
	?><ul class='img'><?
	while ($row = $r->fetch_array())
	{
		?><li><a href='/people.phtml?id=<?=$row[0]?>'><?=$row[1]?></a><?
	}
	?></ul><?
}

function people_info()
{
	global $row, $s_info, $s_photo, $s_pohody_uch;
	?><h1><?=$row['fullname']?></h1><?
	?><table><tr valign='top'><td><?
	$info = tmpl_people_info($row);
	if ($info != '')
		block($s_info,$info,"100%");
	?></td><td rowspan='2'><?
	if ($row['photo'] > 0)
		block($s_photo, "<img src='/images/".img_name($row['photo'])."' witdh='{$row['width']}' height='{$row['height']}' />","100%");
	?></td></tr><tr><td><?
	$info = tmpl_people_pohody($row);
	if ($info != '')
		block($s_pohody_uch,$info,"100%");
	?></td></tr></table><?
}

?>