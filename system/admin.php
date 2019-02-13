<?

session_start();

function admin_html()
{
     global $admin, $site_title, $HTTP_SESSION_VARS, $section_list, $section, $default_section;
     
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
<?
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
<?
}

/////////////////////////////////////////////////
/// Авторизация
/////////////////////////////////////////////////

function admin_authorized()
{
   global $HTTP_SESSION_VARS;
   return $HTTP_SESSION_VARS['uid'] > 0;
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
<?
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
   $priv_ = split(' ', $result['priv']);
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
            <?
               foreach($admin['sections'] as $name => $info)
               {
                   if (!in_array($name, $section_list))
                   	continue;
                   ?><option value='<?=$name?>' <?=$name==$section?'selected':''?> ><?=$info['title']?></option><?
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
<?
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
     <? admin_prepare_field_map($admin['views'][$view]); ?> 
<!-- хедер текущего view (заголовок таблицы) -->
     <? admin_view_header($admin['views'][$view]); ?>
<!-- конец хедера -->
<!-- содержимое таблицы -->
     <? admin_show_sql($admin['views'][$view], admin_build_sql($admin['views'][$view])) ?>
<!-- конец содержимого таблицы -->
<!-- футер текущего view (статистика, прокрутка?) -->
     <? $static ? 1 : admin_view_footer($admin['views'][$view]); ?>
<!-- конец футера -->
<!-- строка с кнопками действий -->
     <? $static ? 1 : admin_buttons($admin['views'][$view]); ?>
<!-- конец строки с кнопками действий -->
   </table>
   </form>
<!-- фильтр -->
<tr><td>
   <? if (count($admin['views'][$view]['filters']) > 0) admin_filter_show($section); ?>
</td></tr>
<!-- конец фильтра -->
</td></tr>
<!-- конец view -->
<?
	}
?>
</table>
<?
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

?><tr><?
        ?><th width='20'><a href='<?=nav_link(array('sort' => false, 'asc' => false ))?>'>б/с</a></th><?
	foreach ($field_map as $name => $field)
		admin_view_header_field($name, $view, $admin['fields'][$field]);
?></tr><?
}

function admin_view_header_field($name, $view, $field)
{
  global $sort, $asc;
  ?><th width='<?=$view['widths'][$name]?>'>
      <a href='<?=nav_link(array('sort' => $name, 'asc' => (integer)($name != $sort ? 1 : !$asc) ))?>'>
         <?=htmlspecialchars($field['title'])?></a><span class='order'><?=($name == $sort ? ($asc ? 'H':'G') : '' )?></span>
  </th><?
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
	           ><?
	    ?><td bgcolor="<?=$row_inactive?>"><input type='checkbox' name='row<?=++$num_rows?>' value="<?=htmlspecialchars($row[$view['primary']])?>"></td><?
            admin_lookup_fields($row, $view);
	    $rowstyle = $view['rowstyler']  ? $view['rowstyler']($row) : '';
	    foreach ($field_map as $name => $field)
	    {
		admin_print_field($row[$name],$admin['fields'][$field], $rowstyle);
	    }
	    ?></tr><?
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
	  <?
		if ($field['alias_buttons'])
			admin_print_alias_buttons($row);
	  ?>
        </td>
        <?
}

function admin_print_alias_buttons(&$row)
{
	?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?
	echo admin_button('Алиас <->', nav_link(array('action' => 'alias', 'kind' => 'swap', 'from' => false, 'limit' => false,
					   'id' => $row['id'])), true);
	?>&nbsp;&nbsp;&nbsp;<?
	echo admin_button('Алиас +-The', nav_link(array('action' => 'alias', 'kind' => 'the', 'from' => false, 'limit' => false,
					   'id' => $row['id'])), true);
}

function admin_view_footer($view)
{
	global $admin, $from, $num_rows, $overall_rows, $field_map, $section, $HTTP_SESSION_VARS;
	$filter = $HTTP_SESSION_VARS['filter'][$section];
	?><tr><th>&nbsp;</th><?
	?><th align='left' colspan='<?=count($field_map)?>'>Показаны записи с <?=$from+1?> по <?=$from+$num_rows?> (из <?=$overall_rows?>)
	  <? if (count($filter) > 0): ?> (+фильтр+) <? endif; ?>
	</th><?
	?></tr><?
}

function admin_buttons($view)
{
	global $overall_rows, $from, $limit, $section, $field_map, $HTTP_SESSION_VARS;
	$last_page = max(0,floor(($overall_rows-1) / $limit) * $limit);
	?><tr><td class='buttons' colspan='<?=count($field_map)+1?>'><table border='0' cellspacing='0' cellpadding='0' width='100%' class='buttons'><tr><?
	?><td class='buttons'  style='text-align: left'><?
	echo admin_button("<<", nav_link(array('from' => 0)));
	echo "&nbsp;";
	if ($from > 0)
		echo admin_button("<", nav_link(array('from' => max(0,$from-$limit))));
        ?></td><td class='buttons'><?
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
	?></td><td class='buttons' style='text-align: right'><?
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
    	<?
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
	?></td><?
	?></tr></table></td></tr><?
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
	global $admin, $HTTP_SESSION_VARS, $section;

	$filter = $HTTP_SESSION_VARS['filter'][$section];
	if ($filter && count($filter) > 0)
	{
	       return ' WHERE '.join(' AND ',admin_condition(array_map('mysql_escape_string', $filter), $view));
	}
	else
		return '';
}

function admin_build_sql($view)
{
	global $admin, $from, $limit, $sort, $asc, $primary, $HTTP_SESSION_VARS, $section;
	
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
	global $admin, $from, $limit, $sort, $asc, $primary, $HTTP_SESSION_VARS, $section;
	$filter = $HTTP_SESSION_VARS['filter'][$section];
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
        if (form_create(&$form, false, "admin", "", &$remote, &$html, &$values, &$errors, &$form_tag))
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
		?><h1>Фильтр</h1><?
	}
	else
	{
		?><h1><?=htmlspecialchars($admin['sections'][$section]['title'])?></h1><?
	}
	?><table class='form' cellspacing='0' cellpadding='2' width='100%' border='0'><?
	foreach($form as $field => $field_desc)
	{
		if ($field == '__primary__')
			continue;
		?><tr class='caption'><td colspan='3' align='left'><?=htmlspecialchars($field_desc['title'])?></td></tr><?
		?><tr class='edit'><td width='50'>&nbsp;</td><td><?=$html[$field]?>
		   <?if ($form[$field]['type'] == 'hidden') echo htmlspecialchars($values[$field]); ?>
		</td><td class='error'><?=$errors[$field]?></td><?
	}
	if ($filter)
	{
	  ?><tr class='buttons'><td width='50'>&nbsp;</td><td colspan='2'>
	  <input type='submit' value='Фильтр' style='width: 120px'>
          <input type='button' value='Убрать фильтр' style='width: 120px' onClick='document.location = "?section=<?=$section?>&delfilter=1"'> 
	  </td></tr><?
	}
	else
	{
	  ?><tr class='buttons'><td width='50'>&nbsp;</td><td colspan='2'><input type='submit' value='<?=$edit?'Сохранить':'Добавить'?>'>
          <input type='button' value='Отмена' onclick='window.close()'> 
	  </td></tr><?
	}
	?></table></form><?
}

function admin_process_post($edit, $view, $values)
{
	global $con;
	$queries = admin_process_build_sql($edit, $view, $values);
	$result = $edit ? $con->update($queries[0]) : $con->insert($queries[0]);
	if ($edit)
		$primary = $values["__primary__"];
	else
		$primary = @mysql_insert_id();
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
	<?
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
	$primary = array_map("mysql_escape_string", $primary);
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
	<?
	exit();
}

///////////////////////////////////////////////////
// Функции перехода
///////////////////////////////////////////////////

function admin_goto($section)
{
	global $admin, $con, $HTTP_SESSION_VARS;
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
	<?
	exit();
}



///////////////////////////////////////////////////
// Функции фильтрации вывода
///////////////////////////////////////////////////

function admin_filter_process($section)
{
	global $admin, $con, $HTTP_SESSION_VARS;
	if ($_GET['delfilter'])
	{
		$HTTP_SESSION_VARS['filter'][$section] = array();	
		std_redirect(nav_link(array('delfilter' => false)));
	}
	$view = $admin['sections'][$section]['view'];
	if (!$view['filters'])
		return;
	$remote = $HTTP_SESSION_VARS['filter'][$section];
	$form = admin_prepare_filter_form($admin['views'][$view]);
        if (form_create(&$form, false, "filter", "", &$remote, &$html, &$values, &$errors, &$form_tag))
        {
        	// форма уже запощена
        	foreach ($values as $name => $value)
        		if ($value === '' || $value === false || $value === null)
        			unset($values[$name]);
        	$HTTP_SESSION_VARS['filter'][$section] = $values;
		std_redirect(nav_link(array('x' => rand())));
        }
}

function admin_filter_show($section)
{
	global $admin, $con, $HTTP_SESSION_VARS;
	$view = $admin['sections'][$section]['view'];
	$remote = $HTTP_SESSION_VARS['filter'][$section];
	$form = admin_prepare_filter_form($admin['views'][$view]);
        form_create(&$form, false, "filter", "", &$remote, &$html, &$values, &$errors, &$form_tag);
       	admin_show_form(true, $section, $form, $admin['views'][$view], $html, $values, $errors, $form_tag, true);
}

function admin_delfilter($section)
{
	global $admin, $con, $HTTP_SESSION_VARS;
        $HTTP_SESSION_VARS['filter'][$section] = array();
	?>
	<script language='JavaScript'>
		window.opener.location.reload();
		window.close();
	</script>
	<?
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
// Алиасы
///////////////////////////////////////////////////

function admin_make_alias($section)
{
	global $con;

	$id = nav_get('id', 'integer', 0);
	$kind = nav_get('kind', 'string', '');
	if (!$id || !$kind)
		return;
	
	$info = $con->qquery("SELECT * FROM t_artist_list WHERE id = $id");
	switch ($kind)
	{
		case 'swap' :
			$alias = join(' ', array_reverse(split(' ', $info['ARTIST_NAME'])));
			break;
		case 'the' :
			if (preg_match('/^The /', $info['ARTIST_NAME']))
				$alias = preg_replace('/^The /', '', $info['ARTIST_NAME']);
			else
				$alias = 'The '.$info['ARTIST_NAME'];
			break;
		default:
			return;
	}

	if ($con->property("SELECT id FROM t_artist_list WHERE artist_name = '".$con->safe_str($alias)."'"))
	{
		?>Такой исполнитель уже есть!<?
		die;
	}

	if ($con->property("SELECT id FROM t_alias_list WHERE artist_name = '".$con->safe_str($alias)."'"))
	{
		?>Такой алиас уже есть!<?
		die;
	}
	
	$con->insert("INSERT INTO t_alias_list SET ".
		     "artist_id = $id, ".
		     "artist_name = '".$con->safe_str($alias)."', ".
		     "rus = {$info['RUS']}, ".
		     "genre_id = {$info['GENRE_ID']}, ".
		     "artist_rating = {$info['ARTIST_RATING']}, ".
		     "artist_titles = {$info['ARTIST_TITLES']}, ".
		     "artist_albums = {$info['ARTIST_ALBUMS']}");


	?><script>window.close();</script><?
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

function rowstyler_adv_banner(&$row)
{
	$today = date('Y-m-d H:m:s');
	return ($row['validfromdate'] <= $today && $today <= $row['validtodate']) ? "style='font-weight: bolder'" : '';
}

function rowstyler_t_users(&$row)
{
	global $con;

	if ($con->property("SELECT balance FROM acc_balance WHERE user_id = {$row['id']}") > 0)
		$style = 'VIP';
	else if ($row['mp3key_encoded'])
		$style = 'Priv';
	else
		$style = 'Basic';
	$colors = array(
		'VIP' => '#ff0000',
		'Priv' => '#007f00',
		'Basic' => '#000000',
		       );
	if ($style)
		return "style='color: {$colors[$style]}'";
	else
		return '';
}

function admin_special_disable($section)
{
	admin_special_hideunhide($section, '', '_save');
}

function admin_special_enable($section)
{
	admin_special_hideunhide($section, '_save', '');
}

function admin_special_hideunhide($section, $postfix1, $postfix2)
{
	global $con;

	$primary = nav_get('primary', 'array');
	foreach ($primary as $album_id)
	{
	 	$title_ids = $con->query_list("SELECT id FROM t_title_list$postfix1 WHERE album_id = $album_id");
	
		$con->insert("INSERT INTO t_album_list$postfix2 SELECT * FROM t_album_list$postfix1 WHERE id = $album_id");
		$con->insert("INSERT INTO t_title_list$postfix2 SELECT * FROM t_title_list$postfix1 WHERE album_id = $album_id");
		$con->insert("INSERT INTO t_filepath_list$postfix2 SELECT * FROM t_filepath_list$postfix1 WHERE file_id IN (".join(', ', $title_ids).")");
		$con->insert("INSERT INTO t_text_list$postfix2 SELECT * FROM t_text_list$postfix1 WHERE title_id IN (".join(', ', $title_ids).")");

		$con->delete("DELETE FROM t_top WHERE title_id IN (".join(', ', $title_ids).")");
		$con->delete("DELETE FROM t_album_list$postfix1 WHERE id = $album_id");
		$con->delete("DELETE FROM t_title_list$postfix1 WHERE album_id = $album_id");
		$con->delete("DELETE FROM t_filepath_list$postfix1 WHERE file_id IN (".join(', ', $title_ids).")");
		$con->delete("DELETE FROM t_text_list$postfix1 WHERE title_id IN (".join(', ', $title_ids).")");
	}
?>
<script>
	window.opener.document.location.reload();
	window.close();
</script>
<?
}

function admin_special_recording($section)
{
	global $con;

	$primary = nav_get('primary', 'array');
	$recording = nav_post('recording', 'integer', 0);

	if ($recording)
	{
		$expr = ' = '.($recording == -1 ? 'NULL' : $recording);
		foreach ($primary as $title_id)
			$con->update("UPDATE t_title_list SET company $expr WHERE id = $title_id");
		?>
		<script>
			window.opener.document.location.reload();
			window.close();
		</script>
		<?
		return;
	}

	$list = $con->query_list("SELECT id, name FROM companies");
	?>
	<form method='post'>
	<table class='form' cellspacing='0' cellpadding='2' width='100%' border='0'>
	<tr class='caption'><td colspan='3' align='left'>Компания</td></tr>
	<tr class='edit'><td width='50'>&nbsp;</td><td>
		<select name='recording'>
			<option value='-1' selected>РОМС</option>
			<?
			foreach ($list as $id => $name)
			{
				?><option value='<?=$id?>'><?=$name?></option><?
			}
			?>
		</select>
		</td><td class='error'></td></tr>
        <tr class='buttons'><td width='50'>&nbsp;</td><td colspan='2'>
	  <input type='submit' value='Пометить' style='width: 120px'>
	  </td></tr>
	</table>
	</form>
	<?

}

?>
