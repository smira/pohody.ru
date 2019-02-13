<?

// HTML templates for website
$site_title = 'Жизнь походная';
$s_pohody_ru = 'Походы.Ru :: ';
$s_travels = 'Походы';
$s_people = 'Походники';
$s_rivers = 'Реки';
$s_photos = 'Фотографии';
$s_news = 'Новости';
$s_forum = 'Общение';
$s_articles = 'Статьи';

$s_info = 'Информация';
$s_photo = 'Фотография';
$s_pohody_uch = 'Участие в походах';
$s_pohody_list = 'Список походов';
$s_people_list = 'Все походники';
$s_rivers_list = 'Список рек';
$s_photos_list = 'Все фотографии';
$s_pohod_info = 'Информация';
$s_command = 'Команда';
$s_relik = 'Реликвия';
$s_river_pohody = 'Походы по этой реке';
$s_by_pohod = 'по походам';
$s_by_day = 'Навигация';
$s_pages = 'Страницы';
$s_prev = "<img src='/design/less.gif' width='10' height='7'>&nbsp;Предыдущая";
$s_next = "Следующая&nbsp;<img src='/design/more.gif' width='10' height='7'>";
$s_back = "<img src='/design/up.gif' width='7' height='10'>&nbsp;Вернуться в галерею <img src='/design/up.gif' width='7' height='10'>";
$s_no_desc = '&lt;Нет подписи&gt;';
$s_search = 'Поиск';
$s_diaries = 'Дневники';
$s_favourites = 'избранное';
$s_info = 'Информация';
$s_books = 'Книги';
$s_contents = 'Оглавление';

$main_menu = array(
	'main' 		=> array('title' => 'Главная', 'url' => '/'),
	'photo' 	=> array('title' => $s_photos, 'url' => '/photo.phtml'),
	'travel' 	=> array('title' => $s_travels, 'url' => '/travels.phtml'),
	'people' 	=> array('title' => $s_people, 'url' => '/people.phtml'),
	'rivers' 	=> array('title' => $s_rivers, 'url' => '/rivers.phtml'),
	'articles' 	=> array('title' => $s_articles, 'url' => '/articles.phtml'),
	'forum' 	=> array('title' => $s_forum, 'url' => '/forum.phtml'),
);

$nav = array(
	array('title' => $site_title, 'url' => '/')
	);

$copyright = '<b>По поводу сайта пишите</b>: <a href="mailto:smir@pohody.ru">Андрей Смирнов</a><br>'.
   '<b>Материалы</b>: Александр Семенов, Ирина Терешкина, Федор и Ольга Блюхер, Андрей Калашников, Сергей Поездник, Игорь Когановский, Павел ???, Екатерина Бони, Никита Федоров';


$gallery_w = 6;
$gallery_h = 5;

$print = nav_get('print', 'integer', 0);

function html($title, $menu, $func)
{
  global $copyright, $site_title, $print;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?=build_title($title, $menu)?></title>
<meta name="keywords" value="жизнь,походная,походы,река,байдарка,катамаран,порог" />
<link rel="stylesheet" type="text/css" href="<?=$print ? 'print.css' : 'main.css' ?>" />
</head>
<body>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<? if (!$print): ?>
<td width=125><a href="/"><img src='design/top.gif' width=125 height=50 border=0/></a></td>
<? endif; ?>
<td valign='center'>
<p class='title'><?=$title?></p>
</td>
</tr>
<? if (!$print): ?>
<tr><td colspan='2'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr class='menu'>
<?
global $main_menu;
$i = 0;

foreach ($main_menu as $key => $params)
{
	?><td width='14%' <?=$menu==$key?'style="font-weight:bold"':''?> >
	<a href='<?=$params['url']?>'><?=$params['title']?></a>
	</td><?
	$i++;
}
?>
</tr>
</table>
</td></tr>
<? endif; ?>
</table>
<table width="100%" cellpadding="5" cellspacing="0" class='body'>
<tr><td>
<? $func(); ?>
</td></tr>
</table>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='copyright'>
<? if ($menu == 'main') : ?>
<!-- SpyLOG f:0211 -->
<script language="javascript"><!--
Mu="u3070.01.spylog.com";Md=document;Mnv=navigator;Mp=0;
Md.cookie="b=b";Mc=0;if(Md.cookie)Mc=1;Mrn=Math.random();
Mn=(Mnv.appName.substring(0,2)=="Mi")?0:1;Mt=(new Date()).getTimezoneOffset();
Mz="p="+Mp+"&rn="+Mrn+"&c="+Mc+"&t="+Mt;
if(self!=top){Mfr=1;}else{Mfr=0;}Msl="1.0";
//--></script><script language="javascript1.1"><!--
Mpl="";Msl="1.1";Mj = (Mnv.javaEnabled()?"Y":"N");Mz+='&j='+Mj;
//--></script><script language="javascript1.2"><!--
Msl="1.2";Ms=screen;Mpx=(Mn==0)?Ms.colorDepth:Ms.pixelDepth;
Mz+="&wh="+Ms.width+'x'+Ms.height+"&px="+Mpx;
//--></script><script language="javascript1.3"><!--
Msl="1.3";//--></script><script language="javascript"><!--
My="";My+="<a href='http://"+Mu+"/cnt?cid=307001&f=3&p="+Mp+"&rn="+Mrn+"' target='_blank'>";
My+="<img src='http://"+Mu+"/cnt?cid=307001&"+Mz+"&sl="+Msl+"&r="+escape(Md.referrer)+"&fr="+Mfr+"&pg="+escape(window.location.href);
My+="' border=0 width=88 height=63 alt='SpyLOG'>";
My+="</a>";Md.write(My);//--></script><noscript>
<a href="http://u3070.01.spylog.com/cnt?cid=307001&f=3&p=0" target="_blank">
<img src="http://u3070.01.spylog.com/cnt?cid=307001&p=0" alt='SpyLOG' border='0' width=88 height=63 >
</a></noscript>
<!-- SpyLOG -->
<? else: ?>
<!-- SpyLOG f:0211 --> 
<script language="javascript"><!-- 
Mu="u3070.01.spylog.com";Md=document;Mnv=navigator;Mp=1; 
Mn=(Mnv.appName.substring(0,2)=="Mi")?0:1;Mrn=Math.random(); 
Mt=(new Date()).getTimezoneOffset(); 
Mz="p="+Mp+"&rn="+Mrn+"&t="+Mt; 
My=""; 
My+="<a href='http://"+Mu+"/cnt?cid=307001&f=3&p="+Mp+"&rn="+Mrn+"' target='_blank'>"; 
My+="<img src='http://"+Mu+"/cnt?cid=307001&"+Mz+"&r="+escape(Md.referrer)+"&pg="+escape(window.location.href)+"' border=0 width=88 height=31 alt='SpyLOG'>"; 
My+="</a>";Md.write(My);//--></script><noscript> 
<a href="http://u3070.01.spylog.com/cnt?cid=307001&f=3&p=1" target="_blank"> 
<img src="http://u3070.01.spylog.com/cnt?cid=307001&p=1" alt='SpyLOG' border='0' width=88 height=31 > 
</a></noscript> 
<!-- SpyLOG -->
<? endif; ?>
</td>

<td class='copyright'>
<?=$copyright?>
</td>
</tr>
</table>
</body>
</html>
<?
   std_finish();
}

function build_title($page_title, $menu)
{
	global $main_menu, $site_title, $s_pohody_ru;
	$title = array();
	array_push($title, $s_pohody_ru.$site_title);
	if ($menu != 'main')
	{
	   array_push($title, $main_menu[$menu]['title']);
	   if ($page_title != $main_menu[$menu]['title'])
	   	array_push($title, $page_title);
	}
	return join(' &gt;&gt; ',$title);
}

function block($title, $content, $width = false, $align = false, $nowrap = false)
{
?>
<table class='block' border='0' cellspacing='0' cellpadding='0' <?=$width?"width='$width'":''?> align='center'>
<tr>
<td>
  <table class='header' border='0' cellspacing='0' cellpadding='0' width='100%'>
  <tr height='18'>
  <td width='18' class='left'></td>
  <td class='header'><?=$title?$title:'&nbsp;'?></td>
  <td width='18' class='right'></td> 
  </tr>
  </table>
</td>
</tr>
<tr>
<td class='content' <?=$align?"style='text-align: $align'":''?> <?=$nowrap ? 'nowrap' : ''?> >
<?=$content?>
</td>
</tr>
</table>
<?
}

function shownav()
{
  global $nav;
  if (count($nav) < 3)
    return;
?><tr><td><p class='nav'><?
  for ($i = 0; $i < count($nav); $i++)
  {
  	?><a href='<?=$nav[$i]['url']?>'><?=$nav[$i]['title']?></a><?
  	if ($i != count($nav)-1)
  		{ ?> &gt;&gt; <? }
  }
  ?>:</p></td></tr><?
}

function tmpl_people_info($info)
{
  $result = '';
  if ($info['email'] != '')
	  $result .= "<b>e-mail</b>: <a href='mailto:{$info['email']}'>{$info['email']}</a><br>";
  if ($info['homepage'] != '')
	  $result .= "<b>страничка</b>: <a href='{$info['homepage']}'>{$info['homepage']}</a><br>";
  return $result;
}

function tmpl_people_pohody($info)
{
  global $con;
  $r = $con->query_list(
        "SELECT pohody.id AS id, rivers.name AS name, pohody.year AS year FROM ekipazh INNER JOIN ".
  	"comands ON ekipazh.idekipazh = comands.ekipazh INNER JOIN ".
  	"pohody ON comands.idpohod = pohody.id INNER JOIN rivers ".
  	"ON rivers.id = pohody.river ".
  	"WHERE ekipazh.idpeople = {$info['id']} ORDER BY pohody.year, pohody.number", false);
  if (count($r) == 0)
  	return '';

  $result = '<ul class="img">';
  foreach ($r as $row)
  {
  	$result .= "<li><a href='/travels.phtml?id={$row['id']}'>{$row['name']} '{$row['year']}</a></li>";
  }

  $result .= '</ul>';

  return $result;
}

function tmpl_pohod_info($info)
{
   $result = '';
   if ($info['river'])
   	$result .= "<b>Река</b>: <a href='/rivers.phtml?id={$info['river_id']}'>{$info['river']}</a><br>";
   if ($info['season'])
   	$result .= "<b>Сезон</b>: {$info['season']}<br>";
   if ($info['year'])
   	$result .= "<b>Год</b>: {$info['year']}<br>";
   if ($info['captain'])
   	$result .= "<b>Капитан похода</b>: <a href='/people.phtml?id={$info['captain_id']}'>{$info['captain']}</a><br>";
   return $result;
}

function tmpl_pohod_command($info)
{
   global $con;
   $r = $con->query_list("SELECT boats.name AS boat, boats.type AS type, ".
      "people.name AS name, people.id AS id FROM comands ".
      "INNER JOIN ekipazh ON comands.ekipazh = ekipazh.idekipazh INNER JOIN ".
      "people ON people.id = ekipazh.idpeople INNER JOIN boats ON comands.boat = boats.id ".
      "WHERE comands.idpohod = {$info['id']} ORDER BY boats.name, people.name",
      false);
   $i = array();
   foreach ($r as $man)
   	$i["<b>{$man['boat']}</b> ({$man['type']})"][] = array('name'=>$man['name'],'id'=>$man['id']);
   if (count($i) > 0)
   {
   	$result = '<ul class="img2">';
   	foreach($i as $boat => $ekipazh)
   	{
   		$result .= "<li>$boat\n<ul class='img'>";
   		foreach ($ekipazh as $man)
   			$result .= "<li><a href='/people.phtml?id={$man['id']}'>{$man['name']}</a></li>";
   		$result .= "</ul></li>";
   	}
   	$result .= '</ol>';
   	return $result;
   }
   else
   	return '';

}

function tmpl_thumbnail($info)
{
	return sprintf("<a href='/pic.phtml?id=%d' target='_blank'>".
	       "<img src='/images/s%04d.jpg' ".
	       "width='%d' height='%d'>".
	       "<br>%s</a>",$info['id'],$info['id'],$info['thumb_width'],
	         $info['thumb_height'],$info['description']);
   //<a href='javascript:void(0)' ".onClick='openImage(\"/images/i%04d.jpg\")'>
}

function tmpl_river_pohody($info)
{
	global $con;
	$r = $con->query_list("SELECT pohody.id AS id, pohody.year AS year, ".
	   "pohody.season AS season FROM pohody WHERE pohody.river = {$info['id']}", false);
	if ($r)
	{
		$result = '<ul class="img">';
		foreach ($r as $pohod)
			$result .= "<li><a href='/travels.phtml?id={$pohod['id']}'>{$pohod['season']} {$pohod['year']}</a></li>";
		$result .= '</ul>';
		return $result;
	}
	else return '';
		
}

function tmpl_days_nav($days)
{
	$result = '<ul class="img">';
	for ($i = 0; $i < count($days); $i++)
		$result .= "<li><a href='#day".($i+1)."'>{$days[$i]}</a></li>";
	$result .= '</ul>';
	return $result;
}

function img_name($id)
{
  return sprintf("i%04d.jpg",$id);
}

function thumb_name($id)
{
  return sprintf("s%04d.jpg",$id);
}

function reformat($line)
{
  global $para, $con, $days;

  $replace_left = array(
      '/\{b(.*?)\}/s',
      '/\{i(.*?)\}/s',
      '/\{q(.*?)\}/s',
      '/\{l(.*?)\}/s',
      '/\{r\}/s',
      '/\{d (.*?)\}/se',
      '/\{p(\d+?)\}/se',
      '/\{c(\d+?) (.*?)\}/se',
      '/\{t(\d+?)\}/se',
      '/\{smile(\d)\}/s',
      '/\{t(\d+?)@(\d+?)\}/se',
  );

  $replace_right = array(
      '<b>$1</b>',
      '<i>$1</i>',
      '<li>$1</li>',
      '<ol>$1</ol>',
      '<br />',
      '"<a name=\"day".array_push($days, "$1")."\"><span class=\"day\">$1</span></a>"',
      '"<a href=\"/people.phtml?id=$1\">".$con->property("SELECT name FROM people WHERE id=$1")."</a>"',
      '"<span class=\"comment\"><a href=\"/people.phtml?id=$1\">"'.
         '.$con->property("SELECT name FROM people WHERE id=$1")."</a>: '.
         '$2</span>"',
      '"<a href=\"/travels.phtml?id=$1\">".$con->property("SELECT CONCAT(rivers.name,\" \'\",pohody.year) FROM pohody INNER JOIN rivers ON pohody.river=rivers.id WHERE pohody.id=$1")."</a>"',
      '<img src="/design/smile$1.gif" width="16" height="12">',
      '"<a href=\"/travels.phtml?id=$1&doc=$2\">".$con->property("SELECT CONCAT(rivers.name,\" \'\",pohody.year) FROM pohody INNER JOIN rivers ON pohody.river=rivers.id WHERE pohody.id=$1")."</a>"',
  );

  $line = preg_replace($replace_left,$replace_right, $line);
  if (preg_match('/\{m(\d+)\}/',$line, $matches))
  {
  	$para = (integer)$matches[1];
  	$line = preg_replace('/\{m(\d+)\}/','',$line);
  }	
  return $line;
}

function reformat_author($id)
{
  global $con;
  return "<i>Автор дневника</i>: <a href=\"/people.phtml?id=$id\">".$con->property("SELECT name FROM people WHERE id=$id")."</a>";
}

function excerpt($text)
{
  $lines = explode("\n", $text);

  $good_lines = array();
  foreach ($lines as $line)
  {
  	if (strlen($line) > 80 && !preg_match('/^{/',$line))
  		array_push($good_lines, $line);
  }

  if (count($good_lines) == 0)
        return false;
  else if (count($good_lines) == 1)
  	return cutdown($good_lines[0]);
  else
  	return cutdown($good_lines[mt_rand(0,count($good_lines)-1)]);
}

function cutdown($line)
{
  if ($line)
  {
  	$words = preg_split('/[\s]+/', $line);
  	$len = min(count($words), 30);
  	return join(' ', array_slice($words,0,$len))."...";
  }
  else return '';
}

function tmpl_page_block($min_page, $max_page, $cur_page, $url)
{
   if ($max_page - $min_page < 1)
   	return;

   global $s_pages;

   $text = '<div>';

   for ($i = $min_page; $i <= $max_page; $i++)
   {
   	if ($i != $cur_page)
   		$text .= "<a href='$url&page=$i'>$i</a>";
   	else $text .= "<span class='sel'>$i</span>";
   	$text .= '&nbsp;&nbsp;';
   }

   $text .= '</div>';
   ?><div style='text-align:center'><?
   block($s_pages, $text,false,"center");
   ?></div><?

}

function show_gallery($photos, $url, $page)
{
  global $gallery_w, $gallery_h;

  $gallery_page = $gallery_w*$gallery_h;
  $skip = ($page-1)*$gallery_page;
  $page_count = (integer)((count($photos) + $gallery_page - 1) / $gallery_page);

  reset($photos);
  while ($skip-- > 0)
  	each($photos);

  tmpl_page_block(1, $page_count, $page, $url);

  $todo = $gallery_page;
  $col = 0;

  ?><p><table class='photos' width='90%' cols='<?=$gallery_w?>' align='center'><?

  while (($todo-- > 0) && (list($id, $info) = each($photos)))
  {
  	if ($col == 0) // new line
  		{ ?><tr valign='top'><? }
  	$col++;
  	?><td><a href="<?=$url?>&page=<?=$page?>&id=<?=$id?>">
  	  <img src="/images/<?=thumb_name($id)?>" width="<?=$info[1]?>"
  	         height="<?=$info[2]?>"><br><?=$info[0]?></a>
  	   <?=($info[3] ? ($info[0] ? " (" : "").
  	        "<a href='/travels.phtml?id=$info[4]'>$info[3]</a>".
  	        ($info[0] ? ")" : '') : '')?></td><?
  	if ($col == $gallery_w)
  		{ ?></tr><?  $col = 0; }
  }

  if ($col != 0)
  {
  	while ($col++ != $gallery_w)
  	{ ?><td>&nbsp;</td><? }
  	?></tr><?
  }

  ?></table></p><?

  tmpl_page_block(1, $page_count, $page, $url);
}

function show_gallery_picture($photos, $url, $page, $id)
{
  global $s_prev, $s_next, $s_back, $s_no_desc;

  reset($photos);

  while ((list($key, $info) = each($photos)) && ($key != $id)) ;

  if (!$key)
  	return;

  $nav = '';
  if (!prev($photos))
    end($photos);

  if (prev($photos))
  {
  	foreach (explode('&nbsp;',$s_prev) as $part)
  		$nav .= "<a href='$url&page=$page&id=".key($photos)."'>$part</a>";
  	next($photos);
  }
  else
  {
  	reset($photos);
        $nav .= $s_prev;
  }

  foreach (explode('&nbsp;',$s_back) as $part)
	  $nav .= "   <a href='$url&page=$page'>$part</a>   ";

  if (next($photos))
  {
  	foreach (explode('&nbsp;',$s_next) as $part)
  		$nav .= "<a href='$url&page=$page&id=".key($photos)."'>$part</a>";
  	prev($photos);
  }
  else $nav .= $s_next;

  $text = $nav.
      "<br><img src='/images/".img_name($id)."' width='{$info[1]}' height='{$info[2]}'><br>".
       $nav;
  

  block($info[0]?$info[0]:$s_no_desc, $text,false,"center");
}

function tmpl_index_news()
{
   global $s_news, $con;

   $news = $con->query_list("SELECT when_ AS `when`, text FROM news ORDER BY `when` DESC LIMIT 0,5", false);

   $text = '<ul class="img">';
   foreach ($news as $new)
   {
   	$text .= '<li>';
   	list($year, $month, $day) = sscanf($new['when'],"%d-%d-%d");
   	$text .= sprintf('<b>%02d.%02d.%04d</b> - %s',$day,$month,$year,reformat($new['text']));
   	$text .= '</li>';
   }
   $text .= '</ul>';

   block($s_news, $text, "100%");
}

function tmpl_index_pohody()
{
   global $s_travels, $con;

   $pohody_list = $con->qquery("SELECT pohody.id AS pohody_id, pohody_docs.iddoc AS doc_id, ".
       "photos.id AS id, photos.thumb_width AS width, photos.thumb_height AS height FROM pohody ".
       "INNER JOIN pohody_docs ON pohody.id = pohody_docs.idpohod ".
       "INNER JOIN photos ON photos.idpohod = pohody.id ORDER BY rand() LIMIT 0,1");

   $pohody_id = $pohody_list['pohody_id'];

   $pohody_info = $con->qquery("SELECT CONCAT(rivers.name,\" '\",pohody.year) AS name ".
	 "FROM pohody INNER JOIN rivers ON rivers.id = pohody.river WHERE pohody.id = $pohody_id");
   $description = $con->property("SELECT body FROM documents WHERE id = ".$pohody_list['doc_id']);

   $imgname = thumb_name($pohody_list['id']);
   $excerpt = reformat(excerpt($description));

   $text = <<<EOF
<table cols='2' rows='1' width='100%' cellspacing='0' cellpadding='0'>
<tr><td width='{$pohody_list['width']}'>
<a href='/pic.phtml?id={$pohody_list['id']}' target='_blank'>
<img src='/images/$imgname' width='{$pohody_list['width']}' height='{$pohody_list['height']}'>
</a>
</td><td style='padding: 5px'>
<p style='text-align: justify' class='nolink'>
<a href='/travels.phtml?id=$pohody_id&doc={$pohody_list['doc_id']}'>$excerpt</a>
</p>
</td></tr>
</table>
EOF;


   block("<span style='font-weight:normal'>$s_travels:</span> {$pohody_info['name']}", $text, "100%");
}

function tmpl_index_rivers()
{
   global $s_rivers, $con;

   $rivers_list = $con->qquery("SELECT rivers.id AS river_id, ".
       "photos.id AS id, photos.thumb_width AS width, photos.thumb_height AS height FROM rivers ".
       "INNER JOIN photos ON photos.idriver = rivers.id ORDER BY rand() LIMIT 0,1");

   $river_id = $rivers_list['river_id'];

   $river_info = $con->qquery("SELECT name, description FROM ".
       "rivers WHERE id = $river_id");

   $imgname = thumb_name($rivers_list['id']);
   $excerpt = reformat(excerpt($river_info['description']));

   $text = <<<EOF
<table cols='2' rows='1' width='100%' cellspacing='0' cellpadding='0'>
<tr><td width='{$rivers_list['width']}'>
<a href='/pic.phtml?id={$rivers_list['id']}' target='_blank'>
<img src='/images/$imgname' width='{$rivers_list['width']}' height='{$rivers_list['height']}'>
</a>
</td><td style='padding: 5px'>
<p style='text-align: justify' class='nolink'>
<a href='/rivers.phtml?id=$river_id'>$excerpt</a>
</p>
</td></tr>
</table>
EOF;


   block("<span style='font-weight:normal'>$s_rivers:</span> {$river_info['name']}", $text, "100%");
}

function tmpl_index_people()
{
   global $s_people, $con;

   $people_list = $con->qquery("SELECT people.name AS name, people.id AS people_id, people.fullname AS fullname, ".
       "photos.id AS id, photos.thumb_width AS width, photos.thumb_height AS height FROM people ".
       "INNER JOIN photos ON photos.idman = people.id ORDER BY rand() LIMIT 0,1");

   $text = "<div style='text-align: center'><a href='/people.phtml?id=".
       "{$people_list['people_id']}'>".
       "<img src='/images/".thumb_name($people_list['id']).
       "' width='{$people_list['width']}' height='{$people_list['height']}'></a>".
       "<br><i>{$people_list['fullname']}<i></div>";

   block("<span style='font-weight:normal'>$s_people:</span> {$people_list['name']}", $text, "100%");
}

function tmpl_index_search()
{
   global $con, $s_search, $s_travels, $s_rivers, $s_people, $s_photos;
   $text = <<< EOF
       <form method="post">
       <table border='0'>
       <tr><td><b>Искать:<b></td><td><input type='text' name='search' size=30></td></tr>
       <tr><td> </td><td>
       <table width='100%'>
       <tr>
       <td nowrap width='50%'><input type='checkbox' name='travels' checked>$s_travels</td>
       <td nowrap width='50%'><input type='checkbox' name='rivers' checked>$s_rivers</td>
       </tr>
       <tr>
       <td nowrap width='50%'><input type='checkbox' name='people' checked>$s_people</td>
       <td nowrap width='50%'><input type='checkbox' name='photos' checked>$s_photos</td>
       </tr>
       </table>
       </td></tr>
       <tr><td> </td><td><input type='submit' value='$s_search'></tr>
       </table>
       <form>
EOF;
   block($s_search, $text, "100%");
}

function tmpl_index_adv()
{
   global $s_info;
   $text = <<< EOF
    <ul class='img'>
    <li>Вы знаете, что еще должно быть на этом сайте?
    <li>У Вас есть описание/дневник/фотографии похода, которые Вы бы хотели выложить
    на этот сайт?
    <li>Вы просто хотите нам помочь?
    </ul>
    <b>Пишите<b>: <a href='mailto:info@pohody.ru'>info@pohody.ru</a>
EOF;
   block($s_info, $text, "100%");
}

function tmpl_diaries_list(&$descriptions, $desc_id, $travel_id)
{
	$result = '<ul class="img">';
	foreach ($descriptions as $id => $info)
	   if ($desc_id == $id)
	   	$result .= "<li><b>{$info[2]}</b></li>";
	   else
	   	$result .= "<li><a href='/travels.phtml?id=$travel_id&doc=$id'>{$info[2]}</a></li>";
	$result .= '</ul>';
	return $result;
}

function reformat_book($line)
{
  $replace_left = array(
      '/\{/',
      '/\}/',
      '/<header(\d)>(.*?)<\/header>/se',
      '/<line \/>/s',
      '/<b>(.*?)<\/b>/s',
      '/<i>(.*?)<\/i>/s',
      '/<img file="(.*?)" (.*?)\/>/s',
      '/<page \/>/se',
      '/</s',
      '/>/s',
  );

  $replace_right = array(
      '',
      '',
      '"{div class=\"header$1\"}{a name=\"".push_header($1,"$2")."\"}$2{/a}{/div}"',
      '{br}',
      '{b}$1{/b}',
      '{i}$1{/i}',
      '{img src="/pics/$1" $2}',
      'next_page()',
      '&lt;',
      '&gt;',
  );

  $line = preg_replace($replace_left, $replace_right, $line);
  $line = preg_replace(array('/\{/','/\}/'), array('<','>'), $line);
  return $line;
}

function next_page()
{
  global $cur_page;

  $cur_page++;
}

function push_header($level, $title)
{
  global $headers, $header_id, $cur_page;

  $info = array('title' => $title, 'anchor' => "anchor".$header_id++,
  	    'level' => $level, 'page' => $cur_page, 'children' => array());

  $h = &$headers;
  do
  {
  	$a = end($h);
  	if (count($h) == 0 || $a['level'] == $level) // we should push it here
  	{
  		array_push($h, $info);
  		break;
  	}
  	$h = &$h[count($h)-1]['children'];
  }
  while (1);

  return $info['anchor'];
}

function format_book(&$text)
{
  global $cur_page, $headers, $header_id;

  $cur_page = 1;
  $header_id = 1;
  $headers = array();
  $pages = array();
  $lines = explode("\n", $text);

  foreach ($lines as $line)
  {
  	$new = reformat_book($line);
  	if (!preg_match('/^<(div|p)/', $new))
  		$new = '<p>'.$new.'</p>';
  	$pages[$cur_page] .= $new;
  }

  return $pages;
}

function draw_headers($url, &$headers)
{
   global $page;
   if (count($headers) == 0)
   	return "";
   $result = '';
   $level = $headers[0]['level'] - 1;
   $level = ($level == 1) ? "" : (string)$level;
   $result .= "<ul class='img$level'>";
   foreach($headers as $header)
   {
   	$result .= "<li><a href='$url&page={$header['page']}#{$header['anchor']}'>".
   	   ($page == $header['page'] ? '<b>' : '').
   	  htmlspecialchars($header['title']).
   	   ($page == $header['page'] ? '</b>' : '').
   	  "</a>";
   	$result .= draw_headers($url, $header['children']);
   	$result .= "</li>";
   }
   $result .= "</ul>";
   return $result;
}
?>