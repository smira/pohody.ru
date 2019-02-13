function setPointer(theRow, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()) {
        if (theAction == 'out') {
            newColor = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor = theMarkColor;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor = (thePointerColor != '')
                     ? thePointerColor
                     : theDefaultColor;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function

function countChecked(form)
{
    var count = 0;
    for (i = 0; i < form.elements.length; i++)
    	if ((form.elements[i].name.substring(0,3) == "row") && form.elements[i].checked)
    		count++;
    return count;
}

function prepareEdit(form, url, width, height)
{
    if (countChecked(form) != 1)
    {
    	alert("Должно быть выбрана ровно одно строка!");
    	return;
    }
    var primary;
    for (i = 0; i < form.elements.length; i++)
    	if ((form.elements[i].name.substring(0,3) == "row") && form.elements[i].checked)
    	   primary = escape(form.elements[i].value);
    window.open(url + "&primary=" + primary, "admin_edit","toolbar=no,scrollbars=yes,width="+width+",height="+height+",status=no,menubar=no,directories=no,resizable=yes");
}

function prepareDelete(form, url, width, height)
{
    if (countChecked(form) == 0)
    {
    	alert("Должно быть выбрана хотя бы одно строка!");
    	return;
    }
    if (!confirm("Вы действительно хотите удалить "+countChecked(form)+" строку(и)?"))
    	return;

    var primary = "";
    for (i = 0; i < form.elements.length; i++)
    	if ((form.elements[i].name.substring(0,3) == "row") && form.elements[i].checked)
    	   primary += "&primary[]=" + escape(form.elements[i].value);
    window.open(url + primary, "admin_delete","toolbar=no,scrollbars=yes,width="+width+",height="+height+",status=no,menubar=no,directories=no,resizable=yes");
}

function prepareMulti(form, url, width, height)
{
    if (countChecked(form) == 0)
    {
    	alert("Должно быть выбрана хотя бы одно строка!");
    	return;
    }

    var primary = "";
    for (i = 0; i < form.elements.length; i++)
    	if ((form.elements[i].name.substring(0,3) == "row") && form.elements[i].checked)
    	   primary += "&primary[]=" + escape(form.elements[i].value);
    window.open(url + primary, "admin_multi","toolbar=no,scrollbars=yes,width="+width+",height="+height+",status=no,menubar=no,directories=no,resizable=yes");
}

function confirmLink(url)
{
	if (confirm("Подтвердите действие"))
		document.location = url;
}

function popupUserInfo(user_id)
{
	window.open("/admin/account.phtml?job=user_details&user_id="+user_id, "userinfo", "toolbar=no,scrollbars=yes,width=300,height=500,status=no,menubar=no,directories=no,resizable=yes");
}
