<?php

$form_signature = "rgs2000";
$form_monthes = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
$form_errors = array(
	"min" => "Значение поля не может быть меньше %s",
	"max" => "Значение поля не может быть больше %s",
	"notnull" => "Поле требует обязательного заполнения",
	"strmin" => "Значение поля не может быть короче %s символов",
	"strmax" => "Значение поля не может быть длиннее %s символов",
	"int" => "Неверный формат числа",
	"regexp" => "Поле принимает недопустимое значение");
$form_year_min = 1901;
$form_year_max = 2100;//date("Y");
$form_null_key = "__null_";

/**
 * form_create()
 * Функция обрабатывает описание формы и значения из трех источников - $_POST,
 * $remote_data, $fields[$i]["default"]. На выходе дает html-тэги для всех полей ($html),
 * значения полей ($values), сообщения об ошибках ($errors) и тэг <form ...> ($form_tag).
 * @param $fields ассоциативный массив описаний полей формы;
 * каждое описание представляет собой также ассоциативный массив со след. ключами:
 * 1. type - тип поля; один из следующих вариантов:
 *   a) hidden   скрытое поле, содержащее любое текстовое значение
 *   b) bit      checkbox, 0 или 1
 *   c) int      editbox, целое знаковое
 *   d) char     editbox, любое текстовое значение
 *   e) enchar   password, звездочки да и только
 *   f) text     textarea, любое текстовое значение (memo)
 *   g) radio    массив radiobutton'ов
 *   h) select   selectbox, выпадающий список
 *   i) date     три выпадающих списка с днем, месяцем и годом
 *   j) checks   массив checkbox'ов, в ассоциативном массиве (требует обработки перед и после form_create)
 *   k) file     файл - TODO
 *   l) image    файл с картинкой - TODO
 * 2. default - значение по умолчанию
 * 3. notnull - означает обязательность поля; применимо только для int, select, date,
 *    radio и check
 * 4. min, max - минимальное (максимальное) значение для int, длина текста
 *    (char, enchar, text), дата (возможно описание рамок только для года (2001), для года и
 *    месяца (2001-10) или для всей даты (2001-10-12)
 * 5. regexp, regexp-error - регулярное выражение для проверки значения и текст
 *    сообщения об ошибке
 * 6. function - callback-функция формата function check($value) для проверки поля
 * 7. class, size - дополнительные параметры для html-тэга поля
 * 8. checks_zeroes - если type=checks, результирующий массив по умолчанию хранит только
 *    установленные флаги; если же установлен флаг check_zeroes, нули тоже записываются
 * @param $url url обработчика формы
 * @param $fname имя формы
 * @param $extra дополнительные параметры для тэга <form>
 * @param $remote_data внешние данные, первичное заполнение формы (например, результат
 * запроса к базе данных)
 * @param $html html-тэги для полей формы
 * @param $values значения полей
 * @param $errors сообщения об ошибках
 * @param $form_tag <form .....>
 * @return true, если форма отправлена и не содержит ни одной ошибки; иначе - false
 */
function form_create(&$fields, $url, $fname, $extra, &$remote_data, &$html, &$values, &$errors, &$form_tag)
{
	global $form_signature;
	
	$errors = false;

	$is_sent = form_is_sent($fname);

	$form_tag = "<form name=$fname".($url?" action=$url":"")." method='post' onsubmit='return {$fname}_submit(this)'".($extra?" ".$extra:"").">\n";
	$form_tag .= "<input type=hidden name='$fname-$form_signature' value=1>\n";
	$form_tag .= "<script>\n";
	$form_tag .=
		"function {$fname}_submit(form) {\n".
		"	var r = {$fname}_check(form);\n".
		"	if (r != null) {\n".
		"		r[0].focus();\n".
		"		window.scrollBy(0, -100);\n".
		"		try{r[0].select();}catch(e){}\n".
		"		alert('Ошибка в поле \"'+r[1]+'\":\\n'+r[2]);\n".
		"		return false;\n".
		"	}\n".
		"	return true;\n".
		"}\n";
	$form_tag .= "function {$fname}_check(form) {\n";

	foreach ($fields as $name=>$val)
		if (is_array($val))
		{
			$value = $values[$name] = form_generate_value($val, $name, $remote_data, $is_sent, $errors);
			$html[$name] = form_generate_field($val, $name, $value, $errors);
			if ($is_sent && $error = form_check_rules($val, $value))
				$errors[$name] = $error;
			$form_tag .= form_generate_rule_check($val, $name);
		}

	$form_tag .= "}\n";
	$form_tag .= "</script>\n";

	return $is_sent && !$errors;
}

/**
 * form_is_sent()
 * Проверяет, содержит ли $_POST данные о форме form_name.
 * @param $form_name название формы
 * @return true, если содержит, иначе false
 */
function form_is_sent($form_name)
{
	global $_POST, $form_signature;
	if (isset($_POST[$form_name."-".$form_signature])) return true;
	return false;
}

/**
 * form_generate_field()
 * Генерит html-код для элемента формы (input, select и т.д.) в виде строки, либо
 * массив html-кодов для radio-баттонов.
 * @param $field описание поля
 * @param $name  название поля
 * @param $value значения поля
 * @return html-код, либо массив html-кодов
 */
function form_generate_field(&$field, $name, $value)
{
	global $form_monthes, $form_year_min, $form_year_max, $form_null_key;

	$name = " name=".$name.($field['type'] != 'date' ? " id=".$name : '');
	$class = $field["class"]?" class=".$field["class"]:"";
	$size = $field["size"]?" size=".$field["size"]:"";
	$readonly = $field["readonly"]?" readonly":"";
	$disabled = $field["disabled"]?" disabled":"";

	switch ($field["type"])
	{
	case "bit":
		return "<input type=checkbox".$name.($value?" checked":"").$class.$disabled.">";
	case "hidden":
	case "char":
	case "int":
	case "enchar":
		$types = array("hidden"=>"hidden", "char"=>"text", "int"=>"text", "enchar"=>"password");
		$maxlen = $field["max"]?" maxlength=".($field["type"]=="int"?strlen($field["max"]):$field["max"]):"";
		$value = ($value !== false && $value !== null)?" value=\"".($field["type"]=="enchar"?"":htmlspecialchars($value))."\"":"";
		return "<input type=".$types[$field["type"]].$name.$size.$maxlen.$readonly.$disabled.$class.$value.">";
	case "radio":
		$output = array();
		foreach ($field["elements"] as $key=>$val)
			$output[$key] = "<input type=radio value=\"$key\"".$name.($value==$key?" checked":"").$class.">";
		return $output;
	case "select":
		$output = "<select".$name.$size.($field['multiple']?' multiple ':'').$class.">";
		if (!(isset($field["default"]) && $field["notnull"]))
			$output .= "<option value=".$form_null_key.">".($field["notnull"]?"- Выберите -":"- Не указано -")."\n";
		foreach ($field["elements"] as $key=>$val)
			$output .= "<option value=$key".($value !== false && $value !== null && ($value==$key || (is_array($value) && in_array($key, $value))) ? " selected":"").">$val\n";
		$output .= "</select>";
		return $output;
	case "text":
		$output = "<textarea ".$name.($field["size"]?" cols=".$field["size"]:"").($field["lines"]?" rows=".$field["lines"]:"").$class.">\n".htmlspecialchars($value)."</textarea>";
		return $output;
	case "date":
		list($year, $month, $day) = explode("-", $value);

		$output = "<select $name-d".$class.">";
		$output .= "<option value=".$form_null_key.">день\n";
		for ($i = 1; $i <= 31; $i++)
		{
			$i2 = $i<10?"0".$i:$i;
			$output .= "<option value=".$i.($day==$i?" selected":"").">".$i2;
		}
		$output .= "</select>";
		
		$output .= "<select $name-m".$class.">";
		$output .= "<option value=".$form_null_key.">месяц\n";
		for ($i = 1; $i <= 12; $i++)
			$output .= "<option value=".$i.($month==$i?" selected":"").">".$form_monthes[$i-1];
		$output .= "</select>";
		
		$output .= "<select $name-y".$class.">";

		$output .= "<option value=".$form_null_key.">год\n";
		list($min_y) = split("-", $field["min"]);
		if (!$min_y) $min_y = $form_year_min;
		list($max_y) = split("-", $field["max"]);
		if (!$max_y) $max_y = $form_year_max;
		for ($i = $min_y; $i <= $max_y; $i++)
			$output .= "<option value=".$i.($year==$i?" selected":"").">".$i;
		$output .= "</select>";

		return $output;
	case "checks":
		$output = array();
		foreach ($field["elements"] as $key=>$val)
			$output[$key] = "<input type=checkbox".$name."-".$key.($value[$key]?" checked":"").$class.">";
		return $output;
	default:
		die("Type not supported by egnore forms module: ".$field["type"]);
		break;
	}
}

/**
 * form_check_rules()
 * Проверяет значение $value на соответствие правилам, определенным для поля $field
 * @param $field ассоциативный массив - описание поля
 * @param $value проверяемое значение
 * @return строка с сообщением об ошибке, либо false, если все в порядке
 */
function form_check_rules(&$field, $value)
{
	global $form_errors, $form_null_key;
	// Checking "notnull", "min" and "max" - differ for different types
	if ($field['disabled'])
		return false;

	if ($value === false)
	{
		if ($field["notnull"])
			return $form_errors["notnull"];
		return false;
	}

	switch ($field["type"])
	{
	case "date":
		list($y, $m, $d) = explode("-", $value);
		if (isset($field["min"]))
		{
			list($min_y, $min_m, $min_d) = explode("-", $field["min"]);
			if (isset($min_y) && $y < $min_y || ($min_y == $y &&
				isset($min_m) && $m < $min_m || ($min_m == $m &&
				isset($min_d) && $d < $min_d)))
				return sprintf($form_errors["min"], $field["min"]);
		}
		if (isset($field["max"]))
		{
			list($max_y, $max_m, $max_d) = explode("-", $field["max"]);
			if (isset($max_y) && $y > $max_y || ($max_y == $y &&
				isset($max_m) && $m > $max_m || ($max_m == $m &&
				isset($max_d) && $d > $max_d)))
				return sprintf($form_errors["max"], $field["min"]);
		}
		break;
	case "int":
		if (strcmp($value, (int)$value) != 0)
			return $form_errors["int"];
		if (isset($field["min"]) && $value < $field["min"])
			return sprintf($form_errors["min"], $field["min"]);
		if (isset($field["max"]) && $value > $field["max"])
			return sprintf($form_errors["max"], $field["max"]);
		break;
	case "char":
	case "enchar":
	case "text":
		$len = strlen($value);
		if (isset($field["min"]) && $len < $field["min"])
			return sprintf($form_errors["strmin"], $field["min"]);
		if (isset($field["max"]) && $len > $field["max"])
			return sprintf($form_errors["strmax"], $field["max"]);
		break;
	}

	// Regexp
	if (isset($field["regexp"]) && !preg_match($field["regexp"], $value))
	{
		if (isset($field["regexp-error"]))
			return $field["regexp-error"];
		return $form_errors["regexp"];
	}
	// Callback function
	if (isset($field["function"]))
		return $field["function"]($value);
	return false;
}

/**
 * form_generate_date_compare()
 * Генерит условие для if-выражение на языке JScript, сравнивающее дату в
 * трех select-box'ах с некой фиксированной датой, указанной в параметре $date.
 * Допускается сокращенная проверка только года или года и месяца ("2001", "2001-01").
 * Пример результата:
 * "(form.date_y.value < 2000 || form.date_y.value == 2000 && (form.date_m.value < 03))"
 * @param $name имя поля формы
 * @param $compare знак сравнения; может быть "<" либо ">"
 * @param $date дата, с которой нужно сравнивать
 * @return JScript-код
 */
function form_generate_date_compare($name, $compare, $date)
{
	list($y, $m, $d) = explode("-", $date);
	$r = false;
	if (isset($y))
	{
		$r = "form['$name-y'].value".$compare.$y;
		if (isset($m))
		{
			$r .= " || form['$name-y'].value == $y && (form['$name-m'].value".$compare.$m;
			if (isset($d))
				$r .= " || form['$name-m'].value == $m && form['$name-d'].value".$compare.$d;
			$r .= ")";
		}
	}
	return $r;
}

/**
 * form_generate_jscript_error()
 * Функция возвращает JScript-код для проверки $rule.
 * @param $rule правило для проверки поля
 * @param $error текст сообщения об ошибке
 * @param $error_param параметр, используемый в sprintf (если $error содержит %s и т.п.)
 * @return строка в кавычках
 */
function form_generate_jscript_check($name, $title, $rule, $error, $error_param = false)
{
	if ($error_param == false)
		return "if ($rule) return new Array(form['$name'], \"$title\", \"".addslashes($error)."\");\n";
	return "if ($rule) return new Array(form['$name'], \"$title\", \"".addslashes(sprintf($error, $error_param))."\");\n";
}

/**
 * form_generate_rule_check()
 * Генерит JScript-код для проверки значения поля $name на
 * соответствие правилам, определенным для поля $field. В случае ошибки 
 * @param $field описание поля
 * @param $name имя поля
 * @return строка, содержащая JScript-код, либо false, если проверок не требуется
 */
function form_generate_rule_check($field, $name)
{
	global $form_errors, $form_null_key;
	// Проверяем "notnull", "min" and "max" - по-разному для разных типов
	$r = "";

	if (isset($field["regexp"]))
		$regexp = form_generate_jscript_check($name, $field["title"], "form['$name'].value.match(".$field["regexp"].")==null", isset($field["regexp-error"])?$field["regexp-error"]:$form_errors["regexp"]);
	else
		$regexp = "";

	switch ($field["type"])
	{
	case "date":
		if ($field["notnull"])
			$r .= form_generate_jscript_check($name."-d", $field["title"], "form['$name-y'].value=='$form_null_key' || form['$name-m'].value=='$form_null_key' || form['$name-d'].value=='$form_null_key'", $form_errors["notnull"]);
		$canBnull = !$field["notnull"] && (isset($field["min"]) || isset($field["max"]));
		if ($canBnull)
			$r .= "if (form['$name-y'].value!='$form_null_key' && form['$name-m'].value!='$form_null_key' && form['$name-d'].value=='$form_null_key') {\n";
		if (isset($field["min"]))
			$r .= form_generate_jscript_check($name."-d", $field["title"], form_generate_date_compare($name, "<", $field["min"]), $form_errors["min"], $field["min"]);
		if (isset($field["max"]))
			$r .= form_generate_jscript_check($name."-d", $field["title"], form_generate_date_compare($name, ">", $field["max"]), $form_errors["max"], $field["max"]);
		$r .= $regexp;
		if ($canBnull)
			$r .= "}\n";
		break;
	case "int":
		if ($field["notnull"])
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value==''", $form_errors["notnull"]);
		$canBnull = !$field["notnull"];
		if ($canBnull)
			$r .= "if (form['$name'].value!='') {\n";
		$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value!=parseInt(form['$name'].value)", $form_errors["int"]);
		if (isset($field["min"]))
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value<".$field["min"], $form_errors["min"], $field["min"]);
		if (isset($field["max"]))
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value>".$field["max"], $form_errors["max"], $field["max"]);
		$r .= $regexp;
		if ($canBnull)
			$r .= "}\n";
		break;
	case "char":
	case "enchar":
	case "text":
		if ($field["notnull"])
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value==''", $form_errors["notnull"]);
		$canBnull = !$field["notnull"];
		if ($canBnull)
			$r .= "if (form['$name'].value!='') {\n";
		$len = strlen($value);
		if (isset($field["min"]))
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value.length<".$field["min"], $form_errors["strmin"], $field["min"]);
		if (isset($field["max"]))
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value.length>".$field["max"], $form_errors["strmax"], $field["max"]);
		$r .= $regexp;
		if ($canBnull)
			$r .= "}\n";
		break;
	case "radio":
	case "select":
		if ($field["notnull"])
			$r .= form_generate_jscript_check($name, $field["title"], "form['$name'].value=='$form_null_key'", $form_errors["notnull"]);
		break;
	default:
		$r .= $regexp;
	}

	// Callback-функции, разумеется, на клиенте выполняться не могут :(
	return $r;
}

/**
 * form_generate_value()
 * Вычисляет значение поля, проверяя по порядку следующие значения:
 * 1) значение, введенное в форму (если $isset)
 * 2) значение из внешнего источника ($remote_data)
 * 3) значение по умолчанию ($field["default"])
 * @param $field описание поля
 * @param $name название поля
 * @param $remote_data внешние данные, используемые когда форма еще не отправлена
 * ("исходные" данные)
 * @param $isset установлена ли форма? если нет, берем $remote_data либо,
 * если $remote_data==false, значения по умолчанию
 * @return значение поля (false - значение не указано, например в select'ах)
 */
function form_generate_value($field, $name, $remote_data, $isset)
{
	global $_POST, $form_signature, $form_null_key;

	if ($isset && !$field['disabled'])
		switch ($field["type"])
		{
		case "bit":
			return $_POST[$name]?1:0;
		case "date":
			$y = &$_POST[$name."-y"];
			$m = &$_POST[$name."-m"];
			$d = &$_POST[$name."-d"];
			if ($y == $form_null_key || $m == $form_null_key || $d == $form_null_key)
				return false;
			return sprintf("%04d-%02d-%02d", $y, $m, $d);
		case "select":
			if ($field['multiple'])
				$name = str_replace('[]', '', $name);
			return $_POST[$name]!=$form_null_key?$_POST[$name]:false;
		case "checks":
			$r = array();
			if ($field["checks_zeroes"])
				foreach($field["elements"] as $key=>$value)
					$r[$key] = $_POST[$name."-".$key]?true:false;
			else
				foreach($field["elements"] as $key=>$value)
					if ($_POST[$name."-".$key])
						$r[$key] = true;
			return $r;
		default:
			return $_POST[$name]!=""?$_POST[$name]:false;
		}

	if (isset($remote_data[$name]))
		return $remote_data[$name];
	else
		return $field["default"];
}

?>
