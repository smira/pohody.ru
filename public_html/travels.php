<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');

$id = nav_get('id', 'integer', -1);

if ($id == -1)
	html($s_travels, 'travel', 'travels_body');
else
{
	$row = $con->qquery("SELECT pohody.id AS id, pohody.season AS season, pohody.year AS year, ".
		"rivers.name AS river, people.name AS captain, ".
		"rivers.id AS river_id, people.id AS captain_id ".
		"FROM pohody LEFT JOIN rivers ON rivers.id = pohody.river ".
		"LEFT JOIN people ON people.id = pohody.captain WHERE pohody.id = $id");
	html("{$row['river']} '{$row['year']}", 'travel', 'travels_info');
}

function travels_body()
{
	global $con, $s_pohody_list;
	$r = $con->query("SELECT pohody.id, pohody.season, pohody.year, ".
	    "rivers.name FROM pohody INNER JOIN rivers ON pohody.river = ".
	    "rivers.id ORDER BY pohody.year, pohody.number");
	?><h1><?=$s_pohody_list?></h1><?php
	?><ul class='img'><?php
	while ($row = $r->fetch_array())
	{
		?><li><a href='/travels.php?id=<?=$row[0]?>'><?=$row[3]?></a>, <?=$row[1]?> <?=$row[2]?></li><?php
	}
	?></ul><?php
}

function travels_info()
{
	global $row, $s_pohod_info, $s_command, $s_relik, $s_by_day,
	       $con, $para, $days, $s_diaries;

	$descriptions = $con->query_list("SELECT documents.id, documents.body, documents.idpeople, people.name ".
	     "FROM documents INNER JOIN pohody_docs ON pohody_docs.iddoc = documents.id ".
	     "INNER JOIN pohody ON pohody_docs.idpohod = {$row['id']} INNER JOIN people ON people.id = documents.idpeople ");

	$days = array();
	if (count($descriptions) > 0)
	{ 
		$desc_id = nav_get("doc","integer", -1);
		if (!$descriptions[$desc_id])
                {
                	$dummy = array_keys($descriptions);
			$desc_id = $dummy[0];
		}

		// main part - description
		$lines = explode("\n",htmlspecialchars($descriptions[$desc_id][0]));
		$r = $con->query("SELECT id, idpara, description,  thumb_width, ".
	    		"thumb_height, width, height FROM photos ".
	                "WHERE idpohod = {$row['id']} AND idpara > 0 ORDER BY idpara");

	        $ro = $r->fetch_array();
        	$para = 0;
	        $result = reformat_author($descriptions[$desc_id][1]);

		foreach ($lines as $line)
		{
			while ($ro && ($ro['idpara'] == $para))
			{
				$result .=  "<div class='img_container'>" .
				   tmpl_thumbnail($ro) . "</div>";
				$ro = $r->fetch_array();
			}
			$result .= "<p>".reformat($line)."</p>\n";
		}

		$r->free();
	}
	else 
		$result = '';

	?><div class="vynos"><table cellspacing=0 cellpadding=0><tr><td><?php
	$info = tmpl_pohod_info($row);
	if ($info != "")
		block($s_pohod_info, $info,"100%");
	if (count($descriptions) > 1)
	{
		?><br><?php
		block($s_diaries, tmpl_diaries_list($descriptions, $desc_id, $row['id']),"100%");
	}	
	$info = tmpl_pohod_command($row);
	if ($info != "")
	{
		?><br><?php
		block($s_command, $info,"100%",false,true);
        }
	$relik = $con->qquery("SELECT id, description, thumb_width, ".
	    "thumb_height, width, height FROM photos ".
	    "WHERE idpohod = {$row['id']} AND idpara = -1");
	if ($relik)
	{
		?><br><?php
		block($s_relik, tmpl_thumbnail($relik),"100%","center");
	}

	if (count($days) > 0)
	{
		?><br><?php
		block($s_by_day, tmpl_days_nav($days),"100%");
	}

	?></td></tr></table></div><?php

	echo $result;
}

?>
