function timestamp_formatter(str,row,index){
	if(str.indexOf(' ')==-1) return str;
	if(str.indexOf('-')==-1) return str;
	var dt=str.split(' ');
	var d=dt[0].split('-');
	return d[2]+'.'+d[1]+'.'+d[0]+' '+dt[1];
}
function datestr_formatter(date,row,index) {		
	if(!date) {
		return '';
	}					
	if(typeof date =='string') {
		if(date.indexOf('-')>-1) {
			var arr=date.split('-');
			return arr[2]+'.'+arr[1]+'.'+arr[0];
		}
		else if(date.indexOf('/')>-1) {
			var arr=date.split('/');
			return arr[1]+'.'+arr[0]+'.'+arr[2];
		}
		return date;
	}
	else {
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		if(d<10) d='0'+d;
		if(m<10) m='0'+m;
		return d+'.'+m+'.'+y;			
	}				
}
function date_to_savedate(str) {
	if((typeof str == "string") && str.indexOf('.')>-1) {
		var arr=str.split('.');
		return arr[2]+'-'+arr[1]+'-'+arr[0];
	}
	return str;
}
function status_filter(status) {
	$('#users').treegrid("options").url = 'loaders/users.php?status='+status;
	$('#users').treegrid('reload');
}
function TreegridDataLoader(treegrid, script, updatedFields) { //догрузка новых данных
	updatedFields = updatedFields || []; //массив полей, которые должны обновляться
	var id_arr = [];
	var self=this;
	this.load=function() {
		$.post(script,null,function(ldata) {
			var loadD = JSON.parse(ldata);
			self.update(loadD); //сначала обновим имеющиеся строки
			var add_data=[];
			for(var i=0;i<loadD.length;i++) {
				if(id_arr.indexOf(loadD[i].id)<0) {
					id_arr.push(loadD[i].id);
					add_data.push(loadD[i]);					
				}
			}
			if(add_data.length) {
				$('#'+treegrid).treegrid('append',{
					parent: null, 
					data: add_data
				});
			}
		});	
	}
	this.update=function(loadedData) {
		if(!updatedFields.length) return;
		var rows = $('#'+treegrid).treegrid('getChildren');
		for(var i=0;i<loadedData.length;i++) {
			var pos = id_arr.indexOf(loadedData[i].id);
			if(pos>-1) {
				for(var j=0;j<updatedFields.length;j++) {
					console.log(updatedFields[j]);
					if(!loadedData[i][updatedFields[j]]) continue;
					if(!rows[pos]) rows[pos]={};
					if(rows[pos][updatedFields[j]]!=loadedData[i][updatedFields[j]]) { //надо обновить
						$('#'+treegrid).treegrid('update',{id:loadedData[i].id,row:loadedData[i]});
						break;
					}
				}
			}
		}
	}
	
}
function JSONTable(table_name,field_list) { //сохраняем список строк в объект
	this.ind=0;
	var self=this;
	self.append=function() {
		$('#'+table_name).treegrid('append',{
		parent: null, 
		data: [{
			id: ++self.ind
		}]
	   });
	   $('#'+table_name).treegrid('select',self.ind);	
	   self.edit();
	}
	self.load=function(rows) {		
		if(!rows || rows=='') rows=[];		
		if(typeof rows == 'string')	rows=JSON.parse(rows);		
		$('#'+table_name).treegrid('loadData',rows);
		if(rows.length) self.ind = rows[rows.length-1].id;
		else self.ind =0;
	}
	self.getRows=function() {
		return $('#'+table_name).treegrid('getChildren');		
	}
	self.getWriteObj=function() {
		return JSON.stringify(self.getRows());
	}
	self.writeField=function(grid_id,field){
		var row=$('#'+grid_id).treegrid('getSelected');
		if(row) {
			row[field]=self.getWriteObj();
			self.parentTabObj.recordToDB(row);
		}				
	}
	self.edit=function()
	{
		self.save();
		var row=$('#'+table_name).treegrid('getSelected');		
		if(row)	
		{
			$('#'+table_name).treegrid('beginEdit', row.id);		
			self.editingId=row.id;			
		}
	}
	self.save=function()
	{			
		if(self.editingId) 
		{		
			$('#'+table_name).treegrid('endEdit', self.editingId);
			self.writeField(self.basegrid_id,self.basegrid_field);
		}		
	}
	self.enter=function(e) {
		if(e.keyCode == 13) self.save();
	}
	self.remove=function() {
		var row=$('#'+table_name).treegrid('getSelected');		
		console.log(row);
		if(row)	{
			self.save();			
			$('#'+table_name).treegrid('remove', row.id);
			self.writeField(self.basegrid_id,self.basegrid_field);
		}
	}
	
}
function CatalogTable(table_name,sql_table,field_list)
{
	var self=this;
	self.ind=0;
	self.editingId=false;
	self.table_name=table_name;	
	self.sql_table=sql_table;
	self.field_list=field_list;
	
	self.append=function(load_script,params)
	{
		load_script=load_script || "savers/get_id.php";
		params=params || {};		
		$.post(load_script,{table_name:self.sql_table},
		function(data)
		{
			self.ind=data;			
			for(var k in params) {
				if( typeof params[k] == 'string') params[k]=params[k].split('#ind').join(self.ind);
			}
			params.id=self.ind;
			$('#'+self.table_name).treegrid('append',{
			parent: null, 
			data: [params]
		   });
		   $('#'+self.table_name).treegrid('select',self.ind);	
		   self.edit();
		});		
	}
	self.edit=function()
	{
		self.save();
		var row=$('#'+self.table_name).treegrid('getSelected');		
		if(row)	
		{
			$('#'+self.table_name).treegrid('beginEdit', row.id);		
			self.editingId=row.id;			
		}
	}
	self.enter=function(e,script) {
		if(e.keyCode == 13) self.save(script);
	}	
	self.recordToDB=function(row,save_script) {
		save_script=save_script || 'savers/update_record.php';		
		var values={};
		for(var i=0;i<self.field_list.length;i++) values[self.field_list[i]]=row[self.field_list[i]];
		//обработка исключений		
		if(values['passwd']=="") delete values['passwd'];
		else if(values['passwd']) values['passwd']=$.md5($.md5(values['passwd']));
		if('freeles_date' in values) values.freeles_date=date_to_savedate(values.freeles_date);
		if(values.status=='Получена оплата') values.ispay=1;
		//====================
		var send_object={table_name:sql_table,id:row.id,values:JSON.stringify(values)};				
		$.post(save_script,send_object,function(d) {console.log(d)});		
		return send_object;	
	}
	self.save=function(save_script)
	{	
		save_script=save_script || 'savers/update_record.php';
		console.log('save');	
		if(self.editingId) 
		{			
			console.log('save2');
			$('#'+self.table_name).treegrid('endEdit', self.editingId);
			var row=$('#'+self.table_name).treegrid('find',self.editingId);
			self.editingId=false;				
			return self.recordToDB(row,save_script);				
		}
		return null;
	}
	self.remove=function()
	{
		var row=$('#'+self.table_name).treegrid('getSelected');		
		if(row)	
		{
			self.save();
			$.post("savers/remove_record.php",{table_name:self.sql_table,id:row.id});
			$('#'+self.table_name).treegrid('remove', row.id);								
		}
	}
}


function UserTable(table_name,sql_table,field_list) {
	CatalogTable.apply(this, arguments);
	var self=this;
	self.save_users=function() {
		if(self.editingId) {			
			$.post("savers/samplework.php",self.save(),function(d) {console.log(d);});
		}
	}
	self.enter=function(e) {
		if(e.keyCode == 13) self.save_users();
	}	
}
function WidthCatalogTable(table_name,sql_table,field_list,elements,elem_fields) {
	CatalogTable.apply(this, arguments);
	var self=this;
	self.clearElements=function() {		
		for(var i=0;i<elements.length;i++) {
			$('#'+elements[i]).val('');
		}
	}
	self.initElements=function(row) {
		if(!row) return;
		for(var i=0;i<elements.length;i++) {
			$('#'+elements[i]).val(row[elem_fields[i]]);
		}
	}
	self.saveElements=function(row) {
		row=row || $('#'+self.table_name).treegrid('getSelected');
		if(!row) return;
		for(var i=0;i<elements.length;i++) {
			row[elem_fields[i]]=$('#'+elements[i]).val();			
		}
		self.recordToDB(row);
	}	
}



function TeacherTable(table_name,sql_table,field_list,sql_table2,field_list2,field_id2,dialog) {
	CatalogTable.apply(this, arguments);
	this.sql_table2=sql_table2;
	this.field_list2=field_list2;
	this.field_id2=field_id2;
	var self=this;
	self.dialogid=dialog;
	self.dialog_edit=function() {
		//читаем строку
		let row=$('#'+self.table_name).treegrid('getSelected');
		if(row)	
		{			
			self.editingId=row.id;
			var formcont="";
			for(let k in row) {
				/*try {
					 $('#'+k).textbox('setValue',row[k]);
				 }
				 catch(err) {}
				 */
				formcont+="<input type=hidden name='"+k+"' id='frm_"+k+"' >";
			}
			$('#cardformcontent').html(formcont);
			for(let k in row) {
				$('#frm_'+k).val(row[k]);				
			}
			$('#cardform').submit();
			//$('#status').combobox('setValue',row['status']);
			//$('#grafic_id').combobox('setValue',row['grafic_id']);
		//	$('#'+self.dialogid).dialog('open');
		}		
	}
	self.dialog_save=function(data) {
		save_script=save_script || 'savers/update_record.php';		
		if(self.editingId) 
		{			
			var data=$('#'+self.table_name).treegrid('find',self.editingId);					
			for(var k in data) {				
				if(k.indexOf('date')>-1) {
					if(data[k]=='') data[k]='null';
					else data[k]=date_to_savedate(data[k]);			
				}
				if(k.indexOf('time')>-1 && data[k]=='') data[k]='null';
			}
						
			var values={};
			for(var i=0;i<self.field_list.length;i++) values[self.field_list[i]]=data[self.field_list[i]];
			var send_object={table_name:self.sql_table,id:self.editingId,values:JSON.stringify(values)};
			console.log(save_script);
			$.post(save_script,send_object,function(d) {console.log(d)});
			
			//2-я таблица		
			var vals2={};
			for(var i=0;i<self.field_list2.length;i++) vals2[self.field_list2[i]]=data[self.field_list2[i]];
			var send_object2={table_name:self.sql_table2,id:data[self.field_id2],values:JSON.stringify(vals2)};
			$.post(save_script,send_object2,function(d) {console.log(d)});
			self.editingId=false;		
			return send_object;			
		}
		return null;
	}
	self.save=function(save_script) {
		save_script=save_script || 'savers/update_record.php';
		console.log('save');	
		if(self.editingId) 
		{			
			console.log('save2');
			$('#'+self.table_name).treegrid('endEdit', self.editingId);
			var row=$('#'+self.table_name).treegrid('find',self.editingId);		
			
			for(var k in row) {				
				if(k.indexOf('date')>-1) {
					if(row[k]=='') row[k]='null';
					else row[k]=date_to_savedate(row[k]);			
				}
				if(k.indexOf('time')>-1 && row[k]=='') row[k]='null';
			}
						
			var values={};
			for(var i=0;i<self.field_list.length;i++) values[self.field_list[i]]=row[self.field_list[i]];
			var send_object={table_name:self.sql_table,id:self.editingId,values:JSON.stringify(values)};
			console.log(save_script);
			$.post(save_script,send_object,function(d) {console.log(d)});
			
			//2-я таблица		
			var vals2={};
			for(var i=0;i<self.field_list2.length;i++) vals2[self.field_list2[i]]=row[self.field_list2[i]];
			var send_object2={table_name:self.sql_table2,id:row[self.field_id2],values:JSON.stringify(vals2)};
			$.post(save_script,send_object2,function(d) {console.log(d)});
			self.editingId=false;		
			return send_object;			
		}
		return null;
	}
}
function click_grafic_cell(elem,hour, day) {
	if(!(hour in app.grafic.cells)) app.grafic.cells[hour]={};
	if(app.grafic.cells[hour][day]) {
		app.grafic.cells[hour][day]=0;
		elem.innerHTML='';
	}
	else {
		app.grafic.cells[hour][day]=1;
		elem.innerHTML='<div class="item">P</div>';
	}
}
function click_shedule_cell(elem, hour, day) {
	//сначала запретим клик по ячейке с "H"
	if($('[hour='+hour+'][day='+day+']').hasClass('trash')) {
		$.messager.alert("","! Вы пытаетесь назначить урок на нерабочее время");
		return;
	}
	//определим выбранный момент времени
	var weekstart = app.shedule.mom1;
	var sel_date = new Date(weekstart.getTime());
	sdt=weekstart.getDate()+day-1;
	sel_date.setDate(sdt);
	sel_date.setHours(hour);
	app.shedule.sel_moment = sel_date.getTime()/1000;
	app.shedule.sel_hour_day={hour:hour, day:day};	
	var row=$('#tg_teachers').treegrid('getSelected');
	//для занятой ячейки откроем форму для редактирования
	if($('[hour='+hour+'][day='+day+']').html()=='<div class="item wh">З</div>') {
		set_shed_dlg_data(row.id,
			app.shedule.cells[hour][day].subject_id,
			app.shedule.cells[hour][day].user_id);
	}	
	//для пустой ячейки откроем форму для создания	
	else set_shed_dlg_data(row.id);				
	$('#shed_dlg').dialog('open'); 
}

function playvoice(file,btn) {
	btn.innerHTML = "Воспроизводится";
	console.log(btn);
	btn.disabled=true;
	var audio = document.createElement('video');		
	//var blob = new Blob([self.blobdata], { 'type' : 'video/webm; codecs=opus' });
	//var audioURL = window.URL.createObjectURL(blob);
	audio.src = file;
	audio.autoplay = true;
	audio.onended = function() {
		btn.innerHTML = "Слушать";
		btn.disabled=false;
		audio.remove();
	}
}

var app={};
app.tasks = new CatalogTable('tg_tasks','useranswerlenta',['mark','status']);
app.images = new CatalogTable('tg_images','images',['name','src']);
app.users={};

app.users.actions=new CatalogTable('users','users',['notific','parent_name','phone','name','child_age','uclass','status']);
app.clients={};
app.clients.actions=new CatalogTable('users','users',['login','email','phone','skype','uclass','passwd','name','parent_name','child_age','notific','status']);

app.teachers={};
app.teachers.actions=new TeacherTable('tg_teachers','teachers',['status','intrdate','intrtime'],'users',['notific','name','fathername',
'lastname','phone','skype','login','region'],'user_id','teachered_dlg');

app.videolessons={};
app.videolessons.actions=new CatalogTable('tg_videolessons','videolessons',['name','src','tasks','maxClass','ordNumber']);
app.videotasks={};
app.videotasks.actions=new JSONTable('tg_tasks',['time_on_sec','task','question','word','answer']);
app.videotasks.actions.basegrid_id='tg_videolessons';
app.videotasks.actions.basegrid_field='tasks';
app.videotasks.actions.parentTabObj=app.videolessons.actions;

app.speedread={};
app.speedread.actions=new WidthCatalogTable('tg_sprlist','speedReadTests',['title','text','symbols','words','uclass','ordNumber'],['sptext'],['text']);

app.grafic={};
app.shedule={};
app.grafic.actions=new CatalogTable('tg_grafics','grafics',['name','content']);
app.grafic.cells={};
app.shedule.setLink=function() {	//задать ссылку
	var mom1=Math.floor(app.shedule.mom1.getTime()/1000);
	var mom2=Math.floor(app.shedule.mom2.getTime()/1000);	
	$('#tg_teachers').treegrid("unselectAll");
	$('#tg_teachers').treegrid("options").url = 'loaders/teachers_shedule.php?mom1='+mom1+"&mom2="+mom2;
	$('#tg_teachers').treegrid("reload");	
	console.log('loaders/teachers_shedule.php?mom1='+mom1+"&mom2="+mom2);
}
app.shedule.load=function() {
	app.grafic.clear('<div class="item trash">H</div>');
	var row=$('#tg_teachers').treegrid('getSelected');
	if(!row) return;	
	//грузнем график, заполнив рабочие ячейки пустотой
	if(row.grafic)	app.grafic.cells=JSON.parse(row.grafic);
	else app.grafic.cells={};
	for(var hour in app.grafic.cells) {
		for(var day in app.grafic.cells[hour]) {
			if(app.grafic.cells[hour][day]) $('[hour='+hour+'][day='+day+']').html('');
		}
	}
	//грузнем расписание
	app.shedule.cells={};
	if(row.shedule) {
		app.shedule.cells=JSON.parse(row.shedule);
	}
	console.log(app.shedule.cells);
	for(var hour in app.shedule.cells) {
		if(hour=="") continue;
		for(var day in app.shedule.cells[hour]) {
			if(day=="") continue;
			if(app.shedule.cells[hour][day]) $('[hour='+hour+'][day='+day+']').html('<div class="item wh">З</div>');
		}
	}
}
app.grafic.apply=function() {
	var row=$('#tg_grafics').treegrid('getSelected');		
	if(row)	
	{
		row.content=JSON.stringify(app.grafic.cells);
		//$('#tg_grafics').treegrid('beginEdit', row.id);		
		app.grafic.actions.editingId=row.id;			
		app.grafic.actions.save();
		$.messager.show({
	title:'Уведомление',
	msg:'График сохранен',
	timeout:2000,
	showType:'slide'
});
	}
}
app.grafic.apply2=function() {
	var content=JSON.stringify(app.grafic.cells);
	let values={content:content};
	if(app.grafic_id==0) {
		$.post("savers/get_id.php",{table_name:'grafics'},function(g_id) {
			app.grafic_id=g_id;	
			console.log(app.grafic_id);
			$('#grafic_id').val(g_id);
			$.post('savers/update_record.php',{values:JSON.stringify(values), id:g_id,table_name:'grafics'},function(d) {console.log(d);});
		});
	}
	else $.post('savers/update_record.php',{values:JSON.stringify(values), id:app.grafic_id,table_name:'grafics'},function(d) {console.log(d);});
	
	
	$.messager.show({
		title:'Уведомление',
		msg:'График сохранен',
		timeout:2000,
		showType:'slide'
	});	
}
app.grafic.clear=function(space) {
	space=space || "";
	for(var hour=7;hour<=21;hour++)
		for(var day=1;day<=7;day++)
			$('[hour='+hour+'][day='+day+']').html(space);
}
app.grafic.load=function(row) {
	if(row.content)	app.grafic.cells=JSON.parse(row.content);
	else app.grafic.cells={};
	app.grafic.clear();
	for(var hour in app.grafic.cells) {
		for(var day in app.grafic.cells[hour]) {
			if(app.grafic.cells[hour][day]) $('[hour='+hour+'][day='+day+']').html('<div class="item">P</div>');
		}
	}
}

app.grafic.load2=function() {	
	$.post("loaders/grafics.php",{id:app.grafic_id},function(d) {
		let rows=JSON.parse(d);
		console.log(rows);
		
		app.grafic.cells=JSON.parse(rows[0].content)
		for(var hour in app.grafic.cells) {
			for(var day in app.grafic.cells[hour]) {
				if(app.grafic.cells[hour][day]) $('[hour='+hour+'][day='+day+']').html('<div class="item">P</div>');
			}
		}
	});
}


function getWeekStart(tdate) { //возвращает начало недели понедельник для произвольной даты
	var curr = tdate || (new Date); // get current date
	var first = curr.getDate() - curr.getDay()+1; 
	if(curr.getDay()==0) first-=7;
	return new Date(curr.setDate(first));
}
function getWeekEnd(tdate) {
	var curr = tdate || (new Date); // get current date
	var first = curr.getDate() - curr.getDay()+1; 
	if(curr.getDay()==0) first-=7;
	var last=first+6;
	return new Date(curr.setDate(last));
}
 date_formatter= function(date){
	var y = date.getFullYear();
	var m = date.getMonth()+1;
	var d = date.getDate();
	m= m<10 ? '0'+m : m;
	d= d<10 ? '0'+d : d;
	return d+'.'+m+'.'+y;
}
date_formatter2= function(date){
	var y = date.getFullYear();
	var m = date.getMonth()+1;
	var d = date.getDate();
	m= m<10 ? '0'+m : m;
	d= d<10 ? '0'+d : d;
	return y+'-'+m+'-'+d;
}
$.fn.datebox.defaults.formatter=date_formatter;

date_parser=function(s) {
	if (typeof s != 'string') return new Date();
	if(s.indexOf('-')>-1) {//yyyy-mm-dd
		var arr=s.split('-');
		return new Date(arr[0], arr[1]-1, arr[2]);
	}
	else if(s.indexOf('.')>-1) {
		var arr=s.split('.');
		return new Date(arr[2], arr[1]-1, arr[0]);
	}
	return new Date();
}
function get_shed_dlg_data() {
	var data={};
	data.teacher_id=$('#shed_teacher').combobox('getValue');
	data.user_id=$('#shed_client').combobox('getValue');
	data.subject_id=$('#shed_subject').combobox('getValue');
	
	//дополнительно ставим компоненты даты из глобальных переменных
	data.moment=app.shedule.sel_moment;
	data.hour_day=JSON.stringify(app.shedule.sel_hour_day);		
	return data;
}
function set_shed_dlg_data(teacher_id,subject_id,user_id) {
	console.log(teacher_id,subject_id,user_id);
	$('#shed_teacher').combobox('setValue',teacher_id);
	if(subject_id) $('#shed_subject').combobox('setValue',subject_id);
	else $('#shed_subject').combobox('clear');	
	if(user_id)	$('#shed_client').combobox('setValue',user_id);
	else $('#shed_client').combobox('clear');	
}
function imgload(files,new_filename) { //загрузка фотки на сервер
	$('#recrow').show();
	// ничего не делаем если files пустой
	if( typeof files == 'undefined' ) return;

	// создадим объект данных формы
	var data = new FormData();

	// заполняем объект данных файлами в подходящем для отправки форм
	$.each( files, function( key, value ){
		data.append( key, value );
	});

	// добавим переменную для идентификации запроса
	data.append( 'my_file_upload', 1 );
	data.append( 'setfilename', new_filename );
	
	// AJAX запрос
	$.ajax({
		url         : 'savers/fileloader.php',
		type        : 'POST', // важно!
		data        : data,
		cache       : false,
		dataType    : 'json',
		// отключаем обработку передаваемых данных, пусть передаются как есть
		processData : false,
		// отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
		contentType : false, 
		// функция успешного ответа сервера
		success     : function( respond, status, jqXHR ){
			console.log(respond);
			// ОК - файлы загружены
			if( typeof respond.error === 'undefined' ){
				// выведем пути загруженных файлов в блок '.ajax-reply'
				var files_path = respond.files;
				var html = '';
				$.each( files_path, function( key, val ){
					 html += val;
				} )
				console.log(html);
				$('#selimg').attr('src','https://umikum.ru/taskimg/'+html);
				$('#selimg').css('width','300px');
				var row=$('#tg_images').treegrid('getSelected');
				if(row) {
					$('#tg_images').treegrid('update',{id:row.id,row:{src:html}});
				}
			}
			// ошибка
			else {
				console.log('ОШИБКА: ' + respond.error );
			}
		},
		// функция ошибки ответа сервера
		error: function( jqXHR, status, errorThrown ){
			console.log( 'ОШИБКА AJAX запроса: ' + status, jqXHR );
		}

	});
}
function shed_dlg_save() { //сохранение дня расписания
	var dlg_data=get_shed_dlg_data();
	$.post("savers/shed_save.php",dlg_data,function(d) {$.messager.show({
			title:'Уведомление',
			msg:d,
			timeout:2000,
			showType:'slide'
		});
		$('#shed_dlg').dialog('close');
		var hour_day=JSON.parse(dlg_data.hour_day);
		$('[hour='+hour_day.hour+'][day='+hour_day.day+']').html('<div class="item wh">З</div>');
		//дописать в активную строку
		var row=$('#tg_teachers').treegrid('getSelected');
		if(!row) return;
		if(!(app.shedule.cells[hour_day.hour])) app.shedule.cells[hour_day.hour]={};
		app.shedule.cells[hour_day.hour][hour_day.day]=dlg_data;
		row.shedule=JSON.stringify(app.shedule.cells);
	});
}
$(document).ready(function() {
		$('#idspass').on('click',function() {
			$.messager.prompt('Ввод пароля','Введите новый пароль',function(r){
				if (r){
					$.post("savers/setpass.php",{uid:app.teacheruserid,psw:r},function(d){alert(d);});
				}
			});
		});
		$('#idslogin').on('click',function() {
			$.messager.prompt('Ввод логина','Введите новый логин',function(r){
				if (r){
					$.post("savers/update_record.php",{id:app.teacheruserid,table_name:'users',values:JSON.stringify({login:r})},function(d){alert("Логин успешно обновлен");});
				}
			});
		});
	}
);
$.fn.datebox.defaults.parser = date_parser;
$.fn.calendar.defaults.firstDay = 1;
