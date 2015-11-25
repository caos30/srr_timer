var interval_grabar=60000;

var a_chronos = new Array();
var n_chronos = 0;
var time_ini = 0;
var chime_ini = 0;
var chime_unit = 1;
var chime_period = 1;
var chime_next = 0;
var itask=0;
var id_project="";
var id_chrono;
var id_chime;
var idata;
var pageX , pageY; 
var op='';
var current_date='2013-05-08';
var default_lang='en';
var temp_lang='';

var settings = [];
var texts = [];

$(document).ready(function(){
        js_json_get_settings('1');
});

function js_set_initial_binds(){
    /* == prepare language == */
        if (typeof(settings['lang'])==='undefined') settings['lang'] = default_lang;
        js_json_load_texts();
    /* == capturing mouse position == */
 	$(document).mousemove(function(e){
 		pageX = e.pageX;
 		pageY = e.pageY;
 	});
    /* == bind some events == */
 	$('#chime_start').bind('click',function(){
		$('#chime_start').hide();
		$('#chime_stop').show();
		chime_unit = parseInt($('#chime_unit').val() , 10);
		chime_period = parseInt($('#chime_period').val() , 10);
		var dat = new Date();
		chime_ini = parseInt(dat.getTime()/1000 , 10); 
		chime_next = chime_ini + 60 * chime_unit * chime_period;
		id_chime = setInterval('js_update_chime()',1000);
		js_play_audio('lib/images/bleep.mp3');
	});
 	$('#chime_stop').bind('click',function(){
		$('#chime_stop').hide();
		$('#chime_start').show();
		$('#chime_next').html('');
		clearInterval(id_chime);
		js_play_audio('lib/images/beep.mp3');
	});
 	$('#b_active').bind('click',function(){
                if ($(this).attr('rel')=='1'){
                    $(this).find('i').removeClass('fa-check-square-o').addClass('fa-square-o');
                    $(this).attr('rel','0');
                }else{
                    $(this).find('i').removeClass('fa-square-o').addClass('fa-check-square-o');
                    $(this).attr('rel','1');
                }
                return false;
	});
 	$('#div_languages a').bind('click',function(){
                if ($(this).attr('rel')==temp_lang) return;
                /* == reassign CSS class == */
                    var new_lang = $(this).attr('rel');
                    $('#div_languages a').removeClass('current');
                    $('#div_languages a[rel='+new_lang+']').addClass('current');
                /* == set the new language to the temp_lang== */
                    temp_lang = new_lang;
                return false;
	});
 	$('#tb_projects').delegate('a','click',function(){
                var id_project = $(this).parent().parent().attr('rel');
                var action = $(this).attr('rel');
                switch(action){
                    case 'delete':  js_delete_button(id_project);       break;
                    case 'edit':    js_edit_button(id_project);         break;
                    case 'stats':   js_stats_button(id_project);        break;
                    case 'merge':   js_merge_button(id_project);        break;
                    case 'start':   js_start(id_project);               break;
                    case 'stop':    js_stop(id_project); js_save();     break;
                        
                    case 'increment-30':    js_increment(id_project,-30); js_save();   break;
                    case 'increment-10':    js_increment(id_project,-10); js_save();   break;
                    case 'increment-5':     js_increment(id_project,-5);  js_save();   break;
                    case 'increment-1':     js_increment(id_project,-1);  js_save();   break;
                    
                    case 'reset':     js_reset(id_project);  js_save();   break;

                    case 'increment+30':    js_increment(id_project,+30); js_save();   break;
                    case 'increment+10':    js_increment(id_project,+10); js_save();   break;
                    case 'increment+5':     js_increment(id_project,+5);  js_save();   break;
                    case 'increment+1':     js_increment(id_project,+1);  js_save();   break;
                    
                }
                return false;
	});
        
 	$('#div_settings').delegate('a.bt','click',function(){ js_save_settings();$('#div_settings').hide();return false; });
 	$('#div_edit').delegate('a.bt','click',function(){ js_save_div_edit();return false; });
 	$('#div_add').delegate('a.bt','click',function(){ js_save_div_add();return false; });
        $('#div_merge').delegate('a.bt','click',function(){ js_save_div_merge();return false; });
        $('#div_stats').delegate('a.bt','click',function(){ js_stats_update($(this).attr('rel'));return false; });
        $('#div_delete').delegate('a.bt[rel=cancel]','click',function(){ $('#div_delete').hide();return false; });
        $('#div_delete').delegate('a.bt[rel=delete]','click',function(){ js_save_div_delete();return false; });
        
        $('.modal').delegate('.close','click',function(){ $(this).parent().parent().hide();return false; });
        $('.show_control').delegate('a','click',function(){ 
            var show = $(this).attr('rel');
            $('.show_control').attr('rel',show);
            js_update_show_control();
            return false; 
        });
            
}

function js_play_audio(file){
	var audioElement = document.createElement('audio');
	audioElement.setAttribute('src', file);
	audioElement.setAttribute('autoplay', 'autoplay');
	audioElement.play();
	return;
}
	
function js_json_get_settings(first_load){
	var url_json = 'lib/json/json.php?op=get_settings';
	$.getJSON(
            url_json,
            function(data){ 
                settings = data.settings;
                $('#app_version').html(settings.app_version);
                /* == add languages flags to settings modal dialog == */
                    $(settings.languages).each(function(k,v){
                        if (v == settings['lang']) 
                            var current = "class='current'";
                        else
                            var current = "";
                        $('#div_languages').append("<a href='#' "+current+" rel='"+v+"'><img src='lib/images/languages/"+v+".png' title='"+v+"' /></a>");
                        
                    });
                if (first_load=='1') js_json_get_projects(first_load);
            }
	);	
}

function js_render_texts(){
    /* fill SPAN, DIV, P ... etc */
        $('.TEXT').each(function(){
            var rel = $(this).attr('rel');
            $(this).html(texts[rel]);
        });
        
    /* INPUT placeholder/valur */
        $('.TEXTv').each(function(){
            var rel = $(this).attr('rel');
            $(this).val(texts[rel]);
        });
}

function js_json_load_texts(){
	var url_json = 'lib/json/json.php?op=get_language_texts&lang='+settings['lang'];
	$.getJSON(
            url_json,
            function(data){ 
                texts = data.texts;
                js_render_texts();
            }
	);	
}

function js_json_get_projects(first_load){
        /* == use JSON for get the projects == */
	var url_json = 'lib/json/json.php?op=get_times_today&lang='+settings['lang'];
	$.getJSON(
		url_json,
		function(data){ 
			var html = "";
			var i=0;
			$.each(data.a_projects, function(k, val) {
				i++;	
				a_chronos[i] = new Array();	
				$.each(val,function(k2,val2){
					a_chronos[i][k2] = val2;  
				});
                                if (!a_chronos[i]['n_today_time'] || isNaN(a_chronos[i]['n_today_time'])) a_chronos[i]['n_today_time'] = 0;
                                if (!a_chronos[i]['n_total_time'] || isNaN(a_chronos[i]['n_total_time'])) a_chronos[i]['n_total_time'] = 0;
                                if (!a_chronos[i]['n_times'] || isNaN(a_chronos[i]['n_times'])) a_chronos[i]['n_times'] = 0;
				a_chronos[i]['today'] = parseInt(a_chronos[i]['n_today_time']); 
				a_chronos[i]['all'] = parseInt(a_chronos[i]['n_total_time']);
				a_chronos[i]['n_times'] = parseInt(a_chronos[i]['n_times']);
			});
                        n_chronos = i; /* counter of number of chronos */
			$('#current_date').html(data.current_date);
                        //eval(data.script);
                        var it_temp = itask;
                        js_set_initial_binds();
                        js_render_projects_table();
                        js_update_times();
                        if (first_load=='1') js_save();
		}
	);		
}
function js_render_projects_table(){
	var html = "";
	$("#tb_projects > tbody").html('');
        var visible, n_times;
	if (n_chronos > 0){
	for (i2 in a_chronos){
                visible = '1';
                /* == prepare data == */
                    if (   (a_chronos[i2]['b_active']=='1' && settings['show']=='unactive')
                        || (a_chronos[i2]['b_active']!='1' && settings['show']=='active') )
                                visible = '0';
                    if (isNaN(a_chronos[i2]['n_times']) || a_chronos[i2]['n_times']=='') n_times = '0'; else n_times = parseInt(a_chronos[i2]['n_times']);
                /* == render == */
                    html = "<tr rel='"+i2+"' class='visible_"+visible+" active_"+a_chronos[i2]['b_active']+"'>"
                                + "<td><span id='tit_"+i2+"'><span></span><label>"+a_chronos[i2]['title']+"</label></span></td>"
				+ "<td class='n_w a_r'>"
				+ "<a href='#' rel='stats' class='c_r1'><i class='fa fa-area-chart'></i></a>"
				+ "<a href='#' rel='edit' class='c_r1'><i class='fa fa-edit'></i></a>"
				+ "<a href='#' rel='delete' class='c_r1'><i class='fa fa-trash'></i></a>"
				+ "<a href='#' rel='merge' class='c_r1'><i class='fa fa-code-fork'></i></a>"
				+ "<a href='#' rel='start' class='c_r2'><i class='fa fa-play'></i></a>"
				+ "<a href='#' rel='stop' class='' style='display:none;'><i class='fa fa-stop'></i></a>" 
				+ "<a href='#' rel='increment-30'><b>-30</b></a>"
				+ "<a href='#' rel='increment-10'><b>-10</b></a>"
				+ "<a href='#' rel='increment-5'><b>-5</b></a>"
				+ "<a href='#' rel='increment-1'><b>-1</b></a>"
				+ "<a href='#' rel='reset'><b>0</b></a>"
				+ "<a href='#' rel='increment+1'><b>+1</b></a>"
				+ "<a href='#' rel='increment+5'><b>+5</b></a>"
				+ "<a href='#' rel='increment+10'><b>+10</b></a>"
				+ "<a href='#' rel='increment+30'><b>+30</b></a></td>"
				+ "<td><span class='time' id='v_today_"+i2+"'>" + (js_sec2hms(a_chronos[i2]['today'])) + "</span></td>"
				+ "<td class='a_r'><span class='time' id='v_all_"+i2+"'>" + (js_sec2hms(a_chronos[i2]['all'])) + "</span></td>"
				+ "<td class='a_r'><span class='n_times' id='n_times_"+i2+"'>" + n_times + "</span></td></tr>";
                    $("#tb_projects > tbody").append(html);
	}
        js_update_tr_today_mark();
	}
}

function js_update_tr_today_mark(){
    /* check all the TR for find the ones that have time spent today */
        for (i2 in a_chronos){
            if (a_chronos[i2]['today']>0)
                $('tr[rel='+i2+']').addClass('today');
            else
                $('tr[rel='+i2+']').removeClass('today');
        }
    /* mark the currently active task */
        $('tr[rel='+itask+']').addClass('today');
}

function js_show_modal(id){
	var divX = pageX+12; 
	var divY = pageY+12; 
        if (divY+$(id).height() > $(window).height() + $(window).scrollTop()) 
            divY = pageY - 15 - $(id).height();  
	$(id).css('top',divY+'px').css('left',divX+'px');
	$(id).fadeIn('slow');
}

/* ============ SETTINGS management ============ */

    function js_show_settings(){
        /* == be sure that there is no active task == */
            if (itask>0){ 
                alert(texts['STOP_TIMER']);
                return;
            }
        /* == prepare languages == */
            temp_lang = settings['lang'];
            $('#div_languages a').removeClass('current');
            $('#div_languages a[rel='+temp_lang+']').addClass('current');
        /* == prepare control for filter tasks == */
            $('.show_control').attr('rel',settings['show']);
            js_update_show_control();
        js_show_modal('#div_settings');
    }
    
    function js_update_show_control(){
            /* we take the value for 'show' that is on 'rel' attribute of the control */
                var show = $('.show_control').attr('rel');
            /* reset classes to unchecked */
                $('.show_control a i').removeClass();
                $('.show_control a i').addClass('fa');
                $('.show_control a i').addClass('fa-fw');
                $('.show_control a i').addClass('fa-square-o');
            /* check the corresponding option */
                $('.show_control a[rel='+show+'] i').removeClass('fa-square-o');
                $('.show_control a[rel='+show+'] i').addClass('fa-check-square-o');
    }

/* ============ MERGE process ============ */

    function js_merge_button(id_project){
            $('#v_task_name_to_merge').html(a_chronos[id_project]['title'] + " (" + js_sec2hms(a_chronos[id_project]['all']) + ")");
            $('#div_merge select').html("");
            $('#div_merge select').append("<option value=''>-select-</option>");
            for (i2 in a_chronos){
                    $('#div_merge select').append("<option value='"+a_chronos[i2]['_id_']+"'>"+a_chronos[i2]['title']+" ("+js_sec2hms(a_chronos[i2]['all'])+")</option>");
            }
            js_show_modal('#div_merge');
            $('#div_merge select').focus();
    }

    function js_save_div_merge(idp_from,idp_to){
            // unfinished... ;)
            //a_chronos[id_project]['title']=$('#f_project_name').val();
            //$('#tit_'+id_project+' label').html(a_chronos[id_project]['title']);
            //op='op[:]merge[|]idp_from[:]'+idp_from+'[|]idp_to[:]'+idp_to;
            //js_save();
            $('#div_join').hide();	
    }

/* ============ DELETE process ============ */

    function js_delete_button(id_project){
            $('#div_delete').attr('rel', id_project);
            $('#div_delete span[rel=title]').html(a_chronos[id_project]['title']);
            js_show_modal('#div_delete');
    }

    function js_save_div_delete(){
            var id_project = $('#div_delete').attr('rel'); 
            $('#div_debug img').fadeIn('slow');
            if (itask>0){
                    it = itask;
                    js_stop(itask);
                    js_start(it);
            }
            var url_json = 'lib/json/json.php?op=delete_project&id_project=' + a_chronos[id_project]['_id_'];
            $('#txt_debug').val(url_json);
            $.getJSON(
                    url_json,
                    function(data){ 
                            $('#div_debug img').fadeOut('slow');
                            eval('js_render_projects_table()');
                    }
            );	
            a_chronos.splice(id_project, 1);
            $('#div_delete').hide();
    }

/* ============ EDIT process ============ */

    function js_edit_button(id_project){
            $('#div_edit').attr('rel', id_project);
            $('#f_project_name').val(a_chronos[id_project]['title']);
            if (a_chronos[id_project]['b_active']=='1'){
                    $('#b_active').find('i').removeClass('fa-square-o').addClass('fa-check-square-o');
                    $('#b_active').attr('rel','1');
            }else{
                    $('#b_active').find('i').removeClass('fa-check-square-o').addClass('fa-square-o');
                    $('#b_active').attr('rel','0');
            }
            js_show_modal('#div_edit');
            $('#f_project_name').focus();
    }

    function js_save_div_edit(){
            var id_project = $('#div_edit').attr('rel'); 
            a_chronos[id_project]['title'] = $('#f_project_name').val();
            $('#tit_'+id_project+' label').html(a_chronos[id_project]['title']);
            a_chronos[id_project]['b_active'] = $('#b_active').attr('rel'); 
            $('#tb_projects tr[rel='+id_project+']').removeClass();
            if (   (a_chronos[id_project]['b_active']=='1' && settings['show']=='unactive')
                || (a_chronos[id_project]['b_active']!='1' && settings['show']=='active') )
                        visible = '0';
            else        visible = '1';
            $('#tb_projects tr[rel='+id_project+']').addClass('show_'+visible);
            $('#tb_projects tr[rel='+id_project+']').addClass('active_'+a_chronos[id_project]['b_active']);
            js_save();
            $('#div_edit').hide();	
    }

/* ============ ADD NEW TASK process ============ */


    function js_add_button(){
            js_show_modal('#div_add');
            $('#f_new_project_name').focus();
    }

    function js_save_div_add(){
            var url_json = 'lib/json/json.php?op=add_project&title=' + encode64($('#f_new_project_name').val());
            $('#txt_debug').val(url_json);
            $.getJSON(
                    url_json,
                    function(data){ 
                            $('#div_debug img').fadeOut('slow');
                            n_chronos++;
                            var inew = n_chronos;   
                            a_chronos[inew] = new Array();
                            a_chronos[inew]['_id_'] = data._id_;
                            a_chronos[inew]['title'] = $('#f_new_project_name').val();
                            a_chronos[inew]['today'] = 0;
                            a_chronos[inew]['all'] = 0;
                            a_chronos[inew]['b_active'] = '1';
                            eval('js_render_projects_table()');
                    }
            );	
            $('#div_add').hide();	
    }

/* ============ STATS process ============ */

    function js_stats_button(id_project){
            $('#v_statistics_task_name').html(a_chronos[id_project]['title']);
            $('#div_stats_list').html(""); 
            $('#div_stats_list').addClass('loading');
            $('#div_stats .bt[rel=days]').addClass('selected');
            $('#div_stats .bt[rel=months]').removeClass('selected');
            var url_json = 'lib/json/json.php?op=get_times_project&id='+a_chronos[id_project]['_id_'];
            //alert(url_json);
            $.getJSON(
                    url_json,
                    function(data){ 
                            idata = data; 
                            var html = "<table style='width:100%;'>";
                            var max = data.a_project.max_time_day;
                            $.each(data.a_project.times, function(id, val) {
                                    var datev = new String(val.d);
                                    var datef = datev.substr(6,2) + "-" + datev.substr(4,2) + "-" + datev.substr(0,4);
                                    var perc = parseInt((val.t/max)*100);
                                    var bar_width = parseInt(perc*1.5);
                                    html += "<tr><td><b>" + datef + "</b></td><td>" + js_sec2hms(parseInt(val.t)) + "</td><td><img src='lib/images/pixel-vacio.gif' class='bar' style='width:"+bar_width+"px;' title='"+perc+" %'></span></td></tr>";  
                            });
                            html += "</table>";
                            /*eval(data.script);*/
                            $('#div_stats_list').removeClass('loading');
                            $('#div_stats_list').html(html); 
                    }
            );
            js_show_modal('#div_stats');
    }

    function js_stats_update(interval){
            if (interval=='days'){
                    $('#div_stats .bt').removeClass('selected');
                    $('#div_stats .bt[rel=days]').addClass('selected');
                    var a_data = idata.a_project.times; 
                    var max = idata.a_project.max_time_day;
            }else if (interval=='months'){
                    $('#div_stats .bt').removeClass('selected');
                    $('#div_stats .bt[rel=months]').addClass('selected');
                    var a_data = idata.a_project.months;  
                    var max = idata.a_project.max_time_month; 
            }else{
                    $('#div_stats .bt').removeClass('selected');
                    $('#div_stats .bt[rel=years]').addClass('selected');
                    var a_data = idata.a_project.years;  
                    var max = idata.a_project.max_time_year; 
            }
            $('#div_stats_list').html("");
            var html = "<table style='width:100%;' cellpadding='0'>";
            $.each(a_data, function(id, val) {
                    var datev = new String(val.d);
                    if (interval=='days')
                            var datef = datev.substr(6,2) + "-" + datev.substr(4,2) + "-" + datev.substr(0,4);
                    else if (interval=='months')
                            var datef = datev.substr(4,2) + "-" + datev.substr(0,4);
                    else
                            var datef = datev.substr(0,4);
                    var perc = parseInt((val.t/max)*100);
                    var bar_width = parseInt(perc*1.5);
                    html += "<tr><td><b>" + datef + "</b></td><td class='a_r'>" + js_sec2hms(parseInt(val.t)) + "</td><td><img src='lib/images/pixel-vacio.gif' class='bar' style='width:"+bar_width+"px;' title='"+perc+" %'></span></td></tr>";  
            });
            html += "</table>";
            $('#div_stats_list').html(html); 

    }

/* ============ TIME buttons ============ */

    function js_start(it){
            // stop the active task, if there is one 
            if (itask>0) js_stop(itask);
            // active the new task 
            itask = it; 
            if (itask>0){
                    dat = new Date();
                    time_ini = parseInt(dat.getTime()/1000); 
                    id_chrono = setInterval('js_update_times()',1000);
                    // hide 'start' button and show 'stop' button
                        $('tr[rel='+it+']').find('a[rel=start]').hide();
                        $('tr[rel='+it+']').find('a[rel=stop]').show();
                    // red color for active 
                        $('#tit_'+it).addClass('c_r');
                        $('#v_today_'+it).removeClass('c_b');
                        $('#v_today_'+it).addClass('c_r');
                        $('#v_all_'+it).removeClass('c_b');
                        $('#v_all_'+it).addClass('c_r');
                    // add animated clock
                        $('#tit_'+it+' span').html("<img src='lib/images/clock4.gif' /> &nbsp;"); 
                    js_update_tr_today_mark();
            }
    }

    function js_stop(it){
            if (it>0){
                    clearInterval(id_chrono);
                    js_update_chronos();
                    // hide 'stop' button and show 'start' button
                        $('tr[rel='+it+']').find('a[rel=stop]').hide();
                        $('tr[rel='+it+']').find('a[rel=start]').show();
                    // red color for active 
                    $('#tit_'+it).removeClass('c_r');
                    $('#v_today_'+it).removeClass('c_r');			
                    $('#v_all_'+it).removeClass('c_r');
                    if (a_chronos[it]['today']>0)
                    $('#v_today_'+it).addClass('c_b');
                    if (a_chronos[it]['all']>0)
                    $('#v_all_'+it).addClass('c_b');
                    // mark the TR as TODAY
                    $('#tit_'+it).parent().parent().addClass('today');
                    // remove animated clock
                    $('#tit_'+it+' span').html(''); 
                    js_update_tr_today_mark();
            }
            itask = 0; 
    }

    function js_reset(it){
            a_chronos[it]['all'] = a_chronos[it]['all'] - a_chronos[it]['today'];  
            a_chronos[it]['today'] = 0;  
            if (it==itask){
                    dat = new Date();
                    time_ini = parseInt(dat.getTime()/1000); 
            }
            js_update_times();
    }   

    function js_increment(it,inc){
            inc = inc * 60 // min -> sec 
            if (((-1)*inc)>a_chronos[it]['today']){
                    js_reset(it);
            }else{interval_grabar
                    a_chronos[it]['today'] += inc;  
                    a_chronos[it]['all'] += inc;  
                    js_update_times();
            }
    }

/* ============ TIME MANAGEMENT ============ */

    function js_update_chronos(){
            if (time_ini>0){
                    var chrono = js_chrono();
                    a_chronos[itask]['today'] += chrono; 
                    a_chronos[itask]['all'] += chrono;
                    time_ini = 0;
            }
    }

    function js_chrono(){
            var dat = new Date();
            $('#div_debug').show();
            $('#div_debug span').html(Date());
            return parseInt((dat.getTime())/1000) - time_ini;
    }

    function js_update_times(){
            var sec = 0;
            var chrono = js_chrono();
            var v_today_tot=0;
            var v_all_tot=0; 
            for (i2 in a_chronos){
                    // today chrono 
                    sec = a_chronos[i2]['today'];
                    if (i2==itask){
                            sec += chrono;
                            document.title = js_sec2hms(sec) + ' ' + a_chronos[i2]['title']; 
                    } 
                    $('#v_today_'+i2).html(js_sec2hms(sec));
                    $('#v_today_'+i2).removeClass('c_b');
                    if (a_chronos[i2]['today']>0 && i2!=itask)			$('#v_today_'+i2).addClass('c_b');
                    v_today_tot += sec;
                    // all chrono 
                    sec = a_chronos[i2]['all'];
                    if (i2==itask) sec += chrono; 
                    $('#v_all_'+i2).html(js_sec2hms(sec));
                    $('#v_all_'+i2).removeClass('c_b');
                    if (a_chronos[i2]['all']>0 && i2!=itask)			$('#v_all_'+i2).addClass('c_b');
                    v_all_tot += sec;
            }
            js_update_tr_today_mark();

            $('#v_today_tot').html(js_sec2hms(v_today_tot));
            $('#v_all_tot').html(js_sec2hms(v_all_tot));
            if (itask>0){
                    $('#v_today_'+itask).removeClass('c_b').addClass('c_r');
                    $('#v_all_'+itask).removeClass('c_b').addClass('c_r');
                    $('#tit_'+itask+' span').html("<img src='lib/images/clock4.gif' /> &nbsp;"); 
            }
    }

/* ============ CHIME ============ */

    function js_update_chime(){
            if (chime_next==0) return;
            var dat = new Date();
            var next = chime_next - parseInt((dat.getTime())/1000);
            if (next <= 0){
                    /* reset the alarm to the next period */
                    chime_unit = parseInt($('#chime_unit').val() , 10);
                    chime_period = parseInt($('#chime_period').val() , 10);
                    chime_next = chime_next + 60 * chime_unit * chime_period;
                    next = 60 * chime_unit * chime_period;
                    js_play_audio('lib/images/chime.mp3');
            }
            $('#chime_next').html(js_sec2hms(next));
    }

/* ============ misc. ============ */

    function js_sec2hms(sec){
            if (isNaN(sec)) return '00:00:00';
            var h = parseInt(sec/3600);
            var m = parseInt((sec - (h*3600))/60); 
            var s = sec - (h*3600) - (m*60);
            if (h<10) h='0'+h;  
            if (m<10) m='0'+m;  
            if (s<10) s='0'+s;   
            return h+':'+m+':'+s; 
    }

    function js_save(callback){
            if (typeof(callback)==='undefined') callback = '';
            //return;
            $('#div_debug img').fadeIn('slow');
            if (itask>0){
                    it = itask;
                    js_stop(itask);
                    js_start(it);
            }
            var data = "";
            for (i2 in a_chronos){
                    if (data!="") data += "[<>]"; 
                    data += "_id_[:]" + a_chronos[i2]['_id_'] + "[|]";
                    data += "title[:]" + a_chronos[i2]['title'] + "[|]"; 
                    data += "b_active[:]" + a_chronos[i2]['b_active'] + "[|]"; 
                    data += "today[:]" + a_chronos[i2]['today'] + "[|]"; 
                    data += "all[:]" + a_chronos[i2]['all']; 
            }
            // data: 
            /*
            _id_ [:] 5 [|] title [:] st.com. [|]  b_active [:] 0 [|] today [:] 27 [|] all [:] 1233062 [<>] _id_ [:] 6 [|] title [:] ww.co.uk [|] today [:] 0 [|] all [:] 133954   
            */
            // JSON 
            var url_json = 'lib/json/json.php?op=save_projects_data&data='+encode64(data);
            // extra operation for execute at server
            if (op!=''){
                    url_json += '&op='+encode64(op);
                    op=''; 
            }
            $('#txt_debug').val(url_json);
            $.getJSON(
                    url_json,
                    function(data){ 
                            //eval(data.script);
                            $('#div_debug img').fadeOut('slow');
                            if (data.updated_today_times=='1') js_json_get_projects();
                            $('#current_date').html(data.current_date);
                            if (callback!='') eval(callback);
                    }
            );	
    }

    function js_save_settings(){
            $('#div_debug img').fadeIn('slow');
            if (itask>0){
                    it = itask;
                    js_stop(itask);
                    js_start(it);
            }
            /* == prepare data for the server == */
                settings['show'] = $('.show_control').attr('rel');
                settings['lang'] = $('#div_languages a.current').attr('rel');
            /* == send data to server == */
                var data = "";
                $.each(settings,function(k,v){
                        if (k=='languages' || k=='app_version') return;
                        if (data!="") data += "[|]"; 
                        data += k+"[:]"+v;
                });
                /* data:           _id_ [:] 1 [|] username [:] sergi [|]  .... [|] show [:] active [|] lang [:] en       */
                    var url_json = 'lib/json/json.php?op=save_settings&data='+encode64(data);
                /* extra operation for execute at server */
                    if (op!=''){
                            url_json += '&op='+encode64(op);
                            op=''; 
                    }
                    $('#txt_debug').val(url_json);
                $.getJSON(
                        url_json,
                        function(data){ 
                                //eval(data.script);
                                $('#div_debug img').fadeOut('slow');
                                js_json_get_projects();
                                js_json_load_texts();
                        }
                );	
    }

    function getKeyCode(e){
            e= (window.event)? event : e;	
            intKey = (e.keyCode)? e.keyCode: e.charCode;
            return intKey;
    }

    var base64s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

    function encode64(decStr){
    decStr=escape(decStr);		//line add for chinese char
      var bits, dual, i = 0, encOut = '';
      while(decStr.length >= i + 3){
        bits =
        (decStr.charCodeAt(i++) & 0xff) <<16 |
        (decStr.charCodeAt(i++) & 0xff) <<8  |
         decStr.charCodeAt(i++) & 0xff;
        encOut +=
         base64s.charAt((bits & 0x00fc0000) >>18) +
         base64s.charAt((bits & 0x0003f000) >>12) +
         base64s.charAt((bits & 0x00000fc0) >> 6) +
         base64s.charAt((bits & 0x0000003f));
        }
      if(decStr.length -i > 0 && decStr.length -i < 3){
        dual = Boolean(decStr.length -i -1);
        bits =
         ((decStr.charCodeAt(i++) & 0xff) <<16) |
         (dual ? (decStr.charCodeAt(i) & 0xff) <<8 : 0);
        encOut +=
          base64s.charAt((bits & 0x00fc0000) >>18) +
          base64s.charAt((bits & 0x0003f000) >>12) +
          (dual ? base64s.charAt((bits & 0x00000fc0) >>6) : '=') +
          '=';
        }
      return encOut
      }

    function decode64(encStr) {
      var bits, decOut = '', i = 0;
      for(; i<encStr.length; i += 4){
        bits =
         (base64s.indexOf(encStr.charAt(i))    & 0xff) <<18 |
         (base64s.indexOf(encStr.charAt(i +1)) & 0xff) <<12 | 
         (base64s.indexOf(encStr.charAt(i +2)) & 0xff) << 6 |
          base64s.indexOf(encStr.charAt(i +3)) & 0xff;
        decOut += String.fromCharCode(
         (bits & 0xff0000) >>16, (bits & 0xff00) >>8, bits & 0xff);
        }
      if(encStr.charCodeAt(i -2) == 61)
        undecOut=decOut.substring(0, decOut.length -2);
      else if(encStr.charCodeAt(i -1) == 61)
        undecOut=decOut.substring(0, decOut.length -1);
      else undecOut=decOut;

      return unescape(undecOut);		//line add for chinese char
      }

    /*
     * for debug purposes in the console, for example: print_r(a_chronos,true);
     */
    function print_r(printthis, returnoutput) {
        var output = '';

        if($.isArray(printthis) || typeof(printthis) == 'object') {
            for(var i in printthis) {
                if (!printthis[i] || printthis[i]=='null')
                    output += i + ' : null \n';
                else if($.isArray(printthis[i]) || typeof(printthis[i]) == 'object') 
                    output += i + ' > \n' + print_r(printthis[i], true) + '\n';
                else
                    output += i + ' : ' + printthis[i] + '\n';
            }
        }else {
            output += printthis;
        }
        if(returnoutput && returnoutput == true) {
            return output;
        }else {
            alert(output);
        }
    }