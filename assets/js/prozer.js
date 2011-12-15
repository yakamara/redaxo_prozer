

/* ******************* Document Ready **************** */

$(document).ready(function()
{ 
  // Kalender - Tag
  // Scroll-Offset
  // pz_set_calendarday_offset();
  
  // Kalender - Woche
  // Termine richtig positionieren
  // pz_set_calendarweek_events();
  
  // Init der Tages Kalender Verschiebung
  // pz_set_calendarday_dragresize_init();

  // $('<div id="hovery"></div>').prependTo(document.body);




	// var pz_timer = window.setTimeout(pz_tracker, 10000);

});

/* ******************* check Login **************** */

var pz_login_refresh = true;

function pz_isLoggedIn(data)
{
	// data und "login" vergleichen
	if(data == "relogin") {
		pz_login_refresh = false;
		pz_getLoginForm()
		return false;
	}
	return true;
}

function pz_logIn()
{
	pz_loading_start("loginbox");
	$.post("/screen/login/form/", $("#login_form").serialize(), function(data) {
		if(data == 1) {
			
			// refresh nur auf der startseite
			if(pz_login_refresh)
				location.href = "/";
			else
			 	$.facebox.close();
			
		}else
		{
			$("#loginbox").replaceWith(data);
		}
    });
	return;
}

function pz_getLoginForm()
{
	jQuery.facebox({ ajax: '/screen/login/form/' });
}


/* ******************* Tracker **************** */

function pz_tracker() 
{
	link = '/screen/tools/tracker/';
	$.post(link, '', function(data) {
		$("#pz_tracker").html(data);
		window.setTimeout(pz_tracker, 10000);
     });
}


/* ******************* Dropdown **************** */

// Select
function pz_save_dropdown_value($clickid)
{
  var $value = $('#' + $clickid).attr('rel');
  var $text  = $('#' + $clickid).text();
  var $id    = $('#' + $clickid).parents('.js-save-dropdown-value').find('.selected input:hidden').attr('id');

  $('#' + $id).attr('value', $value);
  $('.' + $id + '-selected span.selected').text($text);
  $('.' + $id + '-selected ul.entries a').removeClass('active');
  
  $('#' + $clickid).addClass('active');
}


/* ******************* LAYER LOADING, LOGIN **************** */

function pz_hide(node) {
	$(node).hide();
}


function pz_load_main(layer_id) {
	// layer laden
	// nach vor bringen / sichtbar machen
	// andere sachen nach hinten legen	
	alert("loadmain"+layer_id);
}

function pz_exec_javascript(link) {
	$.post(link, '', function(data) {
		$('body').append(data);
     });	
}

function pz_loading_start(layer_id)
{
	// $('#'+layer_id).css('position', 'relative');
	// $('#'+layer_id).fadeOut("fast");
	// $('<div class="loader"></div>').appendTo('#'+layer_id);
    // , left: "-=50", top: "-=50", height: "+=100", width: "+=100"
	$('#'+layer_id).animate({ opacity: 0 }, 1000 );
}

function pz_loading_end(layer_id)
{
	// $('<div class="loader"></div>').appendTo('#'+layer_id);
	$('#'+layer_id).animate({ opacity: 1 }, 1000 );
}

function pz_loadFormPage(layer_id,form_id,link)
{
	pz_loading_start(layer_id);
	if(link.indexOf("?")) link += "&pz_login_refresh=1";
	else link += "?pz_login_refresh=1";
	
	$.post(link, $("#"+form_id).serialize(), function(data) {
		if(pz_isLoggedIn(data))
		{
			$("#"+layer_id).replaceWith(data);
			$("#"+layer_id).hide();
			$("#"+layer_id).fadeIn("fast");
		}
		pz_loading_end(layer_id);
     });
}

function pz_loadPage(layer_id,link)
{
	pz_loading_start(layer_id);
	if(link.indexOf("?")) link += "&pz_login_refresh=1";
	else link += "?pz_login_refresh=1";
	$.post(link, '', function(data) {
		if(pz_isLoggedIn(data))
		{
			$("#"+layer_id).replaceWith(data);
			$("#"+layer_id).css("opacity",0);
			// $("#"+layer_id).hide();
			// $("#"+layer_id).fadeIn("fast");
			// $("#"+layer_id).animate({ "opacity": 1.0 }, 300 );
		}
		pz_loading_end(layer_id);
     });
}


/* ******************* Emails **************** */

function pz_open_email(id,link) {

	email = $("#email-"+id);
	email_preview = $("#email-content-preview-"+id);
	email_detail = $("#email-content-detail-"+id);
	
	if(email.hasClass('open'))
	{
		email.addClass('close');
		email.removeClass('open');
		email_preview.show();
		email_detail.hide();

	}else
	{
		email.removeClass('close');
		email.addClass('open');
		email_preview.hide();
		email_detail.show();
		
		// if(email_detail.html() == "")
		pz_loadPage("email-content-detail-"+id,link);
		
	}
	
}




/* ******************* Calendar **************** */

var pz_event_day_url = "/screen/calendars/api/";

// Calendar Einträge verschieben
function pz_set_calendarday_dragresize_init() {
	$("#calendar_events_day_list .dragable").draggable({
		containment: "#calendar_events_day_list .calendargrid",
		cursor: "pointer",
		axis: "y",
		delay: "200",
		grid: [0, 15],
		opacity: 0.75,
		scroll: true,
	   	start: function(event, ui) {
	   		$(".draggable").css("z-index","auto");
	   		$(this).css("z-index","10000");
	   	},
	   	stop: function(event, ui) {
	   		var offsetY = $("#calendar_events_day_list .calendargrid").offset();
	   		var y = $(this).offset();
	   		var event_from_pixel = y.top - offsetY.top;
	   		var event_id = $(this).attr("id").replace("event-","");
	   		var event_duration_pixel = parseInt($(this).outerHeight());
	   		var event_position_pixel = parseInt($(this).css("left"));

	   		$.get(pz_event_day_url, {mode: "dayview_event_change", event_id: event_id, event_from_pixel: event_from_pixel,event_duration_pixel: event_duration_pixel, event_position_pixel: event_position_pixel, event_id: event_id },
	   		   function(data){
	   		   	$("#event-" + event_id).replaceWith(data.html);
	   		   	pz_set_calendarday_dragresize_init();
	   		   }, "json");
	   	}
	});
	$( "#calendar_events_day_list .resizeable").resizable({
		handles: "n,s",
		minHeight: "15",
		grid: [0, 15],
		start: function(e, ui) {
		},
		resize: function(e, ui) {
		},
		stop: function(e, ui) {
			var offsetY = $("#calendar_events_day_list .calendargrid").offset();
	   		var y = $(this).offset();
	   		var event_from_pixel = y.top - offsetY.top;
	   		var event_id = $(this).attr("id").replace("event-","");
	   		var event_duration_pixel = parseInt($(this).outerHeight());
	   		var event_position_pixel = parseInt($(this).css("left"));

	   		$.get(pz_event_day_url, {mode: "dayview_event_change", event_id: event_id, event_from_pixel: event_from_pixel,event_duration_pixel: event_duration_pixel, event_position_pixel: event_position_pixel, event_id: event_id },
	   		   function(data){
	   		   	$("#event-" + event_id).replaceWith(data.html);
	   		   	pz_set_calendarday_dragresize_init();
	   		   }, "json");
			
		}
   });
}

// Kalender - Tag
// Scroll-Offset auf X Uhr setzen
function pz_set_calendarday_offset()
{
  var time = 8;
  var scroll = time * 60;
  $('.calendar.view-day .wrapper').scrollTop(scroll);
}

// Kalender - Woche
// Termine richtig positionieren
function pz_set_calendarweek_events()
{
  $('ul.weekdays li.weekday').each(function()
  {
    // das <li> hat ein rel mit entsprechenden Wochentag
    // das event <article> hat ebenfalls den Wochentag im rel 
    
    var $rel = $(this).attr('rel');           // Wochentag holen -> "weekday-mon", "weekday-tue"
    var $hours = $(this).find('.hours');      // Tagesstunden des Wochentages
    var $hours_position = $hours.position();  // Position der Tagesstunden-Spalte
    var $hours_width = $hours.width();
    
    var $first_column_width = $('ul.weekdays li.weekday ul.hours').width(); // Breite der Tagesstunden-Spalte
    
    // Alle Event <article> mit Wochentag holen und platzieren
    $('.events article[rel="' + $rel + '"]').each(function()
    {
      var $event = $(this);
      var $top = parseInt($event.css('top').replace('px', ''));
      var $left = parseInt($event.css('left').replace('px', ''));
      
      $top = $top + $hours_position.top;
      $left = $left + $hours_position.left - $first_column_width;
      $event.css('top', $top + 'px')
      $event.css('left', $left + 'px')
      $event.css('width', $hours_width + 'px')
    });
  });
}
// ENDE - Kalender - Woche



/* *******************  **************** */


