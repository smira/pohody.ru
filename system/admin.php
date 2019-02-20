<?php

session_start();

function admin_html()
{
     global $admin, $site_title, $_SESSION, $section_list, $section, $default_section;
     
     $section = nav_get_list('section', array_keys($admin['sections']), $default_section, true);
	
     $action = nav_get('action', 'string', '');
     switch($action)
     {
        case 'auth' 	: admin_authorize(); break;
        case 'logout' 	: admin_logout(); break;
     }

     if (admin_authorized())
     	admin_filter_process($section);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru" dir="ltr">
<head>
<title><?=$site_title?> :: Администрирование :: <?=$admin['sections'][$section]['title']?></title>
<link rel="stylesheet" type="text/css" href="/admin/admin.css" />
<script src="/admin/admin.js" type="text/javascript" language="javascript"></script></head>
<body>
<?php
   if (!admin_authorized())
   {
   	admin_auth_form();
   }
   else
   {
   	$section_list = array_keys($admin['sections']);
        $section_list = array_filter($section_list, 'admin_section_priv');
   	if (!in_array($section, $section_list))
   		$section = reset($section_list);

   	switch($action)
	   {
	    case 'add'		:
	    case 'edit'		: admin_modify($action, $section); break;
	    case 'delete'	: admin_delete($section); break;
	    case 'filter'	: admin_filter($section); break;
	    case 'delfilter'	: admin_delfilter($section); break;
	    case 'goto'		: admin_goto($section); break;
	    case 'alias'	: admin_make_alias($section); break;
	   }
	if (preg_match('/^special_/', $action))
	{
		$func = 'admin_'.$action;
		$func($section);
		return;
	}
   
   	admin_main_content($section);
   }
?>
</body>
</html>
<?php
}

/////////////////////////////////////////////////
/// Авторизация
/////////////////////////////////////////////////

function admin_authorized()
{
   global $_SESSION;
   return $_SESSION['uid'] > 0;
}

function admin_auth_form()
{
?>
<h1>Авторизация</h1>
<form method='post' action='?action=auth'>
<center>
<table class='form'>
<tr><td>Имя:</td><td><input type='text' name='user'></td></tr>
<tr><td>Пароль:</td><td><input type='password' name='password'></td></tr>
<tr><td>&nbsp;</td><td><input type='submit' value='&lt; Вход &gt;' class='button'></td></tr>
</table>
</center>
</form>
<?php
}
 
function admin_authorize()
{
   global $con;
   $user = $con->safe_str($_POST['user']);
   $password = $con->safe_str(md5($_POST['password']));
   $result = $con->qquery("SELECT * FROM user WHERE name = '$user' AND password = '$password'");
   if (!$result)
   	return;
   $_SESSION['uid'] = $result['id'];
   $_SESSION['user'] = $result['name'];
   $priv_ = explode(' ', $result['priv']);
   foreach ($priv_ as $p)
   	$priv[$p] = true;
   $_SESSION['priv'] = $priv;
   std_redirect('/admin/');
}

function admin_logout()
{
   session_destroy();
   std_redirect('index.phtml');
}

function admin_section_priv($section)
{
	global $admin;

	return !$admin['sections'][$section]['priv'] ||
		$_SESSION['priv'][$admin['sections'][$section]['priv']];
}

/////////////////////////////////////////////////
/// Основное содержимое
/////////////////////////////////////////////////

function admin_main_content($section)
{
	global $admin, $from, $limit, $sort, $asc, $rows_per_screen, $site_title, $section_list;

	$view = $admin['sections'][$section]['view'];
	$static = $admin['views'][$view]['static'];
	$from = nav_get('from', 'integer', 0);

	$default_limit = isset($admin['views'][$view]['def_rows_count']) ? $admin['views'][$view]['def_rows_count'] : $rows_per_screen;
	if(is_array($default_limit))
	{
		$def = $default_limit[0];
		settype($default_limit, "int");
		$default_limit = $def;

	}                       	
	$limit = nav_get('limit', 'integer', $rows_per_screen);
	$default_sort = $admin['views'][$view]['sort'] ? $admin['views'][$view]['sort'] : '';
	$default_asc = isset($admin['views'][$view]['asc']) ? $admin['views'][$view]['asc'] : 1;
	$sort = nav_get_list('sort', @array_keys($admin['views'][$view]['fields']), $default_sort, true);
	$asc = nav_get('asc', 'integer', $default_asc);
?>
<table cellspacing='0' cellpadding='0' border='0' width='100%'>
<!-- заголовок -->
<tr><td>
  <table cellspacing='0' cellpadding='0' border='0' width='100%' class='header'>
    <tr>
      <td nowrap align='left'>
        <form name='header' method='get' action='/admin/' id='section_form'>
          <b>Раздел:</b>
         <select name='section' onchange='document.forms["section_form"].submit()'>
            <?php
               foreach($admin['sections'] as $name => $info)
               {
                   if (!in_array($name, $section_list))
                   	continue;
                   ?><option value='<?=$name?>' <?=$name==$section?'selected':''?> ><?=$info['title']?></option><?php
               }
            ?>
         </select>
         </form>
      </td>
      <td align='center' width='100%'>
         <b>Администрирование</b>: <?=$site_title?>
      </td>
      <td nowrap align='right'>
      	<a href='/admin/?action=logout'>Выход</a>
      </td>
    </tr>
  </table>
</td></tr>
<!-- конец заголовка -->
<?php
	if ($admin['sections'][$section]['dialog'])
	{
		$admin['sections'][$section]['dialog']();
	}
	else
	{
?>
<!-- view -->
<tr><td>
   <form name='table' style='margin: 0px'>
   <table cellspacing='0' width='100%' class='table'>
     <?php admin_prepare_field_map($admin['views'][$view]); ?> 
<!-- хедер текущего view (заголовок таблицы) -->
     <?php admin_view_header($admin['views'][$view]); ?>
<!-- конец хедера -->
<!-- содержимое таблицы -->
     <?php admin_show_sql($admin['views'][$view], admin_build_sql($admin['views'][$view])) ?>
<!-- конец содержимого таблицы -->
<!-- футер текущего view (статистика, прокрутка?) -->
     <?php $static ? 1 : admin_view_footer($admin['views'][$view]); ?>
<!-- конец футера -->
<!-- строка с кнопками действий -->
     <?php $static ? 1 : admin_buttons($admin['views'][$view]); ?>
<!-- конец строки с кнопками действий -->
   </table>
   </form>
<!-- фильтр -->
<tr><td>
   <?php if (count($admin['views'][$view]['filters']) > 0) admin_filter_show($section); ?>
</td></tr>
<!-- конец фильтра -->
</td></tr>
<!-- конец view -->
<?php
	}
?>
</table>
<?php
}

function admin_prepare_field_map($view)
{
	global $admin, $field_map;
	$field_map = array();
        foreach ($view['fields'] as $name => $field)
        	if (!$admin['fields'][$field]['hide'])
        		$field_map[$name] = $field;
}

function admin_view_header($view)
{
	global $admin, $field_map;

?><tr><?php
        ?><th width='20'><a href='<?=nav_link(array('sort' => false, 'asc' => false ))?>'>б/с</a></th><?php
	foreach ($field_map as $name => $field)
		admin_view_header_field($name, $view, $admin['fields'][$field]);
?></tr><?php
}

function admin_view_header_field($name, $view, $field)
{
  global $sort, $asc;
  ?><th width='<?=$view['widths'][$name]?>'>
      <a href='<?=nav_link(array('sort' => $name, 'asc' => (integer)($name != $sort ? 1 : !$asc) ))?>'>
         <?=htmlspecialchars($field['title'])?></a><span class='order'><?=($name == $sort ? ($asc ? 'H':'G') : '' )?></span>
  </th><?php
}

function admin_show_sql($view, $query)
{
	global $con, $admin, $num_rows, $overall_rows, $row_active, $row_inactive, $field_map, $row;
	$result = $con->query($query);
	if (!$result)
	{
		echo $query,$con->error();
	}
	$num_rows = 0;
	$overall_rows = $static ? 0 : $con->property(admin_build_count_sql($view));
	while ($row = $result->fetch_array())
	{
	    ?><tr  onmouseover="setPointer(this, 'over', '<?=$row_inactive?>', '<?=$row_active?>', '<?=$row_active?>')"
	           onmouseout="setPointer(this, 'out',  '<?=$row_inactive?>', '<?=$row_active?>', '<?=$row_active?>')"
	           ><?php
	    ?><td bgcolor="<?=$row_inactive?>"><input type='checkbox' name='row<?=++$num_rows?>' value="<?=htmlspecialchars($row[$view['primary']])?>"></td><?php
            admin_lookup_fields($row, $view);
	    $rowstyle = $view['rowstyler']  ? $view['rowstyler']($row) : '';
	    foreach ($field_map as $name => $field)
	    {
		admin_print_field($row[$name],$admin['fields'][$field], $rowstyle);
	    }
	    ?></tr><?php
	}
}

function admin_print_field($value, $field, $rowstyle='')
{
	global $num_rows, $row_inactive, $row;
        $value = htmlspecialchars($value);
	if ($field['format'])
		foreach ($field['format'] as $func)
		{
			$func = 'format_'.$func;
			$value = $func($value, $field);
		}	
	?>
	<td <?=$field['align']?'align="'.$field['align'].'"':''?> onmousedown="document.forms.table.row<?=$num_rows?>.checked = !document.forms.table.row<?=$num_rows?>.checked" bgcolor="<?=$row_inactive?>" <?=$rowstyle?>>
	  <?=$value?>
	  <?php
		if ($field['alias_buttons'])
			admin_print_alias_buttons($row);
	  ?>
        </td>
        <?php
}

function admin_print_alias_buttons(&$row)
{
	?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
	echo admin_button('Алиас <->', nav_link(array('action' => 'alias', 'kind' => 'swap', 'from' => false, 'limit' => false,
					   'id' => $row['id'])), true);
	?>&nbsp;&nbsp;&nbsp;<?php
	echo admin_button('Алиас +-The', nav_link(array('action' => 'alias', 'kind' => 'the', 'from' => false, 'limit' => false,
					   'id' => $row['id'])), true);
}

function admin_view_footer($view)
{
	global $admin, $from, $num_rows, $overall_rows, $field_map, $section, $_SESSION;
	$filter = $_SESSION['filter'][$section];
	?><tr><th>&nbsp;</th><?php
	?><th align='left' colspan='<?=count($field_map)?>'>Показаны записи с <?=$from+1?> по <?=$from+$num_rows?> (из <?=$overall_rows?>)
	  <?php if (count($filter) > 0): ?> (+фильтр+) <?php endif; ?>
	</th><?php
	?></tr><?php
}

function admin_buttons($view)
{
	global $overall_rows, $from, $limit, $section, $field_map, $_SESSION;
	$last_page = max(0,floor(($overall_rows-1) / $limit) * $limit);
	?><tr><td class='buttons' colspan='<?=count($field_map)+1?>'><table border='0' cellspacing='0' cellpadding='0' width='100%' class='buttons'><tr><?php
	?><td class='buttons'  style='text-align: left'><?php
	echo admin_button("<<", nav_link(array('from' => 0)));
	echo "&nbsp;";
	if ($from > 0)
		echo admin_button("<", nav_link(array('from' => max(0,$from-$limit))));
        ?></td><td class='buttons'><?php
         echo admin_button("Добавить", nav_link(array('action'=>'add','from'=>false,'limit'=>false)), true);
  	 echo "&nbsp;";
         echo admin_button("Изменить", nav_link(array('action'=>'edit','from'=>false,'limit'=>false)), true, 'prepareEdit');
	 echo "&nbsp;";
         echo admin_button("Удалить",  nav_link(array('action'=>'delete','from'=>false,'limit'=>false)), true, 'prepareDelete');
	 echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
         //echo admin_button("Фильтр",  nav_link(array('action'=>'filter','from'=>false,'limit'=>false)), true);
	 //echo "&nbsp;";
         //echo admin_button("Без фильтра",  nav_link(array('action'=>'delfilter','from'=>false,'limit'=>false)), true);
         if (count($view['goto']) > 0)
         {
  	    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            foreach ($view['goto'] as $field => $goto)
            {
               global $admin;
               $info =  $admin['goto'][$goto];
               echo admin_button("К ".$info['label'],  nav_link(array('action'=>'goto','from'=>false,'limit'=>false,'link'=>$field)), true, 'prepareEdit');
	       echo "&nbsp;";
            }		
         }
	 if (is_array($view['buttons']))
	 {
		 foreach ($view['buttons'] as $action => $title)
               		echo admin_button($title,  nav_link(array('action'=>'special_'.$action,'from'=>false,'limit'=>false)), true, 'prepareMulti');
	         echo "&nbsp;";
	 }
	?></td><td class='buttons' style='text-align: right'><?php
	if(isset($view['def_rows_count']))
	{
			echo "&nbsp;";
			echo "&nbsp;";
			echo "&nbsp;";
	
		$ch_list = $view['def_rows_count'];
		?><SCRIPT language='javascript'>
			function onChangeRowsCount(obj)
			{
				theURL = obj.options[obj.selectedIndex].value; 
    		if (theURL) {	window.location = theURL;	}
    	}</SCRIPT>
    	<?php
		if(is_array($ch_list))
		{
			echo "Количество строк: <select onChange='onChangeRowsCount(this);'>";
			foreach($ch_list as $rows_count_item)
				echo "<option value = '".nav_link(array('limit' => $rows_count_item))."'".($rows_count_item==$limit?" selected":"").">".$rows_count_item."</option>";
			echo "</select>";			
		}
		else
		{
			if($ch_list != $limit)
				echo admin_button("Def. Rows", nav_link(array('limit' => $ch_list)));
		}
		if(is_array($default_limit))
			$defalut_limit = $default_limit[0];

			echo "&nbsp;";
			echo "&nbsp;";
			echo "&nbsp;";

	}
	if ($from < $last_page)
		echo admin_button(">", nav_link(array('from' => min($last_page,$from+$limit))));
	echo "&nbsp;";
	echo admin_button(">>", nav_link(array('from' => $last_page)));
	?></td><?php
	?></tr></table></td></tr><?php
}

function admin_button($text, $url, $newwindow = false, $func = false)
{
	global $popup_width, $popup_height;
	return "<input type='button' value='".htmlspecialchars($text)."' "
	       .($newwindow ? ' class="button" ' : '')."onclick='".
	   ($newwindow ?
	       ($func ? "$func(this.form, \"$url\", $popup_width, $popup_height)" : 
	        "window.open(\"".$url."\",\"admin_edit\",\"toolbar=no,scrollbars=yes,width={$popup_width},height={$popup_height},status=no,menubar=no,directories=no,resizable=yes\")")
	   : "document.location=\"$url\"")."' />";
}

/////////////////////////////////////////////////
/// SQL
/////////////////////////////////////////////////

function admin_in_table($field)
{
	global $admin;
	$field_desc = $admin['fields'][$field];
	return $field_desc['type'] != 'many2many';
}

function admin_condition($query, $view)
{
	global $con, $admin;

	$result = array();
	$used = array();
	foreach ($query as $field => $value)
	{
		if (preg_match('/^(.*)_o2m$/', $field, $matches))
		{
		   if (!$used[$matches[1]])
			$result [] = "`{$matches[1]}` = '".$con->safe_str($con->property(
				admin_lookup_reverse($admin['fields'][$view['fields'][$matches[1]]], $value)))."'";
		}
		else
			$result [] = "`$field` = '$value'";
		$used[$field] = true;
	}
	return $result;
}

function admin_quote_field($field)
{
	return "`{$field}`";
}

function admin_build_filter_where($view)
{
	global $admin, $_SESSION, $section;

	$filter = $_SESSION['filter'][$section];
	if ($filter && count($filter) > 0)
	{
	       return ' WHERE '.join(' AND ',admin_condition(array_map('mysql_escape_string', $filter), $view));
	}
	else
		return '';
}

function admin_build_sql($view)
{
	global $admin, $from, $limit, $sort, $asc, $primary, $_SESSION, $section;
	
	if ($view['static'])
		$query = $view['sql'];
	else
		$query = "SELECT ".join(', ',array_map('admin_quote_field', 
		                    array_keys(array_filter($view['fields'],'admin_in_table')))).
	         		" FROM {$view['table']} ";

	$query .= 
	         ($primary ? " WHERE {$view['primary']} = '$primary'" :
		    admin_build_filter_where($view)).
	         ($sort && admin_in_table($view['fields'][$sort])
	             ? " ORDER BY $sort ".($asc ? "ASC" : "DESC")  : '').
	         ($limit && !$view['static'] ? " LIMIT $from, $limit" : '');
	return $query;
}

function admin_build_count_sql($view)
{
	global $admin, $from, $limit, $sort, $asc, $primary, $_SESSION, $section;
	$filter = $_SESSION['filter'][$section];
	return "SELECT COUNT(*) FROM {$view['table']}".
	         ($primary ? " WHERE {$view['primary']} = '$primary'" :
	            admin_build_filter_where($view));
}

function admin_process_build_sql($edit, $view, $values)
{
	global $con, $admin, $field_nochange;
	$result = array();
	$query = $edit ? 'UPDATE ' : 'INSERT INTO ';
	$query .= $view['table'].' SET ';
	$fields = array();
	foreach($view['fields'] as $field => $field_name)
	{
		$field_desc = $admin['fields'][$field_name];
		if ($field_desc['type'] == 'auto')
			continue;
		if ($values[$field] == $field_nochange)
			continue;
		if ($field_desc['func'])
			$values[$field] = $field_desc['func']($values[$field]);
		if ($field_desc['type'] == 'one2many_' && !$values[$field] && $values[$field.'_o2m'])
		{
			$values[$field] = $con->safe_str($con->property(
				admin_lookup_reverse($field_desc, $values[$field.'_o2m'])));
		}
		if ($field_desc['type'] == 'many2many')
		{
			// удалить сначала значения
			$result[] =
			     "DELETE FROM {$field_desc['linktable']} WHERE ".
			     "{$field_desc['linkprimary']} = '%id%'";
			// теперь добавим
			foreach ($values[$field.'[]'] as $id => $value)
			{
				$result[] =
			     	 "INSERT INTO {$field_desc['linktable']} SET ".
			     	 "{$field_desc['linkprimary']} = '%id%', ".
			     	 "{$field_desc['linkforeign']} = '{$value}'";
			}
		}
		else
		{
			if ($values[$field] === false)
				$fields[] = "`$field` = NULL";
			else
				$fields[] = "`$field` = '".$con->safe_str($values[$field])."'";
		}
	}
	$query .= join(', ', $fields);
	if ($edit)
		$query .= " WHERE {$view['primary']} = '".$con->safe_str($values["__primary__"])."'";
	array_unshift($result, $query);
	return $result;
}

function admin_build_delete($view)
{
	global $primary, $admin;
	$result = array();
	foreach ($primary as $id => $name)
		$primary[$id] = "'".$name."'";
        $result[] = "DELETE FROM {$view['table']} WHERE {$view['primary']} IN (".
                 join(', ', $primary).")";
        foreach ($view['fields'] as $field => $field_name)
        {
        	$field_desc = $admin['fields'][$field_name];
        	if ($field_desc['type'] == 'many2many')
        	{
        		$result[] = "DELETE FROM {$field_desc['linktable']} WHERE ".
			     "{$field_desc['linkprimary']} IN (".
			     join(', ', $primary).")";
        	}
        }
        // проверить все поля, вдруг мы на них ссылаемся?
        foreach ($admin['fields'] as $name => $field_desc)
        {
        	if ($field_desc['type'] == 'many2many' && $field_desc['lookuptable'] == $view['table'])
        	{
        		$result[] = "DELETE FROM {$field_desc['linktable']} WHERE ".
			     "{$field_desc['linkforeign']} IN (".
			     join(', ', $primary).")";
        	}
        }

        return $result;
}

function admin_lookup_values($field)
{
	$query = "SELECT {$field['lookupkey']}, {$field['lookupresult']} FROM ".
	         "{$field['lookuptable']} ORDER BY {$field['lookupresult']}";
        return $query;
}

function admin_lookup_reverse($field, $value)
{
	global $con;

	$query = "SELECT `{$field['lookupkey']}` FROM ".
	         "{$field['lookuptable']} WHERE `{$field['lookupresult']}` =  '".$con->safe_str($value)."'";
	return $query;
}

function admin_lookup_fields(&$row, $view)
{
	global $admin, $field_map, $con;
        foreach ($field_map as $name => $field)
        {
        	$field_desc = $admin['fields'][$field];
        	if ($field_desc['type'] == 'many2many')
        	{
			$query = "SELECT {$field_desc['lookuptable']}.{$field_desc['lookupresult']} FROM ".
	                         "{$field_desc['lookuptable']} INNER JOIN {$field_desc['linktable']} ON ".
	                         "{$field_desc['linktable']}.{$field_desc['linkforeign']} = {$field_desc['lookuptable']}.{$field_desc['lookupkey']}".
	                         " WHERE {$field_desc['linktable']}.{$field_desc['linkprimary']} = '{$row[$view['primary']]}'";
	                $row[$name] = join(', ', $con->query_list($query));
        	}
        }
}

function admin_lookup_keys($field_desc, $view, $primary)
{
	$query = "SELECT {$field_desc['lookuptable']}.{$field_desc['lookupkey']} FROM ".
	"{$field_desc['lookuptable']} INNER JOIN {$field_desc['linktable']} ON ".
	"{$field_desc['linktable']}.{$field_desc['linkforeign']} = {$field_desc['lookuptable']}.{$field_desc['lookupkey']}".
	" WHERE {$field_desc['linktable']}.{$field_desc['linkprimary']} = '$primary'";
        return $query;
}

///////////////////////////////////////////////////
// Функции добавления/редактирования
///////////////////////////////////////////////////

function admin_modify($action, $section)
{
	global $admin, $con, $primary;
	$edit = $action == 'edit';
	$view = $admin['sections'][$section]['view'];
	if ($edit)
	{
		$primary = nav_get('primary', 'string');
		$remote = admin_prepare_remote($admin['views'][$view]);
	}
	else
	{
		$remote = $_SESSION['filter'][$section];
	}
	$form = admin_prepare_form($edit, $admin['views'][$view]);
        if (form_create($form, false, "admin", "", $remote, $html, $values, $errors, $form_tag))
        {
        	// форма уже запощена
        	admin_process_post($edit, $admin['views'][$view], $values);
        }
        else
        	admin_show_form($edit, $section, $form, $admin['views'][$view], $html, $values, $errors, $form_tag);
	exit();
}

function admin_prepare_form($edit, $view)
{
	global $admin, $con;
	$form = array();
	if ($edit)
		$form["__primary__"] = array('type'=>'hidden');
	foreach($view['fields'] as $field => $field_name)
	{
		$field_desc = $admin['fields'][$field_name];
		if ($field_desc['type'] == 'auto')
			continue;
		switch($field_desc['type'])
		{
			case 'text':  $form_field = array('type' => 'char'); break;
			case 'int' :  $form_field = array('type' => 'int' ); break;
			case 'date':  $form_field = array('type' => 'date'); break;
			case 'memo':  $form_field = array('type' => 'text'); break;
			case 'enum':  $form_field = array('type' => 'select', 'elements' => $field_desc['values']); break;
			case 'one2many' :
				      $form_field = array('type' => 'select', 'elements' => $con->query_list(admin_lookup_values($field_desc)));  break;
			case 'one2many_':
				      $form_field = array('type' => 'int'); break;
			case 'many2many':
                                      $form_field = array('type' => 'select', 'multiple' => true, 'elements' => $con->query_list(admin_lookup_values($field_desc))); break;
			default: std_error("Неизвестный тип поля ".$field_desc['type']. "($field => $field_name)");
		}
		$form_field['title'] = $field_desc['title'];
		if ($field_desc['type'] == 'date' && $field_desc['default_now'])
		        $form_field['default'] = date("Y-m-d");
		if (array_key_exists('default', $field_desc))
			$form_field['default'] = $field_desc['default'];
		if ($field_desc['size'])
			$form_field['size'] = $field_desc['size'];
		if ($field_desc['required'])
			$form_field['notnull'] = true;
		if ($field_desc['min'])
			$form_field['min'] = $field_desc['min'];
		if ($field_desc['max'])
			$form_field['max'] = $field_desc['max'];
		if ($field_desc['lines'])
			$form_field['lines'] = $field_desc['lines'];
		$form[$field.($field_desc['type'] == 'many2many' ?'[]':'')] = $form_field;
		if ($field_desc['type'] == 'one2many_')
		{
			$form[$field]['title'] .= ' [ID]';
			$form[$field.'_o2m'] = 
				array(
					'type' => 'char',
					'size' => '60',
					'title' => $field_desc['title'].' [Значение]',
				     );
					
		}
	}
	return $form;
}

function admin_show_form($edit, $section, $form, $view, $html, $values, $errors, $form_tag, $filter = false)
{
	global $admin, $action, $section;
	echo $form_tag;
	if ($form["__primary__"])
		echo $html["__primary__"];
	if ($action == 'filter' || $filter)
	{
		?><h1>Фильтр</h1><?php
	}
	else
	{
		?><h1><?=htmlspecialchars($admin['sections'][$section]['title'])?></h1><?php
	}
	?><table class='form' cellspacing='0' cellpadding='2' width='100%' border='0'><?php
	foreach($form as $field => $field_desc)
	{
		if ($field == '__primary__')
			continue;
		?><tr class='caption'><td colspan='3' align='left'><?=htmlspecialchars($field_desc['title'])?></td></tr><?php
		?><tr class='edit'><td width='50'>&nbsp;</td><td><?=$html[$field]?>
		   <?php if ($form[$field]['type'] == 'hidden') echo htmlspecialchars($values[$field]); ?>
		</td><td class='error'><?=$errors[$field]?></td><?php
	}
	if ($filter)
	{
	  ?><tr class='buttons'><td width='50'>&nbsp;</td><td colspan='2'>
	  <input type='submit' value='Фильтр' style='width: 120px'>
          <input type='button' value='Убрать фильтр' style='width: 120px' onClick='document.location = "?section=<?=$section?>&delfilter=1"'> 
	  </td></tr><?php
	}
	else
	{
	  ?><tr class='buttons'><td width='50'>&nbsp;</td><td colspan='2'><input type='submit' value='<?=$edit?'Сохранить':'Добавить'?>'>
          <input type='button' value='Отмена' onclick='window.close()'> 
	  </td></tr><?php
	}
	?></table></form><?php
}

function admin_process_post($edit, $view, $values)
{
	global $con;
	$queries = admin_process_build_sql($edit, $view, $values);
	$result = $edit ? $con->update($queries[0]) : $con->insert($queries[0]);
	if ($edit)
		$primary = $values["__primary__"];
	else
		$primary = $result;
	if ($result === false)
		std_error("Ошибка при исполнении запроса $queries[0] ".$con->error());
	for ($i = 1; $i < count($queries); $i++)
	{
		$queries[$i] = str_replace('%id%', $primary, $queries[$i]);
		if ($con->update($queries[$i]) === false)
			std_error("Ошибка при исполнении запроса $queries[$i] ".$con->error());
	}
	?>
		<script language='JavaScript'>
			window.opener.location.reload();
			window.close();
		</script>
	<?php
}

function admin_prepare_remote($view)
{
	global $con, $primary, $admin, $field_nochange;
	$primary = $con->safe_str($primary);           

	$remote = $con->qquery(admin_build_sql($view));

	$remote["__primary__"] = $remote[$view['primary']];

	foreach($view['fields'] as $name => $field_name) 
	{
		$field_desc = $admin['fields'][$field_name];
		if ($field_desc['type'] == 'many2many')
			$remote[$name.'[]'] = $con->query_list(admin_lookup_keys($field_desc, $view, $primary));
		if ($field_desc['type'] == 'one2many_')
		{
			$field = $admin['fields'][$field_name];
			$remote[$name.'_o2m'] = $con->property("SELECT {$field['lookupresult']} FROM {$field['lookuptable']} ".
		              "WHERE {$field['lookupkey']} = '{$remote[$name]}'");
		}
		if ($field_desc['func'] && $field_desc['oneway'])
			$remote[$name] = $field_nochange;
		if ($field_desc['rev_func'])
			$remote[$name] = $field_desc['rev_func']($remote[$name]);
	}
	return $remote;
}

function admin_delete($section)
{
	global $primary, $admin, $con;
	$primary = nav_get('primary', 'array');
	$primary = array_map($con->safe_str, $primary);
	$view = $admin['sections'][$section]['view'];
	$queries = admin_build_delete($admin['views'][$view]);
	foreach ($queries as $query)
	{
	        $result =  $con->delete($query);
		if (!$result)
			std_error("Ошибка при исполнении запроса $query");
	}
	?>
		<script language='JavaScript'>
			window.opener.location.reload();
			window.close();
		</script>
	<?php
	exit();
}

///////////////////////////////////////////////////
// Функции перехода
///////////////////////////////////////////////////

function admin_goto($section)
{
	global $admin, $con, $_SESSION;
        $primary = nav_get('primary', 'string');
	$view = $admin['views'][$admin['sections'][$section]['view']];
	$link = nav_get('link', 'string', false);
	$goto = $admin['goto'][$view['goto'][$link]];
	$fields = array($goto['source'] => $goto['target']);
	for ($i = 1; isset($goto["source$i"]); $i++)
		$fields[$goto["source$i"]] = $goto["target$i"];

	$values = $con->qquery("SELECT ".join(', ',array_keys($fields))." FROM {$view['table']} ".
	            "WHERE {$view['primary']} = '$primary'");
        $_SESSION['filter'][$goto['section']] = array();
        foreach ($fields as $source => $target)
        	$_SESSION['filter'][$goto['section']][$target] = $values[$source];
	?>
	<script language='JavaScript'>
		window.opener.location = '?section=<?=$goto['section']?>';
		window.close();
	</script>
	<?php
	exit();
}



///////////////////////////////////////////////////
// Функции фильтрации вывода
///////////////////////////////////////////////////

function admin_filter_process($section)
{
	global $admin, $con, $_SESSION;
	if ($_GET['delfilter'])
	{
		$_SESSION['filter'][$section] = array();	
		std_redirect(nav_link(array('delfilter' => false)));
	}
	$view = $admin['sections'][$section]['view'];
	if (!$view['filters'])
		return;
	$remote = $_SESSION['filter'][$section];
	$form = admin_prepare_filter_form($admin['views'][$view]);
        if (form_create($form, false, "filter", "", $remote, $html, $values, $errors, $form_tag))
        {
        	// форма уже запощена
        	foreach ($values as $name => $value)
        		if ($value === '' || $value === false || $value === null)
        			unset($values[$name]);
        	$_SESSION['filter'][$section] = $values;
		std_redirect(nav_link(array('x' => rand())));
        }
}

function admin_filter_show($section)
{
	global $admin, $con, $_SESSION;
	$view = $admin['sections'][$section]['view'];
	$remote = $_SESSION['filter'][$section];
	$form = admin_prepare_filter_form($admin['views'][$view]);
        form_create($form, false, "filter", "", $remote, $html, $values, $errors, $form_tag);
       	admin_show_form(true, $section, $form, $admin['views'][$view], $html, $values, $errors, $form_tag, true);
}

function admin_delfilter($section)
{
	global $admin, $con, $_SESSION;
        $_SESSION['filter'][$section] = array();
	?>
	<script language='JavaScript'>
		window.opener.location.reload();
		window.close();
	</script>
	<?php
	exit();
}

function admin_prepare_filter_form($view)
{
	global $admin, $con;
	$form = array();
	foreach($view['filters'] as $field => $field_name)
	{
		$field_desc = $admin['fields'][$field_name];
		switch($field_desc['type'])
		{
			case 'text':  $form_field = array('type' => 'char'); break;
			case 'auto':
			case 'int' :  $form_field = array('type' => 'int' ); break;
			case 'date':  $form_field = array('type' => 'date'); break;
			case 'memo':  $form_field = array('type' => 'text'); break;
			case 'enum':  $form_field = array('type' => 'select', 'elements' => $field_desc['values']); break;
			case 'one2many':
                                      $form_field = array('type' => 'select', 'elements' => $con->query_list(admin_lookup_values($field_desc))); break;
			case 'one2many_':
				      $form_field = array('type' => 'int'); break;
			case 'many2many':
                                      $form_field = array('type' => 'select', 'elements' => $con->query_list(admin_lookup_values($field_desc))); break;
			default: std_error("Неизвестный тип поля ".$field_desc['type']);
		}
		$form_field['title'] = $field_desc['title'];
		if ($field_desc['size'])
			$form_field['size'] = $field_desc['size'];
		if ($field_desc['min'])
			$form_field['min'] = $field_desc['min'];
		if ($field_desc['max'])
			$form_field['max'] = $field_desc['max'];
		$form[$field] = $form_field;
		if ($field_desc['type'] == 'one2many_')
		{
			$form[$field]['title'] .= ' [ID]';
			$form[$field.'_o2m'] = 
				array(
					'type' => 'char',
					'size' => '60',
					'title' => $field_desc['title'].' [Значение]',
				     );
					
		}
	}
	return $form;
}

///////////////////////////////////////////////////
// Функции форматирования
///////////////////////////////////////////////////

function format_cut($line, $field)
{
  if ($line)
  {
  	$words = preg_split('/[\s]+/', $line);
  	$high = 0;
  	$lengths = array_map('strlen', $words);
  	while ($high < count($words) && array_sum(array_slice($lengths, 0, $high+1))+$high < $field['size'])
  		$high++;
  	if ($high > 0)
	  	$result = join(' ', array_slice($words,0,$high));
	else
		$result = substr($line, 0, $field['size']-3);
	if ($high < count($words))
  		$result .= '...';
  	return $result;
  }
  else return '';
}

function format_onlydate($line, $field)
{
	return preg_replace('/([0-9-]+).*/','$1', $line);
}

function format_enum($line, $field)
{
	return $field['values'][$line];
}

function format_lookup($line, $field)
{
	global $con;
	if (!$line)
		return $line;
	$line = $con->safe_str($line);
	return $con->property("SELECT {$field['lookupresult']} FROM {$field['lookuptable']} ".
		              "WHERE {$field['lookupkey']} = '$line'");
}

function br2nl($text)
{
	return str_replace("<br>", "\n",
	        str_replace("<br>\n","\n",
	         str_replace("<br />\n", "\n",
	          str_replace("<br />\r", "", $text))));
}

function admin_special_disable($section)
{
	admin_special_hideunhide($section, '', '_save');
}

function admin_special_enable($section)
{
	admin_special_hideunhide($section, '_save', '');
}


