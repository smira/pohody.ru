<?

/*	
	Description    : �����. ������������������ �� ������������ ������� ��������� � ���� JScript'��.
	
	Revisions      : xx-xx-xxxx  | Pavel Savich         | ����� ���-�� ������
*/

/*

CREATE TABLE forum (
	id             INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	parent_id      INT,
	forum_id       INT,
	topic_id       INT,
	descendants    INT NOT NULL DEFAULT '0',
	children       INT NOT NULL DEFAULT '0',
	last_answer_id INT,
	posted         DATETIME NOT NULL,
	updated        DATETIME NOT NULL,
	level          TINYINT NOT NULL DEFAULT '0',
	notify_answers TINYINT NOT NULL DEFAULT '0',
	ip             VARCHAR(15),

	title          VARCHAR(255),
	body           TEXT,
	author         INT,
	author_name    VARCHAR(64),
	author_mail    VARCHAR(128),
	ro	       TINYINT( 1 ) UNSIGNED NOT NULL,

	INDEX (parent_id),
	INDEX (forum_id),
	INDEX (topic_id),
	INDEX (updated)
);

������������.
* ��������� (message) - ����� ���������
* ����� (forum) - �������� ���������, �� ������� ��������
* ���� (topic) - ���������, ��������� �������� �������� �����

������������ ���� ���������.
1. id - ���������� id ���������
2. parent_id - id ������������� ��������� (����� ������)
3. forum_id - id ������ (����� ������; �.�. ��� �������� ������ ��� ID ��� ����������)
4. topic_id - id ������ (����� ������ � ������ ������; �.�. ��� �������� ������ ��� ID ��� ����������)
5. descendants - ����� ���������� �������� � ���������
6. children - ���������� ���������������� ����� � ���������
7. last_answer_id - id ���������� ���������
8. posted - ����� �������� (UNIX_TIMESTAMP)
9. updated - ����� ���������� ���������� ������ (���������� ��������)
10. level - ������� ��������� (0 - �����, 1 - ����, 2 - ����� �� ���� � �.�.)

�������������� ����.
������������ �������� �������������� ����:
1. title - ��� ������ ��� ������; ��� ��������� ��������� - subj
2. body - ���� ���������

*/

class qforum
{

/**
 * $con
 * ����������
 */
var $con;

/**
 * $table
 * ������� ��, � ������� �������� ���������� � ����������.
 */
var $table;

/**
 * $draw
 * �������-������� ��� ������ ��������� � ������ void draw($msg), ��� $msg - ���� ���������.
 */
var $draw;

/**
 * $draw_prefix, $draw_suffix
 * ������� � ������� ��� ������ ������ ��������� � ������ (�������� � �������� �������) - ������.
 */
var $draw_prefix, $draw_suffix;

/**
 * $fields
 * ������ �������������� �����, ����������� ��� ������ ������ ���������. ����� �������.
 */
var $fields;

/**
 * $msg_dir
 * ���������� (������������ ����� �����), � ������� �������� js-��������� ������
 */
var $msg_dir;

/**
 * $forums_draw
 * �������-������� ��� ������ ���������� � ������ � ������ �������
 * void forums_draw($msg, $n), ��� $msg - ���� ���������, $n - ���������� ����� ������ � ������.
 */
var $forums_draw;

/**
 * $forums_draw_prefix, $forums_draw_suffix
 * ������� � ������� ��� ������ ������ �������. ��� ����������.
 */
var $forums_draw_prefix, $forums_draw_suffix;

/**
 * $max_line
 * ������������ ����� ��������������� ������.
 */
var $max_line;

/**
 * $path
 * ������ ���� � ������� ������ (������ ������ �� ������������)
 */
var $path;

/**
 * qforum
 * �����������
 * @param $con ������� � ��
 */
function qforum(&$con)
{
	$this->con = &$con;
	$this->table = "forum";
	$this->draw_prefix = "<ul>\n";
	$this->draw_suffix = "</ul>\n";
	$this->fields = "title, UNIX_TIMESTAMP(posted) AS posted, author_name, author_mail, ro";
	$this->forums_fields = "title, UNIX_TIMESTAMP(updated) AS updated, author_name, author_mail, body, ro";
	$this->max_line = 40;
}



/**
 * new_forum()
 * ������� ����� �����.
 * @param $msg ����������, ���� ����������� ���������
 *     ������������ ����� ���.
 *     ����, ����������� ������������� � ������, ���� ��� �� �������:
 *     1. id - id ��������� (��� ���� ����������� ������������� ������, ������ ������ ��������)
 *     2. posted - ����� ��������
 *     3. updated - ����� ���������� ������� (��� ������ ��������� = posted)
 *     4. ip - IP ����������� ���������
 * @return id ���������, ���� ��������� ������� ���������; ����� false
 */
function new_forum(&$msg)
{
	// �������������
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// �������������� ����
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = 0;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// ���������� ��������� � ����
	$msg["id"] = $id = $this->con->set($this->table, &$msg);

	// ������� ����������� �����
	$this->create_js_message($msg);

	return $id;
}

/**
 * new_topic()
 * ������ ����� �����. ������� ���� ��������� ���� descendants, children,
 * updated � ��������� ��������� ������.
 * @param $forum_id id ������, ���� ����������� �����
 * @param $msg ����������, ���� ����������� ���������
 *     ������������ ����� ���.
 *     ����, ����������� ������������� � ������, ���� ��� �� �������:
 *     1. id - id ��������� (��� ���� ����������� ������������� ������, ������ ������ ��������)
 *     2. forum_id - id ������, ���� �������� ���������
 *     3. parent_id - id ��������, ���� �������� ��������� (= forum_id)
 *     4. posted - ����� ��������
 *     5. updated - ����� ���������� ������� (��� ������ ��������� = posted)
 *     6 ip - IP ����������� ���������
 * @return id ���������, ���� ��������� ������� ���������; ����� false
 */
function new_topic($forum_id, &$msg)
{
	// ��� �� ��?
	if (!$forum_id)
		return false;
	if ($this->con->property("SELECT parent_id FROM ".$forum->table." WHERE id = ".$forum_id))
		return false;

	// �������������
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// �������������� ����
	if (!isset($msg["forum_id"]))
		$msg["forum_id"] = $forum_id;
	if (!isset($msg["parent_id"]))
		$msg["parent_id"] = $forum_id;
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = 1;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// ���������� ��������� � ����
	$msg["id"] = $id = $this->con->set($this->table, &$msg);
	if ($id === false)
		return false;

	// ���������� ��������� ���������
	$this->con->update("UPDATE ".$this->table." SET children = children+1, descendants = descendants+1, updated = '".$now."' WHERE id = ".$forum_id);

	// ������� ����������� �����
	$this->create_js_message($msg);
	// ��������� ������
	$this->create_js_tree($msg["forum_id"], $msg["id"]);

	return $id;
}

/**
 * new_answer()
 * ������ ����� ���������-����� � �����. ������� ���� ��������� ���� descendants, children,
 * updated � ������������ ���������.
 * @param $msgs ��������� ��������� (���� ��� �����)
 * @param $ids ���������, ����������� �� id ��������� �������� ��� ������ � $msgs (������������ read_topic)
 * @param $msg ����������, ���� ����������� ���������
 *     ������������ ����:
 *     1. parent_id - id ������������� ���������
 *     ����, ����������� ������������� � ������, ���� ��� �� �������:
 *     1. id - id ��������� (��� ���� ����������� ������������� ������, ������ ������ ��������)
 *     2. forum_id - id ������, ���� �������� ���������
 *     3. topic_id - id ������
 *     4. posted - ����� ��������
 *     5. updated - ����� ���������� ������� (��� ������ ��������� = posted)
 *     5. ip - IP ����������� ���������
 * @return id ���������, ���� ��������� ������� ���������; ����� false
 */
function new_answer(&$msgs, &$ids, &$msg)
{
	// ��� �� ��?
	$parent_id = $msg["parent_id"];
	if (!isset($parent_id) || !isset($msgs[$ids[$parent_id]]) || sizeof($msgs) < 2)
		return false;

	// �������������
	unset($msg["id"]);
	$now = date("Y-m-d H:i:s");

	// �������������� ����
	if (!isset($msg["forum_id"]))
		$msg["forum_id"] = $msgs[0]["id"];
	if (!isset($msg["topic_id"]))
		$msg["topic_id"] = $msgs[1]["id"];
	if (!isset($msg["posted"]))
		$msg["posted"] = $now;
	if (!isset($msg["updated"]))
		$msg["updated"] = $now;
	if (!isset($msg["ip"]))
		$msg["ip"] = getenv("REMOTE_ADDR");
	$msg["level"] = $msgs[$ids[$parent_id]]["level"]+1;
	$msg["body"] = str_replace("\r", "", $msg["body"]);

	// ���������� ��������� � ����
	$msg["id"] = $id = $this->con->set($this->table, &$msg);
	if ($id === false)
		return false;

	// ���������� ������� ���������
	$inc = "children = children+1, descendants = descendants+1, updated = '".$now."', last_answer_id = ".$id;
	while ($parent_id)
	{
		$this->con->update("UPDATE ".$this->table." SET ".$inc." WHERE id = ".$parent_id);
		
		$parent_id = $msgs[$ids[$parent_id]]["parent_id"];
		$inc = "descendants = descendants+1, updated = '".$now."', last_answer_id = ".$id;
	}

	// ������� ����������� �����
	$this->create_js_message($msg);

	// ��������� ������
	$this->create_js_tree($msg["forum_id"], $msg["topic_id"]);

	return $id;
}

/**
 * modify_message()
 * ������ ��������� � ��������� ������.
 * @param $id id ����������� ���������
 * @param $changes ������������� ������ ���������� �����
 * @param $moderated ���� true, ������������� ���� moderated ������ ������� ���� (���� ��� �� �����������)
 * @return true - ��, false - ������
 */
function modify_message($id, &$changes, $moderated = false)
{
	// ��� �� ��?
	if (!$id)
		return false;

	// �������������
	unset($changes["id"]);

	// �������������� ����
	if ($moderation && !isset($msg["moderation_time"]))
		$msg["moderated"] = date("Y-m-d H:i:s");
	if (isset($changes["body"]))
		$changes["body"] = str_replace("\r", "", $changes["body"]);

	// ���������� ����
	$this->con->set($this->table, &$changes, $id);

	// ��������������� jscript-������
	$msg = $this->con->qquery("SELECT * FROM ".$this->table." WHERE id = ".$id);
	$this->create_js_message($msg);

	// ��������� ������
	if ($msg["forum_id"])
		$this->create_js_tree($msg["forum_id"], $msg["topic_id"]?$msg["topic_id"]:$msg["id"]);

	return true;
}

/**
 * delete_children()
 * ������� ���� �������� ���������; �� ��������� ��������� ����� � �� ���������.
 * @access private
 * @param $id id ������������� ���������
 * @param $deleted ������������� ������, ����������� id-������� ��������� ���������
 * @return true - ��, false - ������
 */
function delete_children($id, &$deleted)
{
	if (!$id) return;
	$deleted[$id] = true;

	// ����������� � ������
	$r = $this->con->query("SELECT id FROM ".$this->table." WHERE parent_id = ".$id);
	while (list($child) = $r->fetch_row())
		$this->delete_children($child, &$deleted);

	if ($r->num_rows())
		$this->con->query("DELETE FROM ".$this->table." WHERE parent_id = ".$id);

	$r->free();
}

/**
 * delete_message()
 * ������� ��������� �� ������. ���� ���� ���������.
 * @param $id id ���������� ���������
 * @return true - ��, false - ������
 */
function delete_message($id)
{
	// ��� �� ��?
	if (!$id)
		return false;
	
	$parent_id = $this->con->property("SELECT parent_id FROM ".$this->table." WHERE id = ".$id);


	// ����������� � ������
	$deleted = array();
	$this->delete_children($id, &$deleted);
	$this->con->query("DELETE FROM ".$this->table." WHERE id = ".$id);

	if ($moderation && !isset($msg["moderation_time"]))
		$msg["moderated"] = date("Y-m-d H:i:s");

	$m_topic_id = false;
	$m_forum_id = false;

	// ���������� �������
	$desc = "descendants = descendants-".sizeof($deleted).", children = children-1";
	while ($parent_id)
	{
		list($next_parent_id, $last_answer_id, $topic_id) = $this->con->qquery("SELECT parent_id, last_answer_id FROM ".$this->table." WHERE id = ".$parent_id);
		$this->con->update("UPDATE ".$this->table." SET ".$desc.($deleted[$last_answer_id]?", last_answer_id = NULL":"")." WHERE id = ".$parent_id);

		if (!$topic_id && $next_parent_id)
			$m_topic_id = $parent_id;
		if (!$topic_id && !$next_parent_id)
			$m_forum_id = $parent_id;

		$parent_id = $next_parent_id;
		$desc = "descendants = descendants-".sizeof($deleted);
	}

	// ��������� ������
	if ($m_forum_id && $m_topic_id)
		$this->create_js_tree($m_forum_id, $m_topic_id);

	return true;
}

/**
 * read()
 * ��������� ������ ���������.
 * @param $forum_id id ������; ���� false, ����������� ������ ������� (������ �������� ���������)
 * @param $topic_id id ������, ������� ���������� �������; ���� ���� �������� �� false, ��������� ������
 * ����� � ������ id + �������� ��������� ������
 * @return array($msgs, $children, $ids)
 *     1. $msgs - ������ ��������� (�� �������������), ������������� �� ���� ��������;
 *         0 - �������� ��������� ������; 1 - �����... ����� ��� ���� � �� ����
 *     2. $children - ������ �����; ��� ������� ��������� �� $msgs, �������� �����,
 *     �������� ������ ���������� �����; ���� ������������� �� ���� ��������; ���� - ���������� �����
 *     ��������� � $msgs
 *     3. $ids - ������������� ������; ����� - id ���������; �������� - ���������� ������ ���� ��������� �
 *     ������� $msgs
 */
function read($forum_id = false, $topic_id = false)
{
	$main_fields = "id, parent_id, forum_id, topic_id, last_answer_id, descendants, children, level";

	$msgs = array();
	$children = array();
	$ids = array();

	if ($forum_id !== false)
	{
		$all_fields = $main_fields.", ".$this->fields;
		$res = $this->con->query("SELECT ".$all_fields." FROM ".$this->table." WHERE id = ".$forum_id.($topic_id===false?" OR forum_id = ".$forum_id:" OR id = ".$topic_id." OR topic_id = ".$topic_id)." ORDER BY id");
	}
	else {
		$all_fields = $main_fields.", ".$this->forums_fields;
		$res = $this->con->query("SELECT ".$all_fields." FROM ".$this->table." WHERE parent_id IS NULL ORDER BY id");
	}

	for ($i = 0; $r = $res->fetch_array(); $i++)
	{
		$r["@path"] = $this->message_path($r);
		$msgs[] = $r;
		$ids[$r["id"]] = $i;
		if ($r["parent_id"])
		{
			$parent = $ids[$r["parent_id"]];
			if (isset($parent))
				$children[$parent][] = $i;
		}
	}
	$res->free();
	return array($msgs, $children, $ids);
}

/**
 * topics()
 * ���������� ������ ������ id-������ ������� ������. � ���� �������� ������.
 * @param $forum_id id ������
 * @param $limit ����������� ����� ������ (�� ������������ ��������)
 * @return ������ id-������ �������
 */
function topics($forum_id, $limit = false)
{
	return $this->con->query_list("SELECT id FROM ".$this->table." WHERE parent_id = ".$forum_id." ORDER BY updated DESC".($limit?" LIMIT ".$limit:""));
}

/**
 * read_message()
 * ���������� ������ ���� �� ������� ���������.
 * @param $id id ���������
 * @param $fields ������ ����� (����� �������)
 * @return ������ �������� �����
 */
function read_message($id, $fields)
{
	return $this->con->qquery("SELECT ".$fields." FROM ".$this->table." WHERE id = ".$id);
}

/**
 * html()
 * ������� ������ ���������.
 * @param $msgs ������ ��������� (������������ �������� forum_read)
 * @param $children ������ �����
 * @param $i ���������, �������� �������� ���������� ����������; �� ��������� - 0, �.�. �����; false
 * �������� "�������� ��� ������" (��������, ����� ����� ������� ��� ������)
 * @return array($msgs, $children, $ids)
 *     1. $msgs - ������ ��������� (�� �������������), ������������� �� ���� ��������
 *     2. $children - ������ �����; ��� ������� ��������� �� $msgs, �������� �����,
 *     �������� ������ ���������� �����; ���� ������������� �� ���� ��������; ���� - ���������� �����
 *     ��������� � $msgs
 *     3. $ids - ������������� ������; ����� - id ���������; �������� - ���������� ������ ���� ��������� �
 *     ������� $msgs
 */
function html(&$msgs, &$children, $i = 0)
{
	$draw = $this->draw;
	$ch = &$children[$i];

	if ($i)
		echo $this->draw_prefix;
	for ($j = sizeof($ch)-1; $j>=0; $j--)
	{
		$ni = $ch[$j];
		$draw(&$msgs[$ni]);
		if ($children[$ni])
			$this->html($msgs, $children, $ni);
	}
	if ($i)
		echo $this->draw_suffix;
}

/**
 * html_forums()
 * ������� ������ �������.
 * @param $msgs ������ ��������� (�������
 * @param $forum_topic id ������
 * @param $i ���������, �������� �������� ���������� ����������; �� ��������� - 0, �.�. �����; false
 * �������� "�������� ��� ������" (��������, ����� ����� ������� ��� ������)
 * @return array($msgs, $children, $ids)
 *     1. $msgs - ������ ��������� (�� �������������), ������������� �� ���� ��������
 *     2. $children - ������ �����; ��� ������� ��������� �� $msgs, �������� �����,
 *     �������� ������ ���������� �����; ���� ������������� �� ���� ��������; ���� - ���������� �����
 *     ��������� � $msgs
 *     3. $ids - ������������� ������; ����� - id ���������; �������� - ���������� ������ ���� ��������� �
 *     ������� $msgs
 */
function html_forums(&$msgs)
{
	$prefix = $this->forums_draw_prefix;
	$draw = $this->forums_draw;
	$suffix = $this->forums_draw_suffix;

	$prefix();
	foreach ($msgs as $j=>$msg)
		$draw(&$msg, $j);
	$suffix();
}

/**
 * message_path()
 * ��������� ����� ����������� "����" ��� ���������. ��� ������ ��� ������ "$id", ��� ���� - "$forum_id/$id",
 * ��� ��������� ��������� - "$forum_id/$topic_id/$id"
 * @param $msg ���������
 * @return ����������� ����
 */
function message_path(&$msg)
{
	// �����?
	if (!$msg["parent_id"])
		return $msg["id"];
	// �����?
	if (!$msg["topic_id"])
		return $msg["forum_id"]."/".$msg["id"];
	// �����!
	return $msg["forum_id"]."/".$msg["topic_id"]."/".$msg["id"];
}

/**
 * create_js_message()
 * ������� htm-���� � js-������ ��������� � ������ :-)
 * @param $msg ���������
 * @return true, � ������ ������; ����� false
 */
function create_js_message(&$msg)
{
	$fname = $this->msg_dir."/".$this->message_path($msg).".htm";
	$f = std_fopen($fname, "w", 0775);
	if ($f === false)
		return false;
	$s = "<html>\n<head>\n";
	$s .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">\n";
	$s .= "<meta http-equiv=\"Cache-Control\" content=\"max-age: 31536000\">\n";
	$s .= "</head>\n";
	$s .= "<script>\n";
	$s .= "if (!window.parent || !window.parent.messageLoaded) document.location='/';\n";
	$s .= "else {\n";
	$s .= "  var msg = new Object;\n";

	foreach ($msg as $key=>$value)
		if (isset($this->static_fields[$key]))
			$s .= "  msg.".$key." = ".
				($this->static_fields[$key]?"\"".preg_replace('/<\/?script(.*?)>/i','',addcslashes($value, "\0..\37\""))."\"":$value).";\n";
	$s .= "  window.parent.messageLoaded(".$msg["id"].", msg)\n";
	$s .= "}\n";
	$s .= "</script>\n";
	$s .= "</html>\n";
	fwrite($f, $s);
	fclose($f);
	chmod($fname, 0664);
	return true;
}

/**
 * create_js_tree()
 * ������� js-���� � js-������ ��������� � ������ :-)
 * @param $msg ���������
 * @return true, � ������ ������; ����� false
 */
function create_js_tree($forum_id, $topic_id)
{
	list($msgs, $children, $ids) = $this->read($forum_id, $topic_id);

	ob_start();
	$this->html($msgs, $children);
//	$s = "document.write(\"".addcslashes(ob_get_contents(), "\0..\37\"")."\");";
	$s = ob_get_contents();
	ob_end_clean();

	std_mkdir($this->msg_dir."/".$forum_id."/tree", 0755);
	$fname = $this->msg_dir."/".$forum_id."/tree/".$topic_id.".htm";

	$temp = //tempnam($this->msg_dir."/", "tmp.");
                $this->msg_dir."/".$forum_id."/tree/".$topic_id.".htm2";
	$f = fopen($fname, "w");
	if ($f === false)
	   return false;


	fwrite($f, $s);
	fclose($f);
	//rename($temp, $fname);//, 0775);
	//chmod($fname, 0664);
	return true;
}

/**
 * walk_up()
 * ������������� ����� �� ������, ������� callback-�������
 * @param $id �������, � �������� ���������� �����
 * @param $fields SQL-����, ������� ����� ��������; ����� ������� (����������� parent_id)
 * @param $callback callback ������� void callback($msg)
 * @return true, � ������ ������; ����� false
 */
function walk_up($id, $fields, $callback)
{
	$msg = $this->con->qquery("SELECT ".$fields." FROM ".$this->table." WHERE id = ".$id);
	if ($msg === false)
		return;

	$callback($msg);
	if ($msg["parent_id"])
		$this->walk_up($msg["parent_id"], $fields, $callback);
}

/**
 * js_priv_object()
 * ������� JavaScript, ��������� ������, ����������� �������� $priv.
 * ������:
 * js_priv_object("priv", array("pray"=>true, "love"=>true))
 *   => "var priv=new Object; priv.pray = true; priv.love = true;"
 * @param $name �������� JavaScript-����������
 * @param $priv ������������� ������ ����������
 * @return true, � ������ ������; ����� false
 */
function js_priv_object($name, $priv)
{
	$r = "var ".$name." = new Object;\n";
	foreach ($priv as $key=>$value)
		$r .= $name.".".$key." = ".($value?"true":"false").";\n";
	return $r;
}

} // end of class


?>
