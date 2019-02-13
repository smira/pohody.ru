<?php

$form_signature = "rgs2000";
$form_monthes = array("������", "�������", "�����", "������", "���", "����", "����", "�������", "��������", "�������", "������", "�������");
$form_errors = array(
	"min" => "�������� ���� �� ����� ���� ������ %s",
	"max" => "�������� ���� �� ����� ���� ������ %s",
	"notnull" => "���� ������� ������������� ����������",
	"strmin" => "�������� ���� �� ����� ���� ������ %s ��������",
	"strmax" => "�������� ���� �� ����� ���� ������� %s ��������",
	"int" => "�������� ������ �����",
	"regexp" => "���� ��������� ������������ ��������");
$form_year_min = 1901;
$form_year_max = 2100;//date("Y");
$form_null_key = "__null_";

/**
 * form_create()
 * ������� ������������ �������� ����� � �������� �� ���� ���������� - $HTTP_POST_VARS,
 * $remote_data, $fields[$i]["default"]. �� ������ ���� html-���� ��� ���� ����� ($html),
 * �������� ����� ($values), ��������� �� ������� ($errors) � ��� <form ...> ($form_tag).
 * @param $fields ������������� ������ �������� ����� �����;
 * ������ �������� ������������ ����� ����� ������������� ������ �� ����. �������:
 * 1. type - ��� ����; ���� �� ��������� ���������:
 *   a) hidden   ������� ����, ���������� ����� ��������� ��������
 *   b) bit      checkbox, 0 ��� 1
 *   c) int      editbox, ����� ��������
 *   d) char     editbox, ����� ��������� ��������
 *   e) enchar   password, ��������� �� � ������
 *   f) text     textarea, ����� ��������� �������� (memo)
 *   g) radio    ������ radiobutton'��
 *   h) select   selectbox, ���������� ������
 *   i) date     ��� ���������� ������ � ����, ������� � �����
 *   j) checks   ������ checkbox'��, � ������������� ������� (������� ��������� ����� � ����� form_create)
 *   k) file     ���� - TODO
 *   l) image    ���� � ��������� - TODO
 * 2. default - �������� �� ���������
 * 3. notnull - �������� �������������� ����; ��������� ������ ��� int, select, date,
 *    radio � check
 * 4. min, max - ����������� (������������) �������� ��� int, ����� ������
 *    (char, enchar, text), ���� (�������� �������� ����� ������ ��� ���� (2001), ��� ���� �
 *    ������ (2001-10) ��� ��� ���� ���� (2001-10-12)
 * 5. regexp, regexp-error - ���������� ��������� ��� �������� �������� � �����
 *    ��������� �� ������
 * 6. function - callback-������� ������� function check($value) ��� �������� ����
 * 7. class, size - �������������� ��������� ��� html-���� ����
 * 8. checks_zeroes - ���� type=checks, �������������� ������ �� ��������� ������ ������
 *    ������������� �����; ���� �� ���������� ���� check_zeroes, ���� ���� ������������
 * @param $url url ����������� �����
 * @param $fname ��� �����
 * @param $extra �������������� ��������� ��� ���� <form>
 * @param $remote_data ������� ������, ��������� ���������� ����� (��������, ���������
 * ������� � ���� ������)
 * @param $html html-���� ��� ����� �����
 * @param $values �������� �����
 * @param $errors ��������� �� �������
 * @param $form_tag <form .....>
 * @return true, ���� ����� ���������� � �� �������� �� ����� ������; ����� - false
 */
function form_create($fields, $url, $fname, $extra, $remote_data, &$html, &$values, &$errors, &$form_tag)
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
		"		alert('������ � ���� \"'+r[1]+'\":\\n'+r[2]);\n".
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
			if ($is_sent && $error = form_check_rules(&$val, $value))
				$errors[$name] = $error;
			$form_tag .= form_generate_rule_check(&$val, $name);
		}

	$form_tag .= "}\n";
	$form_tag .= "</script>\n";

	return $is_sent && !$errors;
}

/**
 * form_is_sent()
 * ���������, �������� �� $HTTP_POST_VARS ������ � ����� form_name.
 * @param $form_name �������� �����
 * @return true, ���� ��������, ����� false
 */
function form_is_sent($form_name)
{
	global $HTTP_POST_VARS, $form_signature;
	if (isset($HTTP_POST_VARS[$form_name."-".$form_signature])) return true;
	return false;
}

/**
 * form_generate_field()
 * ������� html-��� ��� �������� ����� (input, select � �.�.) � ���� ������, ����
 * ������ html-����� ��� radio-��������.
 * @param $field �������� ����
 * @param $name  �������� ����
 * @param $value �������� ����
 * @return html-���, ���� ������ html-�����
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
			$output .= "<option value=".$form_null_key.">".($field["notnull"]?"- �������� -":"- �� ������� -")."\n";
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
		$output .= "<option value=".$form_null_key.">����\n";
		for ($i = 1; $i <= 31; $i++)
		{
			$i2 = $i<10?"0".$i:$i;
			$output .= "<option value=".$i.($day==$i?" selected":"").">".$i2;
		}
		$output .= "</select>";
		
		$output .= "<select $name-m".$class.">";
		$output .= "<option value=".$form_null_key.">�����\n";
		for ($i = 1; $i <= 12; $i++)
			$output .= "<option value=".$i.($month==$i?" selected":"").">".$form_monthes[$i-1];
		$output .= "</select>";
		
		$output .= "<select $name-y".$class.">";

		$output .= "<option value=".$form_null_key.">���\n";
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
 * ��������� �������� $value �� ������������ ��������, ������������ ��� ���� $field
 * @param $field ������������� ������ - �������� ����
 * @param $value ����������� ��������
 * @return ������ � ���������� �� ������, ���� false, ���� ��� � �������
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
 * ������� ������� ��� if-��������� �� ����� JScript, ������������ ���� �
 * ���� select-box'�� � ����� ������������� �����, ��������� � ��������� $date.
 * ����������� ����������� �������� ������ ���� ��� ���� � ������ ("2001", "2001-01").
 * ������ ����������:
 * "(form.date_y.value < 2000 || form.date_y.value == 2000 && (form.date_m.value < 03))"
 * @param $name ��� ���� �����
 * @param $compare ���� ���������; ����� ���� "<" ���� ">"
 * @param $date ����, � ������� ����� ����������
 * @return JScript-���
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
 * ������� ���������� JScript-��� ��� �������� $rule.
 * @param $rule ������� ��� �������� ����
 * @param $error ����� ��������� �� ������
 * @param $error_param ��������, ������������ � sprintf (���� $error �������� %s � �.�.)
 * @return ������ � ��������
 */
function form_generate_jscript_check($name, $title, $rule, $error, $error_param = false)
{
	if ($error_param == false)
		return "if ($rule) return new Array(form['$name'], \"$title\", \"".addslashes($error)."\");\n";
	return "if ($rule) return new Array(form['$name'], \"$title\", \"".addslashes(sprintf($error, $error_param))."\");\n";
}

/**
 * form_generate_rule_check()
 * ������� JScript-��� ��� �������� �������� ���� $name ��
 * ������������ ��������, ������������ ��� ���� $field. � ������ ������ 
 * @param $field �������� ����
 * @param $name ��� ����
 * @return ������, ���������� JScript-���, ���� false, ���� �������� �� ���������
 */
function form_generate_rule_check($field, $name)
{
	global $form_errors, $form_null_key;
	// ��������� "notnull", "min" and "max" - ��-������� ��� ������ �����
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

	// Callback-�������, ����������, �� ������� ����������� �� ����� :(
	return $r;
}

/**
 * form_generate_value()
 * ��������� �������� ����, �������� �� ������� ��������� ��������:
 * 1) ��������, ��������� � ����� (���� $isset)
 * 2) �������� �� �������� ��������� ($remote_data)
 * 3) �������� �� ��������� ($field["default"])
 * @param $field �������� ����
 * @param $name �������� ����
 * @param $remote_data ������� ������, ������������ ����� ����� ��� �� ����������
 * ("��������" ������)
 * @param $isset ����������� �� �����? ���� ���, ����� $remote_data ����,
 * ���� $remote_data==false, �������� �� ���������
 * @return �������� ���� (false - �������� �� �������, �������� � select'��)
 */
function form_generate_value($field, $name, $remote_data, $isset)
{
	global $HTTP_POST_VARS, $HTTP_POST_FILES, $form_signature, $form_null_key;

	if ($isset && !$field['disabled'])
		switch ($field["type"])
		{
		case "bit":
			return $HTTP_POST_VARS[$name]?1:0;
		case "date":
			$y = &$HTTP_POST_VARS[$name."-y"];
			$m = &$HTTP_POST_VARS[$name."-m"];
			$d = &$HTTP_POST_VARS[$name."-d"];
			if ($y == $form_null_key || $m == $form_null_key || $d == $form_null_key)
				return false;
			return sprintf("%04d-%02d-%02d", $y, $m, $d);
		case "select":
			if ($field['multiple'])
				$name = str_replace('[]', '', $name);
			return $HTTP_POST_VARS[$name]!=$form_null_key?$HTTP_POST_VARS[$name]:false;
		case "checks":
			$r = array();
			if ($field["checks_zeroes"])
				foreach($field["elements"] as $key=>$value)
					$r[$key] = $HTTP_POST_VARS[$name."-".$key]?true:false;
			else
				foreach($field["elements"] as $key=>$value)
					if ($HTTP_POST_VARS[$name."-".$key])
						$r[$key] = true;
			return $r;
		default:
			return $HTTP_POST_VARS[$name]!=""?$HTTP_POST_VARS[$name]:false;
		}

	if (isset($remote_data[$name]))
		return $remote_data[$name];
	else
		return $field["default"];
}

?>
