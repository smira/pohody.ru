/*
 *****************************************
 * ������� ��� ������ � cookie		 *
 *****************************************
 */

function setCookie(name, value, expires, path, domain, secure)
{
	var curCookie = name + "=" + escape(value) +
		((expires) ? "; expires=" + expires.toGMTString() : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
	if ((name + "=" + escape(value)).length <= 4000)
		document.cookie = curCookie;
	else
		if (confirm("Cookie ��������� 4KB � ����� ������� !"))
				document.cookie = curCookie;
}

function getCookie(name)
{
	var prefix = name + "=";
	var cookieStartIndex = document.cookie.indexOf(prefix);
	if (cookieStartIndex == -1)
		return null;
	var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length);
	if (cookieEndIndex == -1)
		cookieEndIndex = document.cookie.length;
	return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex));
}

function deleteCookie(name, path, domain)
{
	if (getCookie(name))
	{
		document.cookie = name + "=" +
			((path) ? "; path=" + path : "") +
			((domain) ? "; domain=" + domain : "") +
			"; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}
}


/*
 *****************************************
 * �������� � ����������� ���������	 *
 *****************************************
 */

/**
 * preprocessors
 * ������ callback-�������, �������������� ��������� ����� ������� ��� � ����.
 * ������� �������������� �������� preproc(); ����������� � �������� �������.
 */
var preprocessors = new Array();

/**
 * preproc()
 * ������� ������������ callback-������� - ������������ ���������.
 * @param callback func callback-�������
 */
function preproc(func)
{
	preprocessors[preprocessors.length] = func;
}

function getObjectHandle (wnd, objectName )
{
	if (wnd.document.getElementById) {
		return wnd.document.getElementById(objectName);
	}
	if (wnd.document.all) {
		return wnd.document.all(objectName);
	}
}

/**
 * messageLoaded()
 * ������� ���������� ����� ����� ��������� ������������ ������������� ��������� � �������� �� ����� ���
 * �� ��������. ���������� �������� messageLoaded() �� ������� ��������, �.�. ����� ����� ����������
 * ��������, ����� ������ ���������� � ���� �������, ����� ������ ��� �� ����������.
 * @param int id ����� ������������ ���������
 * @param Object msg ���� ���������
 */
var messages = new Array();
function messageLoaded(id, msg)
{
	var d = getObjectHandle(window, "m"+id);

	var save_body = msg.body;
	for (var i = preprocessors.length-1; i>=0; i--)
		preprocessors[i](msg, id);
	var i = messages.length;

	d.innerHTML = messageHTML(msg, id, i);
	d.style.display = 'block';
	getObjectHandle(window, "td"+id).className = 'f_expanded';

	msg.body = save_body;
	messages[i] = msg;

	if (!clicked)
	{
		d.scrollIntoView(true);
		scrollBy(0, -100);
	}
}

/**
 * messageShow()
 * ������� ����������/�������� ���������.
 * @param int id ����� ���������
 * @param bool visible true - ��������; false - ������
 */
function messageShow(id, visible)
{
	getObjectHandle(window, "m"+id).style.display = visible?"block":"none";
	getObjectHandle(window, "td"+id).className = visible?"f_expanded":"f_collapsed";
	var img = getObjectHandle(window, "ar"+id);
	img.src = img.src.replace(/f_a(.)2?/, "f_a$1"+(visible?"2":""));
}

/**
 * messageIsLoaded()
 * ���������� true, ���� ��������� ���������.
 * @param int id ����� ���������
 */
function messageIsLoaded(id)
{
	var txt = getObjectHandle(window, "m"+id).innerHTML;
	var loading = "<div class=f_loading>";
	return txt.length > 1 && txt.toUpperCase().substr(0, loading.length)!=loading.toUpperCase();
}

/**
 * messageIsLoaded()
 * ���������� true, ���� ��������� ���������.
 * @param int id ����� ���������
 */
function messageIsVisible(id)
{
	var txt = getObjectHandle(window, "m"+id).innerHTML;
	return txt.length > 1;
}

/**
 * messageClicked()
 * ������� ����������� ���� �� ���������. ���� ���� ��������� �����, ���������� ���.
 * ���� ��� - ����������/��������.
 * @param int id ����� ������������ ���������
 */
var clicked = false;
var messagesClicked = new Array();
function messageClicked(id)
{
	clicked++;
	var d = getObjectHandle(window, "m"+id);

	if (!messageIsLoaded(id))
	{
		// ����������, ����� ����� ����������� �� ����� ������������ ����������� (�������� ���)
		messagesClicked[messagesClicked.length] = id;
		// "��������� ������������..."
		d.innerHTML = "<div class=f_loading>��������� ������������...</div>";
		// ������������� ����� ��� ������������ ��������
		var src;
		if(src = event.srcElement)
		{
			while (src.tagName != "A")
				src = src.parentElement;
			if(src.target == "message_loader")
			{
				src.target = "message_loader"+lframe_next;
				lframe_next = (lframe_next+1)%lframe_count;
			}
		}

		// ���������� ���������
		messageShow(id, true);
		return true;
	}

	messageShow(id, d.style.display != "block");
	return false;
}

/**
 * collapseAll()
 * ������� �������� ��� �������� ���������.
 */
function collapseAll(id)
{
	for (var i = 0; i < messagesClicked.length; i++)
		messageShow(messagesClicked[i], false);
	getObjectHandle(window, "td"+id).scrollIntoView(true);
	scrollBy(0, -100);
	return true;
}

/**
 * messageHTML()
 * ������� HTML ��� ���������.
 * @param Object msg ���������
 * @param int id id ���������
 * @param int i ������ ��������� � ���������� ������� messages
 */
function messageHTML(msg, id, i)
{
	var r = "";
	r += "<table class=f_open cellspacing=0 cellpadding=0>\n";
	r += "<tr><td>";
	r += "<div class=f_msg>" + msg.body + "</div>";
	r += "</td></tr>";
	r += "<tr class=f_opentr><td>";
	r += "<div class=f_fnc>";
	r += "<a href='javascript:void(0)' onclick='return messageClicked("+id+")' class=f_fnc>��������</a>";
	r += " | <a href='javascript:void(0)' onclick='return collapseAll("+id+")' class=f_fnc>�������� ���</a>";
	if (priv.forum_all || priv.forum_write)
	{
		r += " | <a href=\"javascript:forumControl('answer', "+id+", messages["+i+"])\" class=f_fnc>��������</a>";
		if (msg.author_mail != "")
			r += " | <a href=\"javascript:forumControl('answer_mail', "+id+", messages["+i+"])\" class=f_fnc>�������� �� �����</a>";
	}
	r += " | <a href=\"javascript:void(0)\" onclick=\"copyLink("+id+")\" class=f_fnc title=\"���������� ������ �� ������ ��������� � ����� ������ (��������, ����� ������� �����)\">���������� ������</a>";

	if (priv.forum_all || priv.forum_moderate || priv.forum_edit)
		r += " | <a href=\"javascript:forumControl('edit', "+id+", messages["+i+"])\" class=f_fnc>��������</a>";
	if (priv.forum_all || priv.forum_moderate || priv.forum_delete)
		r += " | <a href=\"javascript:forumControl('delete', "+id+", messages["+i+"])\" class=f_fnc>�������</a>";

	r += "</div>";
	r += "</td></tr>";
	r += "</table>";
	return r;
}

/*
 *****************************************
 * ������� ���������� �������		 *
 *****************************************
 */

/**
 * forumControl()
 * ������� ������������ ����������� ������� ������������.
 * @param String job ����� ��������������:
 *   answer - �������� �� ��������� id
 *   topic  - ����� ����� � ������ id
 *   edit   - ������������� ��������� id
 *   forum  - ������� (���� id == null) ����� ��� �������� ������ � ������ id
 * @param int id id ��������� - ��. job
 */
var forumControlData = new Array();

function forumControl(job, id, msg)
{
	switch (job)
	{
	case "delete":
		if (!confirm("�� �������, ��� ������ �������?"))
			return;
		window.open("?job=delete&id="+id, "message_loader");
		return;
	case "forum":
		window.open("?job=forum"+(id?"&id="+id:""), "_blank", "width=640,height=420,toolbar=no,menubar=no,scrollbars=no,status=yes,resizable=yes");
		return;
	case "answer_mail":
		var tmp = answerTemplate(msg);
		var body = tmp.body.replace(/\n/g, "%0A").replace(/&/g, "%26");
//		window.open("mailto:"+msg.author_mail+"?subject="+tmp.title+"&body="+body);
		document.location = "mailto:"+msg.author_mail+"?subject="+tmp.title+"&body="+body;
		return;
	case "answer":
		msg = answerTemplate(msg);
		break;
	case "topic":
		msg = new Object;
		msg.author_name = getCookie("author_name");
		msg.author_mail = getCookie("author_mail");
		msg.notify_answers = true;
		break;
	}

	var wnd = window.open("edit.htm", "_blank", "width=640,height=250,toolbar=no,menubar=no,scrollbars=no,status=yes,resizable=yes");
	forumControlData[forumControlData.length] = new Array(wnd, job, id, msg);
}

/**
 * nick2quoter()
 * ���������� ������ ����� ����, ��� ��������.
 * @param String nick ���
 * @return Object - ������ ����� ����
 */
function nick2quoter(nick)
{
	var r = nick.match(/(^|[\s\-]+)([a-zA-Z�-��-�0-9])/g);
	if (r != null)
		return r.join("").replace(/[\s\-]/g, "");
	return "";
}

/**
 * answerTemplate()
 * ������� ������ ��� ������ �� ����������.
 * @param Object msg �������� ���������
 * @return Object - ������
 */
function answerTemplate(msg)
{
	var tmp = new Object;

	var reg = msg.title.match(/^Re\[?(\d*)\]?:/);
	if (reg != null)
		if (reg[1] != "")
			tmp.title = msg.title.replace(/^Re\[\d*\]:/, "Re["+(new Number(reg[1])+1)+"]:");
		else
			tmp.title = msg.title.replace(/^Re:/, "Re[2]:");
	else
		tmp.title = "Re: "+msg.title;

	tmp.body = quote(msg.body, 40, nick2quoter(msg.author_name));

	tmp.author_name = getCookie("author_name");

	return tmp;
}

/**
 * forumEditLoaded()
 * ������ ���������� ����� ������ �������� ���� �������������� ���������.
 * @param Object msg �������� ���������
 * @return Object - ������
 */
function forumEditLoaded(wnd)
{
	var job, id, msg;
	for (var i = 0; i < forumControlData.length; i++)
		if (forumControlData[i][0] == wnd)
		{
			job = forumControlData[i][1];
			id = forumControlData[i][2];
			msg = forumControlData[i][3];
			break;
		}
	if (job == null)
		return false;

	var d = wnd.document;
	var title;
	switch (job)
	{
	case "topic": title = "����� ����"; break;
	case "answer": title = "����� �� ���������"; break;
	case "edit": title = "��������� ���������"; break;
	}
	d.title = title;
	d.forms[0].action = "forum.phtml?job="+job+"&id="+id;
	getObjectHandle(wnd, "win-title").innerHTML = title;

	var e;
	e = getObjectHandle(wnd, "title"); e.value = e.defaultValue = msg.title?msg.title:"";
	e = getObjectHandle(wnd, "author_name"); e.value = e.defaultValue = msg.author_name?msg.author_name:"";
	e = getObjectHandle(wnd, "body"); e.value = e.defaultValue = msg.body?msg.body:"";

	return true;
}

function copyLink(id)
{
	clipboardData.clearData();
	var link = forum_url+'?forum='+forum_id+'&go='+id;
	clipboardData.setData('Text', link);
	alert("������ '"+link+"'\n����������� � ����� ������");
}


/*
 *****************************************
 * �������-������������� ��� ���������	 *
 *****************************************
 */

/**
 * nl2br()
 * �������� \n �� <br>
 */
function nl2br(m)
{
	m.body = m.body.replace(/\n/g, "<br>");
}

/**
 * answers()
 * ������������ �����������.
 */
function answers(m)
{
	m.body = m.body.
		replace(/(^|\n+) *([A-Za-z0-9�-��-�]*)(&gt;&gt;&gt;)([^\n]*)/g, "$1<span class=rep3>$2$3$4</span>").
		replace(/(^|\n+) *([A-Za-z0-9�-��-�]*)&gt;&gt;([^&\n][^\n]*)/g, "$1<span class=rep2>$2&gt;&gt;$3</span>").
		replace(/(^|\n+) *([A-Za-z0-9�-��-�]*)&gt;([^&\n][^\n]*)/g, "$1<span class=rep>$2&gt;$3</span>");
}

/**
 * pseudo_tags()
 * ��������� [b]������-�����[/b].
 */
function pseudo_tags(m)
{
	m.body = m.body.
		replace(/\[b\](.*)\[\/b\]/ig, "<b>$1</b>").
		replace(/\[i\](.*)\[\/i\]/ig, "<i>$1</i>").
		replace(/\[&lt;\]/ig, "[").
		replace(/\[&gt;\]/ig, "]");
}

/**
 * strip_tags()
 * ������� ����.
 */
function strip_tags(m)
{
	m.body = htmlspecialchars(m.body);
}

/**
 * quote()
 * ������ �����, ����������� �������������� ������� ������ �� ��������,
 * �� _������ ��� ������������� �����_. ������������ ��������� ������ ���� /^\s*[a-zA-Z�-��-�0-9]*>/.
 * ������ ������������� ������ �������� �������� $quoter ($str = $quoter."> ".$str). � ������� �����������
 * ����� ������������� ������ ���� ������ ">". :-)
 * �������� � ���� ������.
 * @param String msg ����������� ���������
 * @param int max ������������ ����� ��������������� ������ (����� �������������); �� ��������� - 40
 * @param String quoter ������-������ - ������ ����� ���� ���� (Goga Nezabudkin => GN); �������� �� ����������
 */
function quote(msg, max, quoter)
{
	if (max == null) max = 40;
	if (quoter == null) quoter = "";

	var out = "";
	var started = 0;
	var space = false;
	var nonspaces = false;
	var prefix = quoter+"> ";
	var nobr = false;
	var state = 0;	// 0 - �� ����� ���� ������ ����� � �����; 1 - ����� ������� �����

	msg += "\n";
	var len = msg.length;
	for (var i = 0; i < len; i++)
	{
		var c = msg.charAt(i);
		if (c == "\n")
		{
			if (nonspaces)
				out += prefix + msg.substr(started, i-started) + "\n";
			else
				out += "\n";
			started = i + 1;

			space = false;
			nonspaces = false;
			prefix = quoter+"> ";
			nobr = false;
			state = 0;
			continue;
		}

		if (state == 0)
		{
			if (c == ">")
			{
				prefix = msg.substr(started, i-started) + ">";
				nobr = true;
				started = i;
			}

			let = c >= '�' && c <= '�' || c >= '�' && c <= '�' || c >= 'a' && c <= 'z' || c >= 'A' && c <= 'Z' || c >= '0' && c <= '9';

			if (c != " ")
				nonspaces = true;

			if (nonspaces && !let)
				state = 1;
		}

		if (state == 1)
		{
			if (c == " ")
				space = i;

			if (!nobr && i - started == max)
			{
				if (space)
				{
					out += prefix + msg.substr(started, space-started) + "\n";
					started = space+1;
				}
				else {
					out += prefix + msg.substr(started, i-started) + "\n";
					started = i;
				}
				space = false;
			}
		}
	}

	out = out.
		replace(/\n{3,}/, "\n\n").
		replace(/^\n+/, "").
		replace(/\n+$/, "\n");
	return out;
}

/**
 * quote_help()
 * ������� ��������� � ������� ��������.
 */
function quote_help(wnd)
{
	wnd.alert("������ ����������� ��������� ���������:\n\n"+
		"1. ���\n"+
		"    ����������� �����������.\n\n"+
		"2. ���� (�������������)\n"+
		"    ���������� ������ ������� �� ����������� ���������.\n"+
		"    ����� ������ �������, ������������� �� �����������\n"+
		"    �����������, ���������.\n\n"+
		"3. ������ (�� �������������)\n"+
		"    ���������� ��������� ���������� ���������, ������\n"+
		"    ����������� ����������� � ����������� ��� ������\n"+
		"    ����� \">\".");
}

/**
 * htmlspecialchars()
 * HTML-���� ������, ����� �� ���������� ��� ����������� � �������.
 * @param String txt �������� ������
 * @return String - ���������� ������
 */
function htmlspecialchars(txt)
{
	return txt.
		replace(/&/g, "&amp;").
		replace(/\"/g, "&quot;").
		replace(/</g, "&lt;").
		replace(/>/g, "&gt;");
}
