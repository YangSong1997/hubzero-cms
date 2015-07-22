/*
		GNU General Public License version 2 or later; see LICENSE.txt
*/
if (typeof(Joomla) === 'undefined'){var Joomla = {};};Joomla.editors={};Joomla.editors.instances={};Joomla.submitform=function(task,form){if(typeof(form)==='undefined'){form=document.getElementById('adminForm');if(!form){form=document.adminForm}}else{if(form instanceof jQuery){form=form[0]}}if(typeof(task)!=='undefined'&&''!==task){form.task.value=task}var event;if(document.createEvent){event = document.createEvent('HTMLEvents');event.initEvent('submit', true, true);}else if(document.createEventObject){event = document.createEventObject();event.eventType = 'submit';}if(typeof form.onsubmit=='function'){form.onsubmit()}else if(typeof form.dispatchEvent=="function"){form.dispatchEvent(event);}else if(typeof form.fireEvent=="function"){form.fireEvent('submit')}form.submit()};Joomla.submitbutton=function(pressbutton){Joomla.submitform(pressbutton);};Joomla.JText={strings:{},'_':function(key,def){return typeof this.strings[key.toUpperCase()]!=='undefined'?this.strings[key.toUpperCase()]:def},load:function(object){for(var key in object){this.strings[key.toUpperCase()]=object[key]}return this;}};Joomla.replaceTokens=function(n){var els=document.getElementsByTagName('input');for(var i=0;i<els.length;i++){if((els[i].type=='hidden')&&(els[i].name.length==32)&&els[i].value=='1'){els[i].name=n}}};Joomla.isEmail=function(text){var regex=new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");return regex.test(text)};Joomla.checkAll=function(checkbox,stub){if(!stub){stub='cb'}if(checkbox.form){var c=0;for(var i=0,n=checkbox.form.elements.length;i<n;i++){var e=checkbox.form.elements[i];if(e.type==checkbox.type){if((stub&&e.id.indexOf(stub)==0)||!stub){e.checked=checkbox.checked;c+=(e.checked==true?1:0)}}}if(checkbox.form.boxchecked){checkbox.form.boxchecked.value=c}return true}return false};Joomla.renderMessages=function(messages){Joomla.removeMessages();var container=$('#system-message-container');var dl=$('<dl>').attr('id','system-message').attr('role','alert');$.each(messages,function(type,item){var dt=$('<dt>').addClass(type).html(type).appendTo(dl);var dd=$('<dd>').addClass(type).addClass('message');var list=$('<ul>');$.each(item,function(index,item,object){var li=$('<li>').html(item).appendTo(list)});list.appendTo(dd);dd.appendTo(dl)});dl.appendTo(container)};Joomla.removeMessages=function(){var children=$('#system-message-container > *');children.remove()};Joomla.isChecked=function(isitchecked,form){if(typeof(form)==='undefined'){form=document.getElementById('adminForm');if(!form){form=document.adminForm}}if(isitchecked==true){form.boxchecked.value++}else{form.boxchecked.value--}};Joomla.popupWindow=function(mypage,myname,w,h,scroll){var winl=(screen.width-w)/2;var wint=(screen.height-h)/2;var winprops='height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable';var win=window.open(mypage,myname,winprops);win.window.focus()};Joomla.tableOrdering=function(order,dir,task,form){if(typeof(form)==='undefined'){form=document.getElementById('adminForm');if(!form){form=document.adminForm;}};form.filter_order.value=order;form.filter_order_Dir.value=dir;Joomla.submitform(task,form);};function writeDynaList(selectParams,source,key,orig_key,orig_val){var html='\n	<select '+selectParams+'>';var i=0;for(x in source){if(source[x][0]==key){var selected='';if((orig_key==key&&orig_val==source[x][1])||(i==0&&orig_key!=key)){selected='selected="selected"'}html+='\n		<option value="'+source[x][1]+'" '+selected+'>'+source[x][2]+'</option>'}i++}html+='\n	</select>';document.writeln(html);};function changeDynaList(listname,source,key,orig_key,orig_val){var list=document.adminForm[listname];for(i in list.options.length){list.options[i]=null}i=0;for(x in source){if(source[x][0]==key){opt=new Option();opt.value=source[x][1];opt.text=source[x][2];if((orig_key==key&&orig_val==opt.value)||i==0){opt.selected=true}list.options[i++]=opt}}list.length=i;};function radioGetCheckedValue(radioObj){if(!radioObj){return''}var n=radioObj.length;if(n==undefined){if(radioObj.checked){return radioObj.value;}else{return'';}}for(var i=0;i<n;i++){if(radioObj[i].checked){return radioObj[i].value;}}return'';};function getSelectedValue(frmName,srcListName){var form=document[frmName];var srcList=form[srcListName];i=srcList.selectedIndex;if(i!=null&&i>-1){return srcList.options[i].value}else{return null}};function checkAll(checkbox,stub){if(!stub){stub='cb'}if(checkbox.form){var c=0;for(var i=0,n=checkbox.form.elements.length;i<n;i++){var e=checkbox.form.elements[i];if(e.type==checkbox.type){if((stub&&e.id.indexOf(stub)==0)||!stub){e.checked=checkbox.checked;c+=(e.checked==true?1:0)}}}if(checkbox.form.boxchecked){checkbox.form.boxchecked.value=c}return true}else{var f=document.adminForm;var c=f.toggle.checked;var n=checkbox;var n2=0;for(var i=0;i<n;i++){var cb=f[stub+''+i];if(cb){cb.checked=c;n2++}}if(c){document.adminForm.boxchecked.value=n2}else{document.adminForm.boxchecked.value=0}}};function listItemTask(id,task){var f=document.adminForm;var cb=f[id];if(cb){for(var i=0;true;i++){var cbx=f['cb'+i];if(!cbx)break;cbx.checked=false}cb.checked=true;f.boxchecked.value=1;submitbutton(task)}return false};function isChecked(isitchecked){if(isitchecked==true){document.adminForm.boxchecked.value++}else{document.adminForm.boxchecked.value--}};function submitbutton(pressbutton){submitform(pressbutton)};function submitform(pressbutton){var event;if(document.createEvent){event = document.createEvent('HTMLEvents');event.initEvent('submit', true, true);}else if(document.createEventObject){event = document.createEventObject();event.eventType = 'submit';}if(pressbutton){document.adminForm.task.value=pressbutton}if(typeof document.adminForm.onsubmit=="function"){document.adminForm.onsubmit()}else if(typeof document.adminForm.dispatchEvent=="function"){document.adminForm.dispatchEvent(event);}else if(typeof document.adminForm.fireEvent=="function"){document.adminForm.fireEvent('submit')}document.adminForm.submit()};function popupWindow(mypage,myname,w,h,scroll){var winl=(screen.width-w)/2;var wint=(screen.height-h)/2;winprops='height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable';win=window.open(mypage,myname,winprops);if(parseInt(navigator.appVersion)>=4){win.window.focus()}};function tableOrdering(order,dir,task){var form=document.adminForm;form.filter_order.value=order;form.filter_order_Dir.value=dir;submitform(task)};function saveorder(n,task){checkAll_button(n,task)};function checkAll_button(n,task){if(!task){task='saveorder'}for(var j=0;j<=n;j++){var box=document.adminForm['cb'+j];if(box){if(box.checked==false){box.checked=true}}else{alert("You cannot change the order of items, as an item in the list is `Checked Out`");return}}submitform(task)};