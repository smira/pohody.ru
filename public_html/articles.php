<?php

chdir($_SERVER["DOCUMENT_ROOT"]."/..");
require('system/core.php');

$id = nav_get('id', 'integer', -1);
$page = nav_get('page', 'integer', 1);

if ($id == -1)
	html($s_articles, 'articles', 'articles_body');
else
{
	$row = $con->qquery("SELECT * FROM books ".
	    "WHERE books.id = $id");
	html($row['author'].'. '.$row['title'], 'articles', 'articles_article');
}


function articles_body()
{
    global $s_articles, $s_books, $con;
    ?><h1><?=$s_articles?></h1><?php
    $books1 = $con->query_list("SELECT topic, id, title, author, additional, source FROM books WHERE kind = 0 ORDER BY added DESC",false);
    $books = array();
    foreach ($books1 as $info)
    {
    	$books[$info['topic']][$info['id']] = $info;
    }
    ?><ul class='img'><?php
    foreach ($books as $topic => $book_list)
    {
    	?><li><b><?=htmlspecialchars($topic)?></b><?php
    	?><table class='list' cellspacing='0'>
    	  <tr>
    	    <th class='dot'>&nbsp;</th><th class='title'>Название</th><th>Автор</th><th>Описание</th>
    	  </tr><?php
    	foreach ($book_list as $book)
    	{
    		?><tr valign='top'>
    		    <td class='dot'><img src='/design/dot2.gif' width='6' height='16'></td>
    		    <td class='title' nowrap><b><a href='/articles.php?id=<?=$book['id']?>&page=1'><?=htmlspecialchars($book['title'])?></a></b></td>
    		    <td nowrap><?=htmlspecialchars($book['author'])?></td>
    		    <td><?=htmlspecialchars($book['additional'])?></td>
    		  </tr>
    		<?php
    	}
    	?></table><?php
    	?></li><?php
    }
    ?></ul><?php
    ?><h1><?=$s_books?></h1><?php
    $books1 = $con->query_list("SELECT topic, id, title, author, additional, source FROM books WHERE kind = 1 ORDER BY added DESC",false);
    $books = array();
    foreach ($books1 as $info)
    {
    	$books[$info['topic']][$info['id']] = $info;
    }
    ?><ul class='img'><?php
    foreach ($books as $topic => $book_list)
    {
    	?><li><b><?=htmlspecialchars($topic)?></b><?php
    	?><table class='list' cellspacing='0'>
    	  <tr>
    	    <th class='dot'>&nbsp;</th><th class='title'>Название</th><th>Автор</th><th>Описание</th>
    	  </tr><?php
    	foreach ($book_list as $book)
    	{
    		?><tr valign='top'>
    		    <td class='dot'><img src='/design/dot2.gif' width='6' height='16'></td>
    		    <td class='title' nowrap><b><a href='/articles.php?id=<?=$book['id']?>&page=1'><?=htmlspecialchars($book['title'])?></a></b></td>
    		    <td nowrap><?=htmlspecialchars($book['author'])?></td>
    		    <td><?=htmlspecialchars($book['additional'])?></td>
    		  </tr>
    		<?php
    	}
    	?></table><?php
    	?></li><?php
    }
    ?></ul><?php
}

function articles_article()
{
	global $row, $page, $id, $headers, $s_contents;
		
	$pages = format_book($row['content']);

	?><div class="vynos" style='width: 300px'><table cellspacing=0 cellpadding=0><tr><td><?php
	if (count($headers) > 0)
	{
		block($s_contents, draw_headers("/articles.php?id=$id", $headers));
	}
	?></td></tr></table></div><?php

        if (count($pages) > 1)
  		tmpl_page_block(1, count($pages), $page, "/articles.php?id=$id");
		
	echo $pages[$page];

	if (count($pages) > 1)
  		tmpl_page_block(1, count($pages), $page, "/articles.php?id=$id");
}


?>
