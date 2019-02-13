<?

function forums_draw_prefix()
{
}

function forums_draw(&$msg, $n)
{
	global $priv;

	echo "<div style='margin: 1em;'>";
	echo ($n+1).". <a href='?forum=".$msg["id"]."'>".$msg["title"]."</a>";
	if ($priv["forum_all"] || $priv["forum_edit_root"])
		echo " (<a href='javascript:forumControl(\"forum\", ".$msg["id"].")'>изменить</a> | <a href='javascript:forumControl(\"delete\", ".$msg["id"].")'>удалить</a>)";

	echo " (".$msg["children"]."; ".$msg["descendants"]."; ".date("m.d.Y H:i:s", $msg["updated"]).")";
	echo "<br>".nl2br($msg["body"]);

	echo "</div>\n";
}

function forums_draw_suffix()
{
	global $priv;
	if ($priv["forum_all"] || $priv["forum_edit_root"])
		echo "<div style='margin: 1em;'><a href='javascript:forumControl(\"forum\")'>Создать новый форум</a></div>";
}

function forum_draw($msg)
{
	global $priv, $ml_number;
?>
<table class=f_topic cellspacing=0 cellpadding=0><tr>
<td class=f_otstup width=<?=15+($msg["level"]>4?($msg["level"]-4)%12+3:$msg["level"]-1)*20?>>
<a href='forum/<?=$msg["@path"]?>.htm' target='message_loader<?=($ml_number++)&3?>' onclick='return messageClicked(<?=$msg["id"]?>)'><?
	for ($i = floor(($msg["level"]-4)/12); $i > 0; $i--)
		echo "<img src='/design/f_ash.gif' border=0>";
	echo "<img id=ar".$msg["id"]." src='/design/".($msg["level"]>1?"f_ar":"f_af").".gif' border=0>";
?>
</a></td>
<td class=f_collapsed id=td<?=$msg["id"]?>>
<a id=a<?=$msg["id"]?> class=f_zag href='forum/<?=$msg["@path"]?>.htm' target='message_loader<?=($ml_number++)&3?>' onclick='return messageClicked(<?=$msg["id"]?>)'><font class=f_name>&nbsp;<?=htmlspecialchars($msg["author_name"])?>:</font> <font class=f_title><?=htmlspecialchars($msg["title"])?></font></a> <font class=f_date>[<span id=date<?=$msg["id"]?>><?=date('<b>H:i</b>\&\n\b\s\p\;d/m', $msg["posted"])?></span>]</font>
<div id=m<?=$msg["id"]?> style="display: none;"></div>
</td>
</tr></table>
<?
}

function slider($forum_name, $forum, $sel, $bottom = false)
{
	global $forum_id, $forums;
?>
<tr><td>
<table width='100%' cellspacing='0' cellpadding='0'>
<tr>
<td width='18' class='pages'><img src='/design/block_left<?=$bottom?'2':''?>.gif' width='18' height='21'></td>

<td class="pages" width='230px'><select class=f_zagol onchange="document.location='?forum='+this.value">
<?
	foreach ($forums as $id=>$name)
		echo "<option value=".$id.($id==$forum?" selected":"").">".$name;
?>
</select></td>

<td class="pages" style="font-weight: bolder">
<a href="javascript:forumControl('topic', <?=$forum_id?>)">Новая тема</a>
</td>



<td class="pages" style="text-align:right">страницы:<b> 
<?
	for ($i = 0; $i < 10; $i++)
		if ($i == $sel)
			echo " <span class='sel'>".($i+1)."</span> ";
		else
			echo " "."<a href=".nav_link(array("page"=>$i, "go"=>false)).">".($i+1)."</a> ";
?>
</b></td>
<td width='18' class='pages'><img src='/design/block_right<?=$bottom?'2':''?>.gif' width='18' height='21'></td>
</tr>
</table>
</td></tr>

<?
}

function forum_tree($forum, $forum_id, $topic_id)
{
	global $forum_priv, $s_forum, $forums;
	$forum_priv["forum"] = $forum;
	$forum_priv["forum_id"] = $forum_id;
	$forum_priv["topic_id"] = $topic_id;
	html($forum_id ? $forums[$forum_id] : $s_forum,"forum", "forum_tree2");
}

function forum_tree2()
{
	global $priv, $forum_priv, $forum_id;
        $forum = $forum_priv["forum"];
        $forum_id = $forum_priv["forum_id"];
	$topic_id = $forum_priv["topic_id"];
	$forum_name = "Форум";
	if ($forum_id)
		list($forum_name) = $forum->read_message($forum_id, "title");


	$page = nav_get("page", "integer", 0);
	$page_size = 10;

	$go = nav_get("go", "integer", 0);
	if ($go)
	{
		list($go_topic_id) = $forum->read_message($go, "topic_id");
		if (!$go_topic_id)
			$go_topic_id = $go;
	}
 ?>
<script language=JavaScript src="forum.js"></script>
<script>
preproc(nl2br);
preproc(answers);
preproc(pseudo_tags);
preproc(strip_tags);

var forum_id = <?=$forum_id?$forum_id:'null'?>;
var forum_url = '<?=$forum->path?>';
<?=$forum->js_priv_object("priv", $priv)?>
var img_ar2 = new Image; img_ar2.src = "/design/f_ar2.gif";
var img_af2 = new Image; img_af2.src = "/design/f_af2.gif";
var lframe_count = <?=$forum->user["loader_frames"]?>;
var lframe_next = 0;
var lframe_loading = new Array();
</script>

<style>
font.f_name{ font-weight: bold; color: #1B11A0;}
font.f_title{}
font.f_date{color:#595959; font-size: 11px;}
a.f_zag{font-weight:normal; text-decoration:none; color: black;}
a.f_zag:visited{color: #595959;}
a.f_mail{font-weight:normal; color:black}
a.f_fnc{}
td.f_expanded a.f_zag{font-weight:normal; text-decoration:none; color:black;}
td.f_expanded a.f_zag:visited {color:black;}
td.f_expanded font.f_date{color:black;}
div.f_fnc{margin-left:10px; margin-bottom:0px; margin-top:0px; margin-right:6px; font-size: 12px;}
div.f_openzag{margin-left:10px; margin-bottom:2px; margin-top:2px; margin-right:6px}
table.f_topic{width:100%; margin-top:3px}
table.f_subtopic{width:100%; border:0; margin-top:0}
table.f_open{width:100%; border:0; margin-top:2px}
td.f_otstup{text-align:right; vertical-align:top; padding-top:3px; padding-right:2px}
div.f_msg{padding-bottom:4px; padding-top:4px; padding-left:6px; padding-right:6px; background-color: white; font-family:monospace; font-size: 14px;}

td.f_collapsed{ border: 1px solid white; font-size: 14px; }
td.f_expanded{border:1px solid #E2E2E2; background-color: #E2E2E2; margin-bottom: 40px; font-size: 14px; }
div.f_loading{margin: 4px; margin-left: 10px; font-style: italic;}
hr.sep{color: black; height: 1px;}

span.rep{color: maroon;}
span.rep2{color: navy;}
span.rep3{color: green;}

select.f_zagol { font-size: 12px; width: 200px; background-color: #E2E2E2; }
</style>

<?
	if ($forum_id)
	{
		if ($go)
		{
			$topics = $forum->topics($forum_id);
			$page = floor(array_search($go_topic_id, $topics)/$page_size);
			$start = $page*$page_size; // счетчик $topic'ов для вывода тем
		}
		else {
			$topics = $forum->topics($forum_id, ($page*$page_size).", ".$page_size);
			$start = 0;
		}
?>
<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td>
<table width="100%" border="0" cellspacing="0" cellpadding="3">

<?slider($forum_name, $forum_id, $page); ?>

<tr><td class="forum">
<?
		for ($i = $start; $i < sizeof($topics) && $i < $start+$page_size; $i++)
		{
			if ($i > $start)
				echo "<hr class=sep>";
			@readfile($forum->msg_dir."/".$forum_id."/tree/".$topics[$i].".htm");
		}
?>
<br>
</td></tr>

<? slider($forum_name, $forum_id, $page, true); ?>

</table></td></tr>
</table><br>
<?
	}
	else {
		list($msgs) = $forum->read();
		$forum->html_forums($msgs);
	}

?>
<?
	$frames = $forum->user["loader_frames"];
	for ($i = 0; $i < $frames; $i++)
		echo "<iframe name='message_loader".$i."' src='about:blank' width=0 height=0 border=0></iframe>";
	if ($go)
		echo "<script>\n clicked = -1;\n try{getObjectHandle(window, 'a".$go."').click();}catch(e){}\n</script>";
}

function forum_edit($title, $form, &$html, &$values, &$errors, &$form_tag, $quoting = false)
{
?>
<html>
<head>
<title><?=$title?></title>
<style>
form       {margin: 0px;}
input.edit {width: 100%;}
input.btn  {width: 100px; margin-bottom: 4px;}
textarea   {width: 100%; height: 100%;}
tr.fix     {height: 30px;}
tr.top     {height: 30px; background-color: #f0f0f0; text-align: center; font-weight: bold; height: 30px;}
td         {font-size: middle; padding-left: 10px; padding-right: 10px; padding-top: 4px; padding-bottom: 4px; vertical-align: top;}
td.quote   {vertical-align: bottom; padding-bottom: 2em; font-size: smaller;}
body       {margin: 0px; padding: 0px; background: white;}
</style>
<script language=JavaScript src=preproc.js></script>
</head>
<body>
<style>
</style>
<body>
<?=$form_tag?>
<table width=100% height=100% cellpadding=0 cellspacing=0 border=0>
<tr class=top>
	<td colspan=3><?=$title?></td>
</tr>
<?
	foreach ($html as $key=>$htm)
	{
		switch ($form[$key]["type"])
		{
		case "bit":
			echo "<tr class=fix><td width=120></td><td colspan=2>".$htm." ".$form[$key]["title"]."</td></tr>\n";
			break;
		case "text":
			echo "<tr><td width=120>".$form[$key]["title"].":";
			echo "</td><td colspan=2".($quoting?" rowspan=2":"").">".$htm."</td></tr>\n";
			if ($quoting)
				echo "<tr><td class=quote><a href='javascript:quote_help()'>Цитирование</a>:".
					"<br><input type=radio name=quoting value=0 checked> Нет".
					"<br><input type=radio name=quoting value=1> Есть".
					"<br><input type=radio name=quoting value=2> Полное</td></tr>";
			break;
		default:
			echo "<tr class=fix><td width=120>".$form[$key]["title"].":</td><td colspan=2>".$htm."</td></tr>\n";
		}
	}
?>
<tr class=fix>
	<td></td>
	<td>
		<input type="submit" value="ОК" class=btn>
	</td>
	<td align=right>
		<input type="reset" value="Вернуть" class=btn>
		<input type="button" onclick="window.close()" value="Закрыть" class=btn>
	</td>
</td></tr>
</table>
</form>
</body>
<script>
function load()
{
	document.all("<? reset($html); echo key($html); ?>").focus();
}
window.onload = load;

</script>
</html>
<?

}

function die_success($values)
{
?>
<script>
<?
if ($values)
	echo "opener.document.location.href='?forum=".$values["forum_id"]."&go=".$values["id"]."';";
?>
	window.close();
</script>
<?
	die();
}

function cyr($str)
{
	return convert_cyr_string($str, "w", "k");
}

function forum_mail_answer($msg)
{
	global $values, $forum_id, $mail_sent, $forum;
	$reply = &$values;

	if ($msg["notify_answers"] && $msg["author_mail"] && !isset($mail_sent[$msg["author_mail"]]) && $msg["author_mail"] != $reply["author_mail"])
	{
		$mail_sent[$msg["author_mail"]] = true;
		$body = 
"На ваше сообщение \"".$msg["title"]."\" (".$forum->path."?forum=".$reply["forum_id"]."&go=".$msg["id"].") ".
"появился новый ответ:\n".
"\n-------------------------------------------------\n".
"URL:    ".$forum->path."?forum=".$reply["forum_id"]."&go=".$reply["id"]."\n".
"Автор:  ".$reply["author_name"].($reply["author_mail"]?" <".$reply["author_mail"].">":"")."\n".
"Тема:   ".$reply["title"]."\n".
"Время:  ".date("H:i (d.m.Y)")."\n".
"-------------------------------------------------\n".
$reply["body"]."\n".
"\n-------------------------------------------------\n".
"URL форума: ".$forum->path."?forum=".$reply["forum_id"]."\n";
		$from = $reply["author_name"]." <".$reply["author_mail"].">";
		mail($msg["author_mail"], cyr($forum->user["mail_prefix"].$reply["title"]), cyr($body), cyr("From: $from\nReply-To: $from"));
	}
}

?>
