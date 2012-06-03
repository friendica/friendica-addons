/**
 * @description {Class} wdCalendar
 * This is the main class of wdCalendar.
 */
(function ($) {
	"use strict";

	var __WDAY = new Array(i18n.xgcalendar.dateformat.sun, i18n.xgcalendar.dateformat.mon, i18n.xgcalendar.dateformat.tue, i18n.xgcalendar.dateformat.wed, i18n.xgcalendar.dateformat.thu, i18n.xgcalendar.dateformat.fri, i18n.xgcalendar.dateformat.sat);
	var __MonthName = new Array(i18n.xgcalendar.dateformat.jan, i18n.xgcalendar.dateformat.feb, i18n.xgcalendar.dateformat.mar, i18n.xgcalendar.dateformat.apr, i18n.xgcalendar.dateformat.may, i18n.xgcalendar.dateformat.jun, i18n.xgcalendar.dateformat.jul, i18n.xgcalendar.dateformat.aug, i18n.xgcalendar.dateformat.sep, i18n.xgcalendar.dateformat.oct, i18n.xgcalendar.dateformat.nov, i18n.xgcalendar.dateformat.dec);


	function dateFormat(format) {
		var o = {
			"M+":this.getMonth() + 1,
			"d+":this.getDate(),
			"h+":this.getHours(),
			"H+":this.getHours(),
			"m+":this.getMinutes(),
			"s+":this.getSeconds(),
			"q+":Math.floor((this.getMonth() + 3) / 3),
			"w":"0123456".indexOf(this.getDay()),
			"W":__WDAY[this.getDay()],
			"L":__MonthName[this.getMonth()] //non-standard
		};
		if (/(y+)/.test(format)) {
			format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
		}
		for (var k in o) {
			if (new RegExp("(" + k + ")").test(format))
				format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
		}
		return format;
	}

	function DateDiff(interval, d1, d2) {
		switch (interval) {
			case "d": //date
			case "w":
				d1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate());
				d2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate());
				break;  //w
			case "h":
				d1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate(), d1.getHours());
				d2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate(), d2.getHours());
				break; //h
			case "n":
				d1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate(), d1.getHours(), d1.getMinutes());
				d2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate(), d2.getHours(), d2.getMinutes());
				break;
			case "s":
				d1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate(), d1.getHours(), d1.getMinutes(), d1.getSeconds());
				d2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate(), d2.getHours(), d2.getMinutes(), d2.getSeconds());
				break;
		}
		var t1 = d1.getTime(), t2 = d2.getTime();
		var diff = NaN;
		switch (interval) {
			case "y":
				diff = d2.getFullYear() - d1.getFullYear();
				break; //y
			case "m":
				diff = (d2.getFullYear() - d1.getFullYear()) * 12 + d2.getMonth() - d1.getMonth();
				break;    //m
			case "d":
				diff = Math.floor(t2 / 86400000) - Math.floor(t1 / 86400000);
				break;
			case "w":
				diff = Math.floor((t2 + 345600000) / (604800000)) - Math.floor((t1 + 345600000) / (604800000));
				break; //w
			case "h":
				diff = Math.floor(t2 / 3600000) - Math.floor(t1 / 3600000);
				break; //h
			case "n":
				diff = Math.floor(t2 / 60000) - Math.floor(t1 / 60000);
				break; //
			case "s":
				diff = Math.floor(t2 / 1000) - Math.floor(t1 / 1000);
				break; //s
			case "l":
				diff = t2 - t1;
				break;
		}
		return diff;

	}

	function DateAdd(interval, number, idate) {
		number = parseInt(number);
		var date;
		if (typeof (idate) == "string") {
			date = idate.split(/\D/);
			eval("var date = new Date(" + date.join(",") + ")");
		}

		if (typeof (idate) == "object") {
			date = new Date(idate.toString());
		}
		switch (interval) {
			case "y":
				date.setFullYear(date.getFullYear() + number);
				break;
			case "m":
				date.setMonth(date.getMonth() + number);
				break;
			case "d":
				date.setDate(date.getDate() + number);
				break;
			case "w":
				date.setDate(date.getDate() + 7 * number);
				break;
			case "h":
				date.setHours(date.getHours() + number);
				break;
			case "n":
				date.setMinutes(date.getMinutes() + number);
				break;
			case "s":
				date.setSeconds(date.getSeconds() + number);
				break;
			case "l":
				date.setMilliseconds(date.getMilliseconds() + number);
				break;
		}
		return date;
	}

	function ColorrCalcBrighten(col, factor) {
		return 255-Math.round((255 - col) * factor);
	}
	function ColorCalcValues(basecol) {
		if (!basecol.match(/^#[0-9a-f]{6}$/i)) return ColorCalcValues("#f8f8ff");
		var r = parseInt(basecol.substring(1, 3), 16);
		var g = parseInt(basecol.substring(3, 5), 16);
		var b = parseInt(basecol.substring(5, 7), 16);
		var col1 = "#" + ColorrCalcBrighten(r, 0.6).toString(16) + ColorrCalcBrighten(g, 0.6).toString(16) + ColorrCalcBrighten(b, 0.6).toString(16);
		var col2 = "#" + ColorrCalcBrighten(r, 0.5).toString(16) + ColorrCalcBrighten(g, 0.5).toString(16) + ColorrCalcBrighten(b, 0.5).toString(16);
		return [basecol, col1, col2];
	}


	if ($.fn.noSelect == undefined) {
		$.fn.noSelect = function (p) { //no select plugin by me :-)
			var prevent;
			if (p == null)
				prevent = true;
			else
				prevent = p;
			if (prevent) {
				return this.each(function () {
					if ($.browser.msie || $.browser.safari) $(this).bind('selectstart', function () {
						return false;
					});
					else if ($.browser.mozilla) {
						$(this).css('MozUserSelect', 'none');
						$('body').trigger('focus');
					}
					else if ($.browser.opera) $(this).bind('mousedown', function () {
						return false;
					});
					else $(this).data('unselectable', 'on');
				});

			} else {
				return this.each(function () {
					if ($.browser.msie || $.browser.safari) $(this).unbind('selectstart');
					else if ($.browser.mozilla) $(this).css('MozUserSelect', 'inherit');
					else if ($.browser.opera) $(this).unbind('mousedown');
					else $(this).removeData('unselectable', 'on');
				});

			}
		}; //end noSelect
	}
	$.fn.bcalendar = function (option) {
		var def = {
			/**
			 * @description {Config} view
			 * {String} Three calendar view provided, 'day','multi_days','week','month'. 'week' by default.
			 */
			view:"week",

			date_format_dm1:"W, d.M",
			date_format_dm2:"d. L",
			date_format_dm3:"d L yyyy",
			date_format_full:"yy-mm-dd",

			/**
			 * @description {Config} weekstartday
			 * {Number} First day of week 0 for Sun, 1 for Mon, 2 for Tue.
			 */
			weekstartday:1, //start from Monday by default
			std_color: "#5858ff",
			/**
			 * @description {Config} height
			 * {Number} Calendar height, false for page height by default.
			 */
			height:false,
			/**
			 * @description {Config} url
			 * {String} Url to request calendar data.
			 */
			url:"",

			/**
			 * @description {Config} eventItems
			 * {Array} event items for initialization.
			 */
			eventItems:[],
			method:"POST",
			/**
			 * @description {Config} showday
			 * {Date} Current date. today by default.
			 */
			showday:new Date(),
			/**
			 * @description {Event} onBeforeRequestData:function(stage)
			 * Fired before any ajax request is sent.
			 * @param {Number} stage. 1 for retrieving events, 2 - adding event, 3 - removiing event, 4 - update event.
			 */
			onBeforeRequestData:false,
			/**
			 * @description {Event} onAfterRequestData:function(stage)
			 * Fired before any ajax request is finished.
			 * @param {Number} stage. 1 for retrieving events, 2 - adding event, 3 - removiing event, 4 - update event.
			 */
			onAfterRequestData:false,
			/**
			 * @description {Event} onAfterRequestData:function(stage)
			 * Fired when some errors occur while any ajax request is finished.
			 * @param {Number} stage. 1 for retrieving events, 2 - adding event, 3 - removiing event, 4 - update event.
			 */
			onRequestDataError:false,

			onWeekOrMonthToDay:false,

			/**
			 * @description {Event} quickAddHandler:function(calendar, param )
			 * Fired when user quick adds an item. If this function is set, ajax request to quickAddUrl will abort.
			 * @param {Object} calendar Calendar object.
			 * @param {Array} param Format [{name:"name1", value:"value1"}, ...]
			 *
			 */
			quickAddHandler:false,

			quickUpdateHandler:false,

			quickDeleteHandler: false,
			/**
			 * @description {Config} quickAddUrl
			 * {String} Url for quick adding.
			 */
			quickAddUrl:"",
			/**
			 * @description {Config} quickUpdateUrl
			 * {String} Url for time span update.
			 */
			quickUpdateUrl:"",
			/**
			 * @description {Config} quickDeleteUrl
			 * {String} Url for removing an event.
			 */
			quickDeleteUrl:"",
			/**
			 * @description {Config} autoload
			 * {Boolean} If event items is empty, and this param is set to true.
			 * Event will be retrieved by ajax call right after calendar is initialized.
			 */
			autoload:false,
			/**
			 * @description {Config} readonly
			 * {Boolean} Indicate calendar is readonly or editable
			 */
			readonly:false,
			/**
			 * @description {Config} extParam
			 * {Array} Extra params submitted to server.
			 * Sample - [{name:"param1", value:"value1"}, {name:"param2", value:"value2"}]
			 */
			extParam:[],
			/**
			 * @description {Config} enableDrag
			 * {Boolean} Whether end user can drag event item by mouse.
			 */
			enableDrag:true,
			url_add:"",
			num_days:7,
			hour_height:42,

			calendars_available:[],
			calendars_selected:[]
		};
		var eventDiv = $("#gridEvent");
		if (eventDiv.length == 0) {
			eventDiv = $("<div id='gridEvent' style='display:none;'></div>").appendTo(document.body);
		}
		var $gridcontainer = $(this);
		option = $.extend(def, option);

		//no quickUpdateUrl, dragging disabled.
		if (option.quickUpdateUrl == null || option.quickUpdateUrl == "") {
			option.enableDrag = false;
		}
		//template for month and date
		var __SCOLLEVENTTEMP = "<DIV style=\"width:{width};top:{top};left:{left};\" title=\"{title}\" class=\"chip chip{i} {drag} {addclasses}\">" +
			"<DIV style=\"border-bottom-color:{bdcolor}\" class=ct>&nbsp;</DIV>" +
			"<DL style=\"border-color:{bdcolor}; background-color:{bgcolor1}; height: {height}px;\"><DT style=\"background-color:{bgcolor2}\">{starttime} - {endtime} {icon}</DT>" +
			"<DD><SPAN>{content}</SPAN></DD><DIV class='resizer' style='display:{redisplay}'><DIV class=rszr_icon>&nbsp;</DIV></DIV></DL>" +
			"<DIV style=\"BORDER-BOTTOM-COLOR:{bdcolor}; background-color:{bgcolor1}; border-color: {bdcolor};\" class=cb1>&nbsp;</DIV>" +
			"<DIV style=\"border-color:{bdcolor};\" class=cb2>&nbsp;</DIV></DIV>";
		var __ALLDAYEVENTTEMP = '<div class="rb-o {eclass}" id="{id}" title="{title}" style="color:{color};">' +
			'<div class="{extendClass} rb-m" style="background-color:{color}">{extendHTML}<div class="rb-i">{content}</div></div></div>';
		var __MonthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var __LASSOTEMP = "<div class='drag-lasso' style=\"left:{left}px;top:{top}px;width:{width}px;height:{height}px;\">&nbsp;</div>";
		//for dragging var
		var _dragdata;
		var _dragevent;

		//clear DOM
		clearcontainer();

		//no height specified in options, we get page height.
		if (!option.height) {
			option.height = document.documentElement.clientHeight;
		}
		//
		$gridcontainer.css("overflow-y", "visible").height(option.height - 8);

		//populate events data for first display.
		if (option.url && option.autoload) {
			populate();
		}
		else {
			//contruct HTML          
			render();
		}

		//clear DOM
		function clearcontainer() {
			$gridcontainer.empty();
		}


		//contruct DOM 
		function render() {
			//params needed
			//viewType, showday, events, config			
			var showday = new Date(option.showday.getFullYear(), option.showday.getMonth(), option.showday.getDate());
			var events = option.eventItems;
			var config = { view:option.view, weekstartday:option.weekstartday, color:option.std_color };
			if (option.view == "day" || option.view == "week" || option.view == "multi_days") {
				var $dvtec = $gridcontainer.find(".scrolltimeevent");
				if ($dvtec.length > 0) {
					option.scroll = $dvtec.scrollTop(); //get scroll bar position
				}
			}
			switch (option.view) {
				case "day":
					BuildDaysAndWeekView(showday, 1, events, config);
					break;
				case "week":
					BuildDaysAndWeekView(showday, 7, events, config);
					break;
				case "multi_days":
					BuildDaysAndWeekView(showday, option.num_days, events, config);
					break;
				case "month":
					BuildMonthView(showday, events, config);
					break;
				default:
					alert(i18n.xgcalendar["no_implement"]);
					break;
			}
			initevents(option.view);
			ResizeView();
		}

		//build day view
		function BuildDaysAndWeekView(startday, l, events, config) {
			var days = [],
				show;
			if (l == 1) {
				show = dateFormat.call(startday, option.date_format_dm1);
				days.push({ display:show, date:startday, day:startday.getDate(), year:startday.getFullYear(), month:startday.getMonth() + 1 });
				option.datestrshow = CalDateShow(days[0].date);
				option.vstart = days[0].date;
				option.vend = days[0].date;
			}
			else {
				var w = 0;
				if (l == 7) {
					w = config.weekstartday - startday.getDay();
					if (w > 0) w = w - 7;
				}
				var ndate;
				for (var i = w, j = 0; j < l; i = i + 1, j++) {
					ndate = DateAdd("d", i, startday);
					show = dateFormat.call(ndate, option.date_format_dm1);
					days.push({ display:show, date:ndate, day:ndate.getDate(), year:ndate.getFullYear(), month:ndate.getMonth() + 1 });
				}
				option.vstart = days[0].date;
				option.vend = days[l - 1].date;
				option.datestrshow = CalDateShow(days[0].date, days[l - 1].date);
			}

			var allDayEvents = [];
			var scrollDayEvents = [];
			//get number of all-day events, including more-than-one-day events.
			var dM = PrepareEvents(days, events, allDayEvents, scrollDayEvents);

			var $html = $("<div class=\"wktopcontainer\"></div>");
			$html.append(BuildWT(days, allDayEvents, dM));

			$gridcontainer.html("").append($html);

			$html = $("<div class=\"scrolltimeevent\"></div>");
			$html.append(BuildDayScollEventContainer(days, scrollDayEvents));
			$gridcontainer.append($html);

			//TODO event handlers
			//$gridcontainer.find(".weekViewAllDaywk").click(RowHandler);
		}

		//build month view
		function BuildMonthView(showday, events, config) {
			$gridcontainer.find("*").remove();
			$gridcontainer.append("<div class='cal-month-cc cc'><div class='cal-month-cc-header'><div class='cc-close cal-month-closebtn'></div><div class='cal-month-cc-title cc-title'></div></div><div class='cal-month-cc-body cc-body'><div class='cal-month-cc-content st-contents'><table class='st-grid'><tbody></tbody></table></div></div></div>");
			var html = [];
			//build header
			html.push("<div id=\"mvcontainer\" class=\"mv-container\">");
			html.push("<table id=\"mvweek\" class=\"mv-daynames-table\"><tbody><tr>");
			for (var i = config.weekstartday, j = 0; j < 7; i++, j++) {
				if (i > 6) i = 0;
				html.push("<th class=\"mv-dayname\" title=\"", __WDAY[i], "\">", __WDAY[i], "");
			}
			html.push("</tr></tbody></table>");
			html.push("</div>");
			$gridcontainer.append(html.join(""));

			var bH = GetMonthViewBodyHeight() - GetMonthViewHeaderHeight();
			var $container = $("<div class=\"mvEventContainer mv-event-container\" style=\"height:" + bH + "px;" + "\"></div>");
			var $body = BuilderMonthBody(showday, config.weekstartday, events, bH);
			$container.append($body);
			$gridcontainer.append($container);

			$gridcontainer.find(".cal-month-closebtn").click(closeCc);
		}

		function closeCc() {
			$gridcontainer.find(".cal-month-cc").css("visibility", "hidden");
		}

		//all-day event, including more-than-one-day events 
		function PrepareEvents(dayarrs, events, allDayEvents, scrolLDayEvents) {
			var i, j, k, de, x, y, La, H, D, Ia,
				tmp_allday = allDayEvents,
				tmp_scrollevents = scrolLDayEvents,
				l = dayarrs.length,
				el = events.length,
				fE = [];
			for (j = 0; j < el; j++) {
				var sD = events[j]["start"];
				var eD = events[j]["end"];
				var s = {};
				s.event = events[j];
				s.day = sD.getDate();
				s.year = sD.getFullYear();
				s.month = sD.getMonth() + 1;
				s.allday = events[j]["is_allday"] == 1;
				s.crossday = events[j]["is_moredays"] == 1;
				s.reevent = events[j]["is_recurring"] == 1; //Recurring event
				s.daystr = [s.year, s.month, s.day].join("/");
				s.st = {};
				s.st.hour = sD.getHours();
				s.st.minute = sD.getMinutes();
				s.st.p = s.st.hour * 60 + s.st.minute; // start time
				s.et = {};
				s.et.hour = eD.getHours();
				s.et.minute = eD.getMinutes();
				s.et.p = s.et.hour * 60 + s.et.minute; // end time
				fE.push(s);
			}
			var dMax = 0;
			for (i = 0; i < l; i++) {
				var da = dayarrs[i];
				tmp_scrollevents[i] = [];
				tmp_allday[i] = [];
				da.daystr = da.year + "/" + da.month + "/" + da.day;
				for (j = 0; j < fE.length; j++) {
					if (!fE[j].crossday && !fE[j].allday) {
						if (da.daystr == fE[j].daystr)
							tmp_scrollevents[i].push(fE[j]);
					}
					else {
						if (da.daystr == fE[j].daystr) {
							tmp_allday[i].push(fE[j]);
							dMax++;
						}
						else {
							if (i == 0 && da.date >= fE[j].event["start"] && da.date <= fE[j].event["end"])//first more-than-one-day event
							{
								tmp_allday[i].push(fE[j]);
								dMax++;
							}
						}
					}
				}
			}
			var lrdate = dayarrs[l - 1].date;
			for (i = 0; i < l; i++) { //to deal with more-than-one-day event
				de = tmp_allday[i];
				if (de.length > 0) { //           
					for (j = 0; j < de.length; j++) {
						var end = DateDiff("d", lrdate, de[j].event["end"]) > 0 ? lrdate : de[j].event["end"];
						de[j].colSpan = DateDiff("d", dayarrs[i].date, end) + 1
					}
				}
				de = null;
			}
			//for all-day events
			for (i = 0; i < l; i++) {
				de = tmp_scrollevents[i];
				if (de.length > 0) {
					x = [];
					y = [];
					D = [];
					var dl = de.length;
					for (j = 0; j < dl; ++j) {
						var ge = de[j];
						for (La = ge.st.p, Ia = 0; y[Ia] > La;) Ia++;
						ge.PO = Ia;
						ge.ne = []; //PO is how many events before this one
						y[Ia] = ge.et.p || 1440;
						x[Ia] = ge;
						if (!D[Ia]) {
							D[Ia] = [];
						}
						D[Ia].push(ge);
						if (Ia != 0) {
							ge.pe = [x[Ia - 1]]; //previous event
							x[Ia - 1].ne.push(ge); //next event
						}
						for (Ia = Ia + 1; y[Ia] <= La;) Ia++;
						if (x[Ia]) {
							k = x[Ia];
							ge.ne.push(k);
							k.pe.push(ge);
						}
						ge.width = 1 / (ge.PO + 1);
						ge.left = 1 - ge.width;
					}
					k = Array.prototype.concat.apply([], D);
					x = y = D = null;
					var t = k.length;
					for (y = t; y--;) {
						H = 1;
						La = 0;
						x = k[y];
						for (D = x.ne.length; D--;) {
							Ia = x.ne[D];
							La = Math.max(La, Ia.VL);
							H = Math.min(H, Ia.left)
						}
						x.VL = La + 1;
						x.width = H / (x.PO + 1);
						x.left = H - x.width;
					}
					for (y = 0; y < t; y++) {
						x = k[y];
						x.left = 0;
						if (x.pe) for (D = x.pe.length; D--;) {
							H = x.pe[D];
							x.left = Math.max(x.left, H.left + H.width);
						}
						var p = (1 - x.left) / x.VL;
						x.width = Math.max(x.width, p);
						x.aQ = Math.min(1 - x.left, x.width + 0.7 * p); //width offset
					}
					de = null;
					tmp_scrollevents[i] = k;
				}
			}
			return dMax;
		}


		// Week view: top row (full-day events)
		function BuildWT(dayarrs, events, dMax) {
			//1:
			var i, j, h, e, el, x, l;
			var html = "<table class=\"wk-top\">";
			html += "<tr><th style='width: 60px;' rowspan=\"3\">&nbsp;</th>";
			for (i = 0; i < dayarrs.length; i++) {
				var ev, title, cl;
				if (dayarrs.length == 1) {
					ev = "";
					title = "";
					cl = "";
				}
				else {
					ev = ""; // "onclick=\"javascript:FunProxy('week2day',event,this);\"";
					title = i18n.xgcalendar.to_date_view;
					cl = "wk-daylink";
				}
				html += "<th data-abbr='" + dayarrs[i].date.getTime() + "' class='gcweekname' scope=\"col\">";
				html += "<div title='" + title + "' " + ev + " class='wk-dayname'><span class='" + cl + "'>" + dayarrs[i].display + "</span></div></th>";

			}
			html += "<th style='width: 16px;' rowspan=\"3\">&nbsp;</th>";
			html += "</tr>"; //end tr1;
			//2:          
			html += "<tr>";
			html += "<td class=\"wk-allday\"";

			if (dayarrs.length > 1) {
				html += " colSpan='" + dayarrs.length + "'";
			}
			//onclick=\"javascript:FunProxy('rowhandler',event,this);\"
			html += "><div class=\"weekViewAllDaywk\" ><table class=\"st-grid\"><tbody>";

			if (dMax == 0) {
				html += "<tr>";
				for (i = 0; i < dayarrs.length; i++) {
					html += "<td class=\"st-c st-s\" data-ch='qkadd' data-abbr='" + dayarrs[i].date.getTime() + "' data-axis='00:00'>&nbsp;</td>";
				}
				html += "</tr>";
			}
			else {
				l = events.length;
				el = 0;
				x = [];
				for (j = 0; j < l; j++) {
					x.push(0);
				}
				//var c = tc();
				for (j = 0; el < dMax; j++) {
					html += "<tr class='row" + j + "'>";
					for (h = 0; h < l;) {
						e = events[h][x[h]];
						html += "<td class='st-c col" + h;
						if (e) { //if exists
							x[h] = x[h] + 1;
							html += "'";
							if (e.colSpan > 1) {
								html += " colSpan='" + e.colSpan + "'";
								h += e.colSpan;
							}
							else {
								h++;
							}
							html += " ch='show'>";
							el++;
						}
						else {
							html += " st-s' data-ch='qkadd' data-abbr='" + dayarrs[h].date.getTime()  + "' data-axis='00:00'>&nbsp;";
							h++;
						}
						html += "</td>";
					}
					html += "</tr>";
				}
				html += "<tr>";
				for (h = 0; h < l; h++) {
					html += "<td class='st-c st-s' data-ch='qkadd' data-abbr='" + dayarrs[h].date.getTime() + "' data-axis='00:00'>&nbsp;</td>";
				}
				html += "</tr>";
			}
			html += "</tbody></table></div></td></tr>"; // stgrid end //wvAd end //td2 end //tr2 end
			//3:
			html += "<tr>";

			html += "<td style=\"height: 5px;\"";
			if (dayarrs.length > 1) {
				html += " colSpan='" + dayarrs.length + "'";
			}
			html += "></td>";
			html += "</tr>";
			html += "</table>";
			var $el = $(html);

			if (dMax > 0) {
				l = events.length;
				el = 0;
				x = [];
				for (j = 0; j < l; j++) {
					x.push(0);
				}
				for (j = 0; el < dMax; j++) {
					for (h = 0; h < l;) {
						e = events[h][x[h]];
						if (e) { //if exists
							x[h] = x[h] + 1;
							var $t = BuildMonthDayEvent(e, dayarrs[h].date, l - h);
							$el.find(".row" + j + " .col" + h).append($t);
							if (e.colSpan > 1) {
								h += e.colSpan;
							}
							else {
								h++;
							}
							el++;
						}
						else {
							h++;
						}
					}
				}
			}

			return $el;
		}

		function BuildDayScollEventContainer(dayarrs, events) {
			//1:
			var i, c;

			var html = "<table style=\"table-layout: fixed;";
			html += ($.browser.msie ? "" : "width:100%");
			html += "\"><tbody><tr><td><table style=\"height: " + (option.hour_height * 24) + "px\" class=\"tg-timedevents\"><tbody>";
			html += "<tr><td style='width:60px;'></td><td";
			if (dayarrs.length > 1) {
				html += " colSpan='" + dayarrs.length + "'";
			}
			html += "><div class=\"tg-spanningwrapper\"><div style=\"font-size: " + (Math.round(option.hour_height / 2) - 1) + "px\" class=\"tg-hourmarkers\">";
			for (i = 0; i < 24; i++) {
				html += "<div class=\"tg-dualmarker\"></div>";
			}
			html += "</div></div></td></tr>";

			//2:
			html += "<tr>";
			html += "<td style=\"width: 60px; \" class=\"tg-times\">";

			//get current time 
			var now = new Date();
			var h = now.getHours();
			var m = now.getMinutes();
			var mHg = gP(h, m) - 4; //make middle alignment vertically
			html += "<div id=\"tgnowptr\" class=\"tg-nowptr\" style=\"left:0;top:" + mHg + "px\"></div>";
			for (i = 0; i < 24; i++) html += "<div style=\"height: " + (option.hour_height - 1) + "px\" class=\"tg-time\">" + fomartTimeShow(i) + "</div>";
			html += "</td>";

			var l = dayarrs.length;
			var hh24 = option.hour_height * 24;
			for (i = 0; i < l; i++) {
				html += "<td class='tg-col' data-ch='qkadd' data-abbr='" + dayarrs[i].date.getTime() + "'>";
				var istoday = formatDate(dayarrs[i].date) == formatDate(new Date());
				// Today
				if (istoday) {
					html += "<div style=\"margin-bottom: -" + hh24 + "px; height:" + hh24 + "px\" class=\"tg-today\">&nbsp;</div>";
				}
				//var eventC = $(eventWrap);
				//onclick=\"javascript:FunProxy('rowhandler',event,this);\"
				html += "<div  style=\"margin-bottom: -" + hh24 + "px; height: " + hh24 + "px\" data-col='" + i + "' class='tgCol" + i + " tg-col-eventwrapper'></div>";

				html += "<div class='tg-col-overlaywrapper tgOver" + i + "' data-col='" + i + "'>";
				if (istoday) {
					var mhh = mHg + 4;
					html += "<div class=\"tg-hourmarker tg-nowmarker\" style=\"left:0;top:" + mhh + "px\"></div>";
				}
				html += "</div>";
				html += "</td>";
			}
			html += "</tr>";

			html += "</tbody></table></td></tr></tbody></table>";
			var $container = $(html);

			for (i = 0; i < l; i++) {
				var $col = $container.find(".tgCol" + i);
				for (var j = 0; j < events[i].length; j++) {
					if (events[i][j].event["color"] && events[i][j].event["color"].match(/^#[0-9a-f]{6}$/i)) {
						c = events[i][j].event["color"];
					}
					else {
						c = option.std_color;
					}
					var $tt = BuildDayEvent(c, events[i][j], j);
					$col.append($tt);
				}
			}

			return $container;
		}


		function getTitle(event) {
			var timeshow, eventshow;
			var showtime = event["is_allday"] != 1;
			eventshow = event["subject"];
			var startformat = getymformat(event["start"], null, showtime, true);
			var endformat = getymformat(event["end"], event["start"], showtime, true);
			timeshow = dateFormat.call(event["start"], startformat) + " - " + dateFormat.call(event["end"], endformat);
			//var linebreak = ($.browser.mozilla?"":"\r\n");
			var linebreak = "\r\n";
			var ret = [];
			if (event["is_allday"] == 1) {
				//ret.push("[" + i18n.xgcalendar.allday_event + "]", linebreak );
			}
			else {
				if (event["is_recurring"] == 1) {
					ret.push("[" + i18n.xgcalendar.repeat_event + "]", linebreak);
				}
			}
			ret.push(i18n.xgcalendar.time + ": ", timeshow, linebreak, i18n.xgcalendar.event + ": ", eventshow);

			if (event["location"] != undefined && event["location"] != "") {
				ret.push(linebreak, i18n.xgcalendar.location + ": ", event["location"]);
			}

			if (event["attendees"] != undefined && event["attendees"] != "") {
				ret.push(linebreak, i18n.xgcalendar.participant + ": ", event["attendees"]);
			}
			return ret.join("");
		}

		function BuildDayEvent(color, e, index) {
			var theme = ColorCalcValues(color);
			var p = { bdcolor:theme[0], bgcolor2:theme[0], bgcolor1:theme[2], width:"70%", icon:"", title:"", data:"" };
			p.starttime = pZero(e.st.hour) + ":" + pZero(e.st.minute);
			p.endtime = pZero(e.et.hour) + ":" + pZero(e.et.minute);
			p.content = e.event["subject"];
			p.title = getTitle(e.event);
			var icons = [];
			if (e.event["has_notification"] == 1) icons.push("<I class=\"cic cic-tmr\">&nbsp;</I>");
			if (e.reevent) {
				icons.push("<I class=\"cic cic-spcl\">&nbsp;</I>");
			}
			p.icon = icons.join("");
			var sP = gP(e.st.hour, e.st.minute);
			var eP = gP(e.et.hour, e.et.minute);
			p.top = sP + "px";
			p.left = (e.left * 100) + "%";
			p.width = (e.aQ * 100) + "%";
			p.height = (eP - sP - 4);
			p.i = index;
			if (option.enableDrag && e.event["is_editable_quick"] == 1) {
				p.drag = "drag";
				p.redisplay = "block";
			}
			else {
				p.drag = "";
				p.redisplay = "none";
			}

			p.addclasses = (e.event["is_editable_quick"] ? "editable" : "not_editable");

			var $newtemp = $(Tp(__SCOLLEVENTTEMP, p));
			$newtemp.data("eventdata", $.extend(true, {}, e.event));

			return $newtemp;
		}

		//get body height in month view
		function GetMonthViewBodyHeight() {
			return option.height;
		}

		function GetMonthViewHeaderHeight() {
			return 21;
		}

		function BuilderMonthBody(showday, startday, events, bodyHeight) {
			var i, j, k, b, day;

			var htb = [];
			var firstdate = new Date(showday.getFullYear(), showday.getMonth(), 1);
			var diffday = startday - firstdate.getDay();
			var showmonth = showday.getMonth();
			if (diffday > 0) {
				diffday -= 7;
			}
			var startdate = DateAdd("d", diffday, firstdate);
			var enddate = DateAdd("d", 34, startdate);
			var rc = 5;

			if (enddate.getFullYear() == showday.getFullYear() && enddate.getMonth() == showday.getMonth() && enddate.getDate() < __MonthDays[showmonth]) {
				enddate = DateAdd("d", 7, enddate);
				rc = 6;
			}
			option.vstart = startdate;
			option.vend = enddate;
			option.datestrshow = CalDateShow(startdate, enddate);
			bodyHeight = bodyHeight - 18 * rc;
			var rowheight = bodyHeight / rc;
			var roweventcount = parseInt(rowheight / 21);
			if (rowheight % 21 > 15) {
				roweventcount++;
			}
			var p = 100 / rc;
			var formatevents = [];
			var hastdata = formartEventsInHashtable(events, startday, 7, startdate, enddate);
			var B = [];
			var C = [];
			for (j = 0; j < rc; j++) {
				k = 0;
				formatevents[j] = b = [];
				for (i = 0; i < 7; i++) {
					var newkeyDate = DateAdd("d", j * 7 + i, startdate);
					C[j * 7 + i] = newkeyDate;
					var newkey = dateFormat.call(newkeyDate, i18n.xgcalendar.dateformat.fulldaykey);
					b[i] = hastdata[newkey];
					if (b[i] && b[i].length > 0) {
						k += b[i].length;
					}
				}
				B[j] = k;
			}
			eventDiv.data("mvdata", formatevents);
			for (j = 0; j < rc; j++) {
				//onclick=\"javascript:FunProxy('rowhandler',event,this);\"
				htb.push("<div style=\"HEIGHT:", p, "%; TOP:", p * j, "%\" data-row=\"" + j + "\" class=\"month-row mvrow_" + j + "\">");
				htb.push("<table class=\"st-bg-table\"><tbody><tr>");

				for (i = 0; i < 7; i++) {
					day = C[j * 7 + i];
					htb.push("<td data-abbr='", day.getTime(), "' data-ch='qkadd' data-axis='00:00' title=''");

					if (formatDate(day) == formatDate(new Date())) {
						htb.push(" class=\"st-bg st-bg-today\">");
					} else if (day.getMonth() != showmonth) {
						htb.push(" class=\"st-bg st-bg-nonmonth\">");
					} else {
						htb.push(" class=\"st-bg\">");
					}
					htb.push("&nbsp;</td>");
				}
				//bgtable
				htb.push("</tr></tbody></table>");

				//stgrid
				htb.push("<table class=\"st-grid row" + j + "\"><tbody>");

				//title tr
				htb.push("<tr>");
				var titletemp = "<td class=\"st-dtitle{titleClass}\" data-ch='qkadd' data-abbr='{abbr}' data-axis='00:00' title=\"{title}\"><span class='monthdayshow'>{dayshow}</span></a></td>";

				for (i = 0; i < 7; i++) {
					var o = { titleClass:"", dayshow:"" };
					day = C[j * 7 + i];
					if (formatDate(day) == formatDate(new Date())) {
						o.titleClass = " st-dtitle-today";
					}
					if (day.getMonth() != showmonth) {
						o.titleClass = " st-dtitle-nonmonth";
					}
					o.title = formatDate(day);
					if (day.getDate() == 1) {
						if (day.getMonth == 0) {
							o.dayshow = formatDate(day);
						}
						else {
							o.dayshow = dateFormat.call(day, option.date_format_dm2).toString();
						}
					}
					else {
						o.dayshow = day.getDate();
					}
					o.abbr = day.getTime();
					htb.push(Tp(titletemp, o));
				}
				htb.push("</tr>");
				htb.push("</tbody></table>");
				//month-row
				htb.push("</div>");
			}
			var $ret = $(htb.join(""));

			for (j = 0; j < rc; j++) {
				var sfirstday = C[j * 7];
				var dMax = B[j];
				var obs = BuildMonthRow(formatevents[j], dMax, roweventcount, sfirstday);
				for (i = 0; i < obs.length; i++) $ret.find(".row" + j).append(obs[i]);
				//htb=htb.concat(rowHtml); rowHtml = null;

			}
			return $ret;
		}

		//formate datetime 
		function formartEventsInHashtable(events, startday, daylength, rbdate, redate) {
			var key;
			var hast = new Object();
			var l = events.length;
			for (var i = 0; i < l; i++) {
				var sD = events[i]["start"];
				var eD = events[i]["end"];
				var diff = DateDiff("d", sD, eD);
				var s = {};
				s.event = events[i];
				s.day = sD.getDate();
				s.year = sD.getFullYear();
				s.month = sD.getMonth() + 1;
				s.allday = events[i]["is_allday"] == 1;
				s.crossday = events[i]["is_moredays"] == 1;
				s.reevent = events[i]["is_recurring"] == 1; //Recurring event
				s.daystr = s.year + "/" + s.month + "/" + s.day;
				s.st = {};
				s.st.hour = sD.getHours();
				s.st.minute = sD.getMinutes();
				s.st.p = s.st.hour * 60 + s.st.minute; // start time position
				s.et = {};
				s.et.hour = eD.getHours();
				s.et.minute = eD.getMinutes();
				s.et.p = s.et.hour * 60 + s.et.minute; // end time postition

				if (diff > 0) {
					if (sD < rbdate) { //start date out of range
						sD = rbdate;
					}
					if (eD > redate) { //end date out of range
						eD = redate;
					}
					var f = startday - sD.getDay();
					if (f > 0) {
						f -= daylength;
					}
					var sdtemp = DateAdd("d", f, sD);
					for (; sdtemp <= eD; sD = sdtemp = DateAdd("d", daylength, sdtemp)) {
						var d = $.extend(s, {});
						key = dateFormat.call(sD, i18n.xgcalendar.dateformat.fulldaykey);
						var x = DateDiff("d", sdtemp, eD);
						if (hast[key] == null) {
							hast[key] = [];
						}
						d.colSpan = (x >= daylength) ? daylength - DateDiff("d", sdtemp, sD) : DateDiff("d", sD, eD) + 1;
						hast[key].push(d);
						d = null;
					}
				}
				else {
					key = dateFormat.call(events[i]["start"], i18n.xgcalendar.dateformat.fulldaykey);
					if (hast[key] == null) {
						hast[key] = [];
					}
					s.colSpan = 1;
					hast[key].push(s);
				}
				s = null;
			}
			return hast;
		}

		function BuildMonthRow(events, dMax, sc, day) {
			var j, e, m,
				x = [],
				y = [],
				z = [],
				cday = [];
			var l = events.length;
			var el = 0;
			var ret = [];
			for (j = 0; j < l; j++) {
				x.push(0);
				y.push(0);
				z.push(0);
				cday.push(DateAdd("d", j, day));
			}
			for (j = 0; j < l; j++) {
				var ec = events[j] ? events[j].length : 0;
				y[j] += ec;
				for (var k = 0; k < ec; k++) {
					e = events[j][k];
					if (e && e.colSpan > 1) {
						for (m = 1; m < e.colSpan; m++) {
							y[j + m]++;
						}
					}
				}
			}

			var tdtemp = "<td class='{cssclass}' data-axis='{axis}' data-ch='{ch}' data-abbr='{abbr}' title='{title}' {otherAttr}>{html}</td>";
			for (j = 0; j < sc && el < dMax; j++) {
				var $row = $("<tr></tr>");
				//var gridtr = $(__TRTEMP);
				for (var h = 0; h < l;) {
					e = events[h] ? events[h][x[h]] : undefined;
					var $ev = null;
					var tempdata = { "class":"", axis:"", ch:"", title:"", abbr:"", html:"", otherAttr:"", click:"javascript:void(0);" };
					var tempCss = ["st-c"];

					if (e) {
						x[h] = x[h] + 1;
						//last event of the day
						var bs = false;
						if (z[h] + 1 == y[h] && e.colSpan == 1) {
							bs = true;
						}
						if (!bs && j == (sc - 1) && z[h] < y[h]) {
							el++;
							$.extend(tempdata, { "axis":h, ch:"more", "abbr":cday[h].getTime(), html:i18n.xgcalendar.others + (y[h] - z[h]) + i18n.xgcalendar.item, click:"javascript:alert('more event');" });
							tempCss.push("st-more st-moreul");
							h++;
						}
						else {
							tempdata.html = "";
							$ev = BuildMonthDayEvent(e, cday[h], l - h);
							tempdata.ch = "show";
							if (e.colSpan > 1) {
								tempdata.otherAttr = " colSpan='" + e.colSpan + "'";
								for (m = 0; m < e.colSpan; m++) {
									z[h + m] = z[h + m] + 1;
								}
								h += e.colSpan;

							}
							else {
								z[h] = z[h] + 1;
								h++;
							}
							el++;
						}
					}
					else {
						if (j == (sc - 1) && z[h] < y[h] && y[h] > 0) {
							$.extend(tempdata, { "axis":h, ch:"more", "abbr":cday[h].getTime(), html:i18n.xgcalendar.others + (y[h] - z[h]) + i18n.xgcalendar.item, click:"javascript:alert('more event');" });
							tempCss.push("st-more st-moreul");
							h++;
						}
						else {
							$.extend(tempdata, { html:"&nbsp;", ch:"qkadd", "axis":"00:00", "abbr":cday[h].getTime(), title:"" });
							tempCss.push("st-s");
							h++;
						}
					}
					tempdata.cssclass = tempCss.join(" ");
					tempCss = null;
					var $z = $(Tp(tdtemp, tempdata));
					if ($ev != null) $z.append($ev);
					$row.append($z);
					tempdata = null;
				}
				ret.push($row);
			}
			return ret;
		}

		function BuildMonthDayEvent(e, cday, length) {
			var theme;
			if (e.event["color"] && e.event["color"].match(/^#[0-9a-f]{6}$/i)) {
				theme = ColorCalcValues(e.event["color"]);
			}
			else {
				theme = ColorCalcValues(option.std_color);
			}
			var p = { color:theme[2], title:"", extendClass:"", extendHTML:"", data:"" };

			p.title = getTitle(e.event);
			p.id = "bbit_cal_event_" + e.event["uri"];
			if (option.enableDrag && e.event["is_editable_quick"] == 1) {
				p.eclass = "drag";
			}
			else {
				p.eclass = "cal_" + e.event["uri"];
			}
			p.eclass += " " + (e.event["is_editable"] ? "editable" : "not_editable");
			var sp = "<span style=\"cursor: pointer\">{content}</span>";
			var i = "<I class=\"cic cic-tmr\">&nbsp;</I>";
			var i2 = "<I class=\"cic cic-rcr\">&nbsp;</I>";
			var ml = "<div class=\"st-ad-ml\"></div>";
			var mr = "<div class=\"st-ad-mr\"></div>";
			var arrm = [];
			var sf = e.event["start"] < cday;
			var ef = DateDiff("d", cday, e.event["end"]) >= length;  //e.event["end"] >= DateAdd("d", 1, cday);
			if (sf || ef) {
				if (sf) {
					arrm.push(ml);
					p.extendClass = "st-ad-mpad ";
				}
				if (ef) {
					arrm.push(mr);
				}
				p.extendHTML = arrm.join("");

			}
			var cen;
			if (!e.allday && !sf) {
				cen = pZero(e.st.hour) + ":" + pZero(e.st.minute) + " " + e.event["subject"];
			}
			else {
				cen = e.event["subject"];
			}
			var content = [];
			if (cen.indexOf("Geburtstag:") == 0) {
				content.push("<img src='/pics/silk/cake.png' alt='Geburtstag: ' title='Geburtstag' style='height: 12px; margin-right: 3px;'>");
				cen = cen.replace(/Geburtstag: /, "");
			}
			content.push(Tp(sp, { content:cen }));
			if (e.event["has_notification"] == 1) content.push(i);
			if (e.reevent) {
				content.push(i2);
			}
			p.content = content.join("");
			var $newel = $(Tp(__ALLDAYEVENTTEMP, p));
			$newel.data("eventdata", e.event);
			return $newel;
		}

		//to populate the data 
		function populate() {
			if (option.isloading) {
				return true;
			}
			if (option.url && option.url != "") {
				option.isloading = true;
				//clearcontainer();
				if (option.onBeforeRequestData && $.isFunction(option.onBeforeRequestData)) {
					option.onBeforeRequestData(1);
				}
				var param = [
					{ name:"showdate", value: Math.floor(option.showday.getTime() / 1000) },
					{ name:"viewtype", value:option.view },
					{ name:"weekstartday", value:option.weekstartday }
				];
				if (option.view == "multi_days") {
					param.push({ name:"num_days", value:option.num_days });
				}
				if (option.extParam) {
					for (var pi = 0; pi < option.extParam.length; pi++) {
						param[param.length] = option.extParam[pi];
					}
				}

				$.ajax({
					type:option.method, //
					url:option.url + option.url_add,
					data:param,
					dataType:"json",
					dataFilter:function (data) {
						//return data.replace(/"\\\/(Date\([0-9-]+\))\\\/"/gi, "new $1");

						return data;
					},
					success:function (data) {//function(datastr) {									
						//datastr =datastr.replace(/"\\\/(Date\([0-9-]+\))\\\/"/gi, 'new $1');						
						//var data = (new Function("return " + datastr))();
						if (data != null && data.error != null) {
							if (option.onRequestDataError) {
								option.onRequestDataError(1, data);
							}
						}
						else {
							data["start"] = parseDate(data["start"]);
							data["end"] = parseDate(data["end"]);
							$.each(data.events, function (index, value) {
								value["start"] = new Date(value["start"] * 1000);
								value["end"] = new Date(value["end"] * 1000);
							});
							responseData(data, data.start, data.end);
						}
						if (option.onAfterRequestData && $.isFunction(option.onAfterRequestData)) {
							option.onAfterRequestData(1);
						}
						option.isloading = false;
					},
					error:function (data) {
						try {
							if (option.onRequestDataError) {
								option.onRequestDataError(1, data);
							} else {
								alert(i18n.xgcalendar.get_data_exception);
							}
							if (option.onAfterRequestData && $.isFunction(option.onAfterRequestData)) {
								option.onAfterRequestData(1);
							}
							option.isloading = false;
						} catch (e) {
						}
					}
				});
			}
			else {
				alert("url" + i18n.xgcalendar.i_undefined);
			}
			return true;
		}

		function responseData(data, start, end) {
			var events = data.events;
			ConcatEvents(events, start, end);
			render();

		}

		function clearrepeat(events, start) {
			var jl = events.length;
			if (jl > 0) {
				var es = events[0]["start"];
				var el = events[jl - 1]["start"];
				for (var i = 0, l = option.eventItems.length; i < l; i++) {

					if (option.eventItems[i]["sart"] > el || jl == 0) {
						break;
					}
					if (option.eventItems[i]["start"] >= es) {
						for (var j = 0; j < jl; j++) {
							if (option.eventItems[i]["uri"] == events[j]["uri"] && option.eventItems[i]["start"] < start) {
								events.splice(j, 1); //for duplicated event
								jl--;
								break;
							}
						}
					}
				}
			}
		}

		function ConcatEvents(events, start, end) {
			var e, s;
			if (!events) {
				events = [];
			}
			if (events) {
				if (option.eventItems.length == 0) {
					option.eventItems = events;
				}
				else {
					//remove duplicated one
					clearrepeat(events, start);
					var sl = option.eventItems.length;
					var sI = -1;
					var eI = sl;
					s = start;
					e = end;
					if (option.eventItems[0]["start"] > e) {
						option.eventItems = events.concat(option.eventItems);
						return;
					}
					if (option.eventItems[sl - 1]["start"] < s) {
						option.eventItems = option.eventItems.concat(events);
						return;
					}
					for (var i = 0; i < sl; i++) {
						if (option.eventItems[i]["start"] >= s && sI < 0) {
							sI = i;
							continue;
						}
						if (option.eventItems[i]["start"] > e) {
							eI = i;
							break;
						}
					}

					var e1 = sI <= 0 ? [] : option.eventItems.slice(0, sI);
					var e2 = eI == sl ? [] : option.eventItems.slice(eI);
					option.eventItems = [].concat(e1, events, e2);
				}
			}
		}

		//utils goes here
		function weekormonthtoday(e) {
			var th = $(this);
			option.showday = new Date(parseInt(th.data("abbr")));
			option.view = "day";
			render();
			if (option.onWeekOrMonthToDay) {
				option.onWeekOrMonthToDay(option);
			}
			e.stopPropagation();
			e.preventDefault();
		}

		function parseDate(str) {
			return new Date(Date.parse(str));
		}

		function gP(h, m) {
			return h * option.hour_height + parseInt(m / 60 * option.hour_height);
		}

		function gW(ts1, ts2) {
			var t1 = ts1 / option.hour_height;
			var t2 = parseInt(t1);
			var t3 = t1 - t2 >= 0.5 ? 30 : 0;
			var t4 = ts2 / option.hour_height;
			var t5 = parseInt(t4);
			var t6 = t4 - t5 >= 0.5 ? 30 : 0;
			return { sh:t2, sm:t3, eh:t5, em:t6, h:ts2 - ts1 };
		}

		function gH(y1, y2, pt) {
			var sy1 = Math.min(y1, y2);
			var sy2 = Math.max(y1, y2);
			var t1 = (sy1 - pt) / option.hour_height;
			var t2 = parseInt(t1);
			var t3 = t1 - t2 >= 0.5 ? 30 : 0;
			var t4 = (sy2 - pt) / option.hour_height;
			var t5 = parseInt(t4);
			var t6 = t4 - t5 >= 0.5 ? 30 : 0;
			return { sh:t2, sm:t3, eh:t5, em:t6, h:sy2 - sy1 };
		}

		function pZero(n) {
			return n < 10 ? "0" + n : "" + n;
		}

		function Tp(temp, dataarry) {
			return temp.replace(/\{([\w]+)\}/g, function (s1, s2) {
				var s = dataarry[s2];
				if (typeof (s) != "undefined") {
					return s;
				} else {
					return s1;
				}
			});
		}

		function fomartTimeShow(h) {
			return h < 10 ? "0" + h + ":00" : h + ":00";
		}

		function getymformat(date, comparedate, isshowtime, isshowweek) {
			var showyear = isshowtime != undefined ? (date.getFullYear() != new Date().getFullYear()) : true;
			var showmonth = true;
			var showday = true;
			var showtime = isshowtime || false;
			var showweek = isshowweek || false;
			if (comparedate) {
				showyear = comparedate.getFullYear() != date.getFullYear();
				//showmonth = comparedate.getFullYear() != date.getFullYear() || date.getMonth() != comparedate.getMonth();
				if (comparedate.getFullYear() == date.getFullYear() &&
					date.getMonth() == comparedate.getMonth() &&
					date.getDate() == comparedate.getDate()
					) {
					showyear = showmonth = showday = showweek = false;
				}
			}

			var a = [];
			if (showyear) {
				a.push(option.date_format_dm3)
			} else if (showmonth) {
				a.push(option.date_format_dm2)
			} else if (showday) {
				a.push(i18n.xgcalendar.dateformat.day);
			}
			a.push(showweek ? " (W)" : "", showtime ? " HH:mm" : "");
			return a.join("");
		}

		function CalDateShow(startday, endday, isshowtime, isshowweek) {
			if (!endday) {
				return dateFormat.call(startday, getymformat(startday, null, isshowtime));
			} else {
				var strstart = dateFormat.call(startday, getymformat(startday, null, isshowtime, isshowweek));
				var strend = dateFormat.call(endday, getymformat(endday, startday, isshowtime, isshowweek));
				var join = (strend != "" ? " - " : "");
				return [strstart, strend].join(join);
			}
		}

		function buildtempdayevent(sh, sm, eh, em, h, title, w, resize, color) {
			if (!color.match(/^#[0-9a-f]{6}$/i)) color = option.std_color;
			var t = ColorCalcValues(color);
			return Tp(__SCOLLEVENTTEMP, {
				bdcolor:t[0],
				bgcolor2:t[1],
				bgcolor1:t[2],
				data:"",
				starttime:[pZero(sh), pZero(sm)].join(":"),
				endtime:[pZero(eh), pZero(em)].join(":"),
				content:title ? title : i18n.xgcalendar.new_event,
				title:title ? title : i18n.xgcalendar.new_event,
				icon:"<I class=\"cic cic-tmr\">&nbsp;</I>",
				top:"0px",
				left:"",
				width:w ? w : "100%",
				height:h - 4,
				i:"-1",
				drag:"drag-chip",
				redisplay:resize ? "block" : "none"
			});
		}

		function quickd(type) {
			$("#bbit-cs-buddle").css("visibility", "hidden");
			var calid = $("#bbit-cs-id").val();
			var param = [
				{ "name":"calendarId", value:calid },
				{ "name":"type", value:type}
			];
			var de = rebyKey(calid, true);
			option.onBeforeRequestData && option.onBeforeRequestData(3);
			$.post(option.quickDeleteUrl, param, function (data) {
				if (data) {
					$(document).trigger("wdcal:updated");
					if (data["IsSuccess"]) {
						de = null;
						populate();
						option.onAfterRequestData && option.onAfterRequestData(3);
					}
					else {
						option.onRequestDataError && option.onRequestDataError(3, data);
						Ind(de);
						render();
						option.onAfterRequestData && option.onAfterRequestData(3);
					}
				}
			}, "json");
		}

		function getbuddlepos(x, y) {
			var tleft = x - 110;
			var ttop = y - 217;
			var maxLeft = document.documentElement.clientWidth;
			var maxTop = document.documentElement.clientHeight;
			var ishide = false;
			if (tleft <= 0 || ttop <= 0 || tleft + 400 > maxLeft) {
				tleft = x - 200 <= 0 ? 10 : x - 200;
				ttop = y - 159 <= 0 ? 10 : y - 159;
				if (tleft + 400 >= maxLeft) {
					tleft = maxLeft - 410;
				}
				if (ttop + 164 >= maxTop) {
					ttop = maxTop - 165;
				}
				ishide = true;
			}
			return { left:tleft, top:ttop, hide:ishide };
		}

		function dayshow(e, data) {
			var $t = $(e.target);
			if ($t.hasClass("axx_username") || $t.parents(".axx_username").length > 0 || $t.hasClass("cal_nojs") || $t.parents(".cal_nojs").length > 0) return false;

			if (data == undefined) {
				if ($t.hasClass("chip") || $t.hasClass("rb-o")) data = $t.data("eventdata");
				else data = $t.parents(".chip, .rb-o").data("eventdata");
			}

			if (data != null) {
				var editable = false;
				if (option.quickDeleteUrl != "" && data["is_editable"] == 1 && option.readonly != true) editable = true;
				var csbuddle = '<div id="bbit-cs-buddle" style="z-index: 180; width: 400px;visibility:hidden;" class="bubble"><table class="bubble-table"><tbody' +
					'><tr><td class="bubble-cell-side"><div id="tl1" class="bubble-corner"><div class="bubble-sprite bubble-tl"></div></div>' +
					'<td class="bubble-cell-main"><div class="bubble-top"></div><td class="bubble-cell-side"><div id="tr1" class="bubble-corner"><div class="bubble-sprite bubble-tr"></div></div>' +
					'<tr><td class="bubble-mid" colSpan="3"><div style="overflow: hidden" id="bubbleContent1"><div><div></div><div class="cb-root">' +
					'<table class="cb-table"><tbody><tr><td class="cb-value"><div class="textbox-fill-wrapper"><div class="textbox-fill-mid">' +
					'<a href="" title="' + i18n.xgcalendar.click_to_detail + '" class="bbit-cs-what textbox-fill-div lk"></a></div></div></td></tr>' +
					'<tr><td class=cb-value><div id="bbit-cs-buddle-timeshow"></div></td></tr>' +
					'</tbody></table><div class="bbit-cs-split"><input id="bbit-cs-id" type="hidden" value=""/>[ <span id="bbit-cs-delete" class="lk">'
					+ i18n.xgcalendar.i_delete + '</span> ]&nbsp;' +
					'<a href="" class="bbit-cs-editLink lk">' + i18n.xgcalendar.update_detail + ' <strong>&gt;&gt;</strong></a>' +
					'</div></div></div></div><tr><td><div id="bl1" class="bubble-corner"><div class="bubble-sprite bubble-bl"></div></div>' +
					'<td><div class="bubble-bottom"></div><td><div id="br1" class="bubble-corner"><div class="bubble-sprite bubble-br"></div></div></tr></tbody>' +
					'</table><div id="bubbleClose2" class="bubble-closebutton"></div><div id="prong1" class="prong"><div class=bubble-sprite></div></div></div>';
				var $bud = $("#bbit-cs-buddle");
				if ($bud.length == 0) {
					$bud = $(csbuddle).appendTo(document.body);
					var calbutton = $("#bbit-cs-delete");
					$("#bubbleClose2").on("click", function () {
						$("#bbit-cs-buddle").css("visibility", "hidden");
					});
					calbutton.on("click", function () {
						var data = $("#bbit-cs-buddle").data("cdata");
						if (option.quickDeleteHandler && $.isFunction(option.quickDeleteHandler)) {
							option.quickDeleteHandler.call(this, data, quickd);
						}
						else {
							if (confirm(i18n.xgcalendar.confirm_delete_event)) {
								var s = 0; //0 single event , 1 for Recurring event
								if (data["is_recurring"] == 1) {
									if (confirm(i18n.xgcalendar.confrim_delete_event_or_all)) {
										s = 0;
									}
									else {
										s = 1;
									}
								}
								else {
									s = 0;
								}
								quickd(s);
							}
						}
					});
				}

				if (editable) {
					$("#bbit-cs-delete").parents(".bbit-cs-split").show();
					$bud.find(".bbit-cs-editLink").attr("href", data["url_edit"]).show();
				}
				else {
					$("#bbit-cs-delete").parents(".bbit-cs-split").hide();
					$bud.find(".bbit-cs-editLink").hide();
				}

				var pos = getbuddlepos(e.pageX, e.pageY);
				if (pos.hide) {
					$("#prong1").hide()
				}
				else {
					$("#prong1").show()
				}
				var ss = [];
				var iscos = DateDiff("d", data["start"], data["end"]) != 0;
				ss.push(dateFormat.call(data["start"], option.date_format_dm2), " (", __WDAY[data["start"].getDay()], ")");
				if (data["is_allday"] != 1) {
					ss.push(",", dateFormat.call(data["start"], "HH:mm"));
				}

				if (iscos) {
					ss.push(" - ", dateFormat.call(data["end"], option.date_format_dm2), " (", __WDAY[data["end"].getDay()], ")");
					if (data["is_allday"] != 1) {
						ss.push(",", dateFormat.call(data["end"], "HH:mm"));
					}
				}
				var location = "";
				if (data["location"] != "") location = data["location"] + ", ";
				$("#bbit-cs-buddle-timeshow").html(location + ss.join(""));
				$bud.find(".bbit-cs-what").html(data["subject"]).attr("href", data["url_detail"]);
				$("#bbit-cs-id").val(data["uri"]);
				$bud.data("cdata", data);
				$bud.css({ "visibility":"visible", left:pos.left, top:pos.top });

				$(document).one("click", function () {
					$("#bbit-cs-buddle").css("visibility", "hidden");
				});
			}
			else {
				alert(i18n.xgcalendar.data_format_error);
			}
			return false;
		}

		function moreshow(mv) {
			var $me = $(this);
			var $pdiv = $(mv);
			var divIndex = parseInt($pdiv.data("row"));
			var offsetMe = $me.position();
			var offsetP = $pdiv.position();
			var width = ($me.width() + 2) * 1.5;
			var top = offsetP.top + 15;
			var left = offsetMe.left;

			var day = new Date(parseInt($me.data("abbr")));
			var cc = $gridcontainer.find(".cal-month-cc");
			var ccontent = $gridcontainer.find(".cal-month-cc-content table tbody");
			var ctitle = $gridcontainer.find(".cal-month-cc-title");
			ctitle.html(formatDate(day));
			ccontent.empty();
			var edata = $("#gridEvent").data("mvdata");
			var events = edata[divIndex];
			var index = parseInt($me.data("axis"));
			ccontent.find("*").remove();
			for (var i = 0; i <= index; i++) {
				var ec = events[i] ? events[i].length : 0;
				for (var j = 0; j < ec; j++) {
					var e = events[i][j];
					if (e) {
						if ((e.colSpan + i - 1) >= index) {
							var $x = $("<tr><td class='st-c'></td></tr>");
							var $y = BuildMonthDayEvent(e, day, 1);
							$x.find(".st-c").append($y);
							ccontent.append($x);
						}
					}
				}
			}
			//click
			ccontent.find("div.rb-o").each(function () {
				$(this).click(dayshow);
			});

			var height = cc.height();
			var maxleft = document.documentElement.clientWidth;
			var maxtop = document.documentElement.clientHeight;
			if (left + width >= maxleft) {
				left = offsetMe.left - ($me.width() + 2) * 0.5;
			}
			if (top + height >= maxtop) {
				top = maxtop - height - 2;
			}
			var newOff = { left:left, top:top, "z-index":180, width:width, "visibility":"visible" };
			cc.css(newOff);
			$(document).on("click", closeCc);
			return false;
		}

		function dayupdate(data, start, end) {
			if (option.quickUpdateUrl != "" && data["is_editable_quick"] == 1 && option.readonly != true) {
				if (option.isloading) {
					return false;
				}
				option.isloading = true;
				var id = data["uri"];
				var os = data["start"];
				var od = data["end"];
				var param = [
					{ "name":"calendarId", value:id },
					{ "name":"CalendarStartTime", value:Math.floor(start.getTime() / 1000) },
					{ "name":"CalendarEndTime", value:Math.floor(end.getTime() / 1000) }
				];
				var d;
				if (option.quickUpdateHandler && $.isFunction(option.quickUpdateHandler)) {
					option.quickUpdateHandler.call(this, param);
				}
				else {
					option.onBeforeRequestData && option.onBeforeRequestData(4);
					$.post(option.quickUpdateUrl, param, function (data) {
						if (data) {
							$(document).trigger("wdcal:updated");
							if (data["IsSuccess"] == true) {
								option.isloading = false;
								option.onAfterRequestData && option.onAfterRequestData(4);
							}
							else {
								option.onRequestDataError && option.onRequestDataError(4, data);
								option.isloading = false;
								d = rebyKey(id, true);
								d["start"] = os;
								d["end"] = od;
								Ind(d);
								render();
								d = null;
								option.onAfterRequestData && option.onAfterRequestData(4);
							}
						}
					}, "json");
					d = rebyKey(id, true);
					if (d) {
						d["start"] = start;
						d["end"] = end;
					}
					Ind(d);
					render();
				}
			}
			return false;
		}

		function quickadd(start, end, isallday, pos) {
			if ((!option.quickAddHandler && option.quickAddUrl == "") || option.readonly) {
				return false;
			}
			var buddle = $("#bbit-cal-buddle");
			if (buddle.length == 0) {
				var temparr = [];
				temparr.push('<form id="bbit-cal-submitFORM">');
				temparr.push('<div id="bbit-cal-buddle" style="z-index: 180; width: 400px;visibility:hidden;" class="bubble">');
				temparr.push('<table class="bubble-table"><tbody><tr><td class="bubble-cell-side"><div id="tl1" class="bubble-corner"><div class="bubble-sprite bubble-tl"></div></div>');
				temparr.push('<td class="bubble-cell-main"><div class="bubble-top"></div><td class="bubble-cell-side"><div id="tr1" class="bubble-corner"><div class="bubble-sprite bubble-tr"></div></div>  <tr><td class="bubble-mid" colSpan="3"><div style="overflow: hidden" id="bubbleContent1"><div><div></div><div class="cb-root">');
				temparr.push('<table class="cb-table"><tbody><tr><th class="cb-key">');
				temparr.push(i18n.xgcalendar.time, ':</th><td class=cb-value><div id="bbit-cal-buddle-timeshow"></div></td></tr><tr><th class="cb-key">');
				temparr.push(i18n.xgcalendar.content, ':</th><td class="cb-value"><div class="textbox-fill-wrapper"><div class="textbox-fill-mid"><input id="bbit-cal-what" class="textbox-fill-input"/></div></div><div class="cb-example">');
				temparr.push(i18n.xgcalendar.example, '</div></td></tr></tbody></table><input id="bbit-cal-start" type="hidden"/><input id="bbit-cal-end" type="hidden"/><input id="bbit-cal-allday" type="hidden"/><input id="bbit-cal-quickAddBTN" value="');
				temparr.push(i18n.xgcalendar.create_event, '" type="submit"/>&nbsp; <SPAN id="bbit-cal-editLink" class="lk">');
				temparr.push(i18n.xgcalendar.update_detail, ' <StrONG>&gt;&gt;</StrONG></SPAN></div></div></div><tr><td><div id="bl1" class="bubble-corner"><div class="bubble-sprite bubble-bl"></div></div><td><div class="bubble-bottom"></div><td><div id="br1" class="bubble-corner"><div class="bubble-sprite bubble-br"></div></div></tr></tbody></table><div id="bubbleClose1" class="bubble-closebutton"></div><div id="prong2" class="prong"><div class=bubble-sprite></div></div></div>');
				temparr.push('</form>');
				var tempquickAddHanler = temparr.join("");
				temparr = null;
				$(document.body).append(tempquickAddHanler);
				buddle = $("#bbit-cal-buddle");
				$("#bubbleClose1").click(function () {
					$("#bbit-cal-buddle").css("visibility", "hidden");
					releasedragevent();
				});
				$("#bbit-cal-submitFORM").keyup(function (e) {
					if (e.which == 27) $("#bubbleClose1").click();
				});
				$("#bbit-cal-submitFORM").submit(function (e) {
					e.stopPropagation();
					e.preventDefault();
					if (option.isloading) {
						return false;
					}
					option.isloading = true;
					var what = $("#bbit-cal-what").val();
					var datestart = $("#bbit-cal-start").val();
					var dateend = $("#bbit-cal-end").val();
					var allday = $("#bbit-cal-allday").val();
					var f = /^[^\$<>]+$/.test(what);
					if (!f) {
						alert(i18n.xgcalendar.invalid_title);
						$("#bbit-cal-what").focus();
						option.isloading = false;
						return false;
					}
					var param = [
						{ "name":"CalendarTitle", value:what },
						{ "name":"CalendarStartTime", value: Math.floor(datestart / 1000)},
						{ "name":"CalendarEndTime", value: Math.floor(dateend / 1000)},
						{ "name":"IsAllDayEvent", value:allday }
					];

					if (option.extParam) {
						for (var pi = 0; pi < option.extParam.length; pi++) {
							param[param.length] = option.extParam[pi];
						}
					}

					if (option.quickAddHandler && $.isFunction(option.quickAddHandler)) {
						option.quickAddHandler.call(this, param);
						$("#bbit-cal-buddle").css("visibility", "hidden");
						releasedragevent();
					}
					else {
						$("#bbit-cal-buddle").css("visibility", "hidden");
						var tId = -1;
						option.onBeforeRequestData && option.onBeforeRequestData(2);

						var sd = new Date(datestart),
							ed = new Date(dateend),
							diff = DateDiff("d", sd, ed);
						var newdata = {
							"uri":"",
							"subject":what,
							"start":sd,
							"end":ed,
							"is_allday":(allday == "1" ? 1 : 0),
							"is_moredays":(diff > 0 ? 1 : 0),
							"is_recurring":0,
							"color":option.std_color,
							"is_editable":0,
							"is_editable_quick":0,
							"location":"",
							"attendees":""
						};
						tId = Ind(newdata);
						releasedragevent();
						render();

						$.post(option.quickAddUrl, param, function (data) {
							option.isloading = false;
							if (data) {
								if (data["IsSuccess"] == true) {
									populate();
									option.onAfterRequestData && option.onAfterRequestData(2);
								}
								else {
									option.onRequestDataError && option.onRequestDataError(2, data);
									option.onAfterRequestData && option.onAfterRequestData(2);
								}
								$(document).trigger("wdcal:updated");
							}

						}, "json");
					}
					return false;
				});
				buddle.mousedown(function (e) {
					e.stopPropagation();
					e.preventDefault();
				});
			}

			var dateshow = CalDateShow(start, end, !isallday, true);
			var off = getbuddlepos(pos.left, pos.top);
			if (off.hide) {
				$("#prong2").hide()
			}
			else {
				$("#prong2").show()
			}
			$("#bbit-cal-buddle-timeshow").html(dateshow);
			var calwhat = $("#bbit-cal-what").val("");
			$("#bbit-cal-allday").val(isallday ? "1" : "0");
			$("#bbit-cal-start").val(start.getTime());
			$("#bbit-cal-end").val(end.getTime());
			buddle.css({ "visibility":"visible", left:off.left, top:off.top });
			calwhat.blur().focus(); //add 2010-01-26 blur() fixed chrome 
			$(document).one("mousedown", function () {
				$("#bbit-cal-buddle").css("visibility", "hidden");
				releasedragevent();
			});
			return false;
		}

		function formatDate(time, format) {
			if (typeof(format) == "undefined") return $.datepicker.formatDate(option.date_format_full,time);
			var time2 = $.datepicker.formatDate(format, time);
			var h = time.getHours();
			var i = time.getMinutes();
			time2 = time2.replace("HH", (h > 10 ? "" : "0") + h);
			time2 = time2.replace("ii", (i > 10 ? "" : "0") + i);
			return time2;
		}

		function rebyKey(key, remove) {
			if (option.eventItems && option.eventItems.length > 0) {
				var sl = option.eventItems.length;
				var i = -1;
				for (var j = 0; j < sl; j++) {
					if (option.eventItems[j]["uri"] == key) {
						i = j;
						break;
					}
				}
				if (i >= 0) {
					var t = option.eventItems[i];
					if (remove) {
						option.eventItems.splice(i, 1);
					}
					return t;
				}
			}
			return null;
		}

		function Ind(event, i) {
			var d = 0;
			var j;
			if (!i) {
				if (option.eventItems && option.eventItems.length > 0) {
					var sl = option.eventItems.length;
					var s = event["start"];
					var d1 = s.getTime() - option.eventItems[0]["start"].getTime();
					var d2 = option.eventItems[sl - 1]["start"].getTime() - s.getTime();
					var diff = d1 - d2;
					if (d1 < 0 || diff < 0) {
						for (j = 0; j < sl; j++) {
							if (option.eventItems[j]["start"] >= s) {
								i = j;
								break;
							}
						}
					}
					else if (d2 < 0) {
						i = sl;
					}
					else {
						for (j = sl - 1; j >= 0; j--) {
							if (option.eventItems[j]["start"] < s) {
								i = j + 1;
								break;
							}
						}
					}
				}
				else {
					i = 0;
				}
			}
			else {
				d = 1;
			}
			if (option.eventItems && option.eventItems.length > 0) {
				if (i == option.eventItems.length) {
					option.eventItems.push(event);
				}
				else {
					option.eventItems.splice(i, d, event);
				}
			}
			else {
				option.eventItems = [event];
			}
			return i;
		}


		function ResizeView() {
			var _viewType = option.view;
			if (_viewType == "day" || _viewType == "week" || _viewType == "multi_days") {
				var $dvwkcontaienr = $gridcontainer.find(".wktopcontainer");
				var $dvtec = $gridcontainer.find(".scrolltimeevent");
				if ($dvwkcontaienr.length == 0 || $dvtec.length == 0) {
					alert(i18n.xgcalendar.view_no_ready);
					return;
				}
				var dvwkH = $dvwkcontaienr.height() + 2;
				var calH = option.height - 8 - dvwkH;
				$dvtec.height(calH);
				if (typeof (option.scroll) == "undefined") {
					var currentday = new Date();
					var h = currentday.getHours();
					var m = currentday.getMinutes();
					var th = gP(h, m);
					//var ch = $dvtec.attr("clientHeight");
					var ch = $dvtec.height();
					var sh = th - 0.5 * ch;
					//var ph = $dvtec.attr("scrollHeight");
					var ph = $dvtec.children().height();
					if (sh < 0) sh = 0;
					if (sh > ph - ch) sh = ph - ch - 10 * (23 - h);
					//$dvtec.attr("scrollTop", sh);
					$dvtec.scrollTop(sh);
				}
				else {
					$dvtec.scrollTop(option.scroll);
				}
			}
			else if (_viewType == "month") {
				//Resize GridContainer
			}
		}

		function returnfalse() {
			return false;
		}

		function initevents(viewtype) {
			if (viewtype == "week" || viewtype == "day" || viewtype == "multi_days") {
				$("div.chip", $gridcontainer).each(function () {
					var chip = $(this);
					chip.click(dayshow);
					if (chip.hasClass("drag")) {
						chip.mousedown(function (e) {
							dragStart.call(this, "std_item_move", e);
							e.stopPropagation();
							e.preventDefault();
						});
						//resize                      
						chip.find("div.resizer").mousedown(function (e) {
							dragStart.call($(this).parent().parent(), "std_item_resize", e);
							e.stopPropagation();
							e.preventDefault();
						});
					}
					else {
						chip.mousedown(returnfalse)
					}
				});
				$("div.rb-o", $gridcontainer).each(function () {
					var chip = $(this);
					chip.click(dayshow);
					if (chip.hasClass("drag") && (viewtype == "week" || viewtype == "multi_days")) {
						//drag;
						chip.mousedown(function (e) {
							dragStart.call(this, "fullday_item_move", e);
							e.stopPropagation();
							e.preventDefault();
						});
					}
					else {
						chip.mousedown(returnfalse)
					}
				});
				if (option.readonly == false) {
					$("td.tg-col", $gridcontainer).each(function () {
						$(this).mousedown(function (e) {
							dragStart.call(this, "std_empty_drag", e);
							e.stopPropagation();
							e.preventDefault();
						});
					});
					$gridcontainer.find(".weekViewAllDaywk").mousedown(function (e) {
						dragStart.call(this, "fullday_empty_drag", e);
						e.stopPropagation();
						e.preventDefault();
					});
				}

				if (viewtype == "week" || viewtype == "multi_days") {
					$gridcontainer.find(".wktopcontainer th.gcweekname").each(function () {
						$(this).click(weekormonthtoday);
					});
				}


			}
			else if (viewtype = "month") {
				$("div.rb-o", $gridcontainer).each(function () {
					var chip = $(this);
					chip.click(dayshow);
					if (chip.hasClass("drag")) {
						//drag;
						chip.mousedown(function (e) {
							dragStart.call(this, "std_item_month_drag", e);
							e.stopPropagation();
							e.preventDefault();
						});
					}
					else {
						chip.mousedown(returnfalse)
					}
				});
				$("td.st-more", $gridcontainer).each(function () {

					$(this).on("click", function (e) {
						moreshow.call(this, $(this).parent().parent().parent().parent()[0]);
						e.stopPropagation();
						e.preventDefault();
					}).on("mousedown", function (e) {
							e.stopPropagation();
							e.preventDefault();
						});
				});
				if (option.readonly == false) {
					$gridcontainer.find(".mvEventContainer").mousedown(function (e) {
						dragStart.call(this, "empty_month_drag", e);
						e.stopPropagation();
						e.preventDefault();
					});
				}
			}

		}

		function releasedragevent() {
			if (_dragevent) {
				_dragevent();
				_dragevent = null;
			}
		}

		function dragStart(type, e) {
			var w, h, offset, moffset, left, top, l, py, pw, xa, ya, i, data, fdi, dp, yl;
			var $obj = $(this);
			releasedragevent();
			switch (type) {
				case "std_empty_drag":
					_dragdata = { type:"std_empty_drag", target:$obj, sx:e.pageX, sy:e.pageY };
					break;
				case "fullday_empty_drag":
					w = $obj.width();
					h = $obj.height();
					offset = $obj.offset();
					left = offset.left;
					top = offset.top;
					l = option.view == "day" ? 1 : 7;
					py = w % l;
					pw = parseInt(w / l);
					if (py > l / 2 + 1) {
						pw++;
					}
					xa = [];
					ya = [];
					for (i = 0; i < l; i++) {
						xa.push({ s:i * pw + left, e:(i + 1) * pw + left });
					}
					ya.push({ s:top, e:top + h });
					_dragdata = { type:"fullday_empty_drag", target:$obj, sx:e.pageX, sy:e.pageY, pw:pw, xa:xa, ya:ya, h:h };
					w = left = l = py = pw = xa = null;
					break;
				case "std_item_move":
					var evid = $obj.parent().data("col");
					var p = $obj.parent();
					var pos = p.offset();
					w = p.width() + 10;
					h = $obj.height();
					data = $obj.data("eventdata");
					_dragdata = { type:"std_item_move", target:$obj, sx:e.pageX, sy:e.pageY,
						pXMin:pos.left, pXMax:pos.left + w, pw:w, h:h,
						cdi:parseInt(evid), fdi:parseInt(evid), data:data
					};
					break;
				case "std_item_resize":
					h = $obj.height();
					data = $obj.data("eventdata");
					_dragdata = { type:"std_item_resize", target:$obj, sx:e.pageX, sy:e.pageY, h:h, data:data };
					break;
				case "fullday_item_move":
					var con = $gridcontainer.find(".weekViewAllDaywk");
					w = con.width();
					h = con.height();
					offset = con.offset();
					moffset = $obj.offset();
					left = offset.left;
					top = offset.top;
					l = 7;
					py = w % l;
					pw = parseInt(w / l);
					if (py > l / 2 + 1) {
						pw++;
					}
					xa = [];
					ya = [];
					var di = 0;
					for (i = 0; i < l; i++) {
						xa.push({ s:i * pw + left, e:(i + 1) * pw + left });
						if (moffset.left >= xa[i].s && moffset.left < xa[i].e) {
							di = i;
						}
					}
					fdi = { x:di, y:0, di:di };
					ya.push({ s:top, e:top + h });
					data = $obj.data("eventdata");
					dp = DateDiff("d", data["start"], data["end"]) + 1;
					_dragdata = { type:"fullday_item_move", target:$obj, sx:e.pageX, sy:e.pageY, data:data, xa:xa, ya:ya, fdi:fdi, h:h, dp:dp, pw:pw };
					break;
				case "empty_month_drag":
					w = $obj.width();
					offset = $obj.offset();
					left = offset.left;
					top = offset.top;
					l = 7;
					yl = $obj.children().length;
					py = w % l;
					pw = parseInt(w / l);
					if (py > l / 2 + 1) {
						pw++;
					}
					h = $gridcontainer.find(".mvrow_0").height();
					xa = [];
					ya = [];
					for (i = 0; i < l; i++) {
						xa.push({ s:i * pw + left, e:(i + 1) * pw + left });
					}
					xa = [];
					ya = [];
					for (i = 0; i < l; i++) {
						xa.push({ s:i * pw + left, e:(i + 1) * pw + left });
					}
					for (i = 0; i < yl; i++) {
						ya.push({ s:i * h + top, e:(i + 1) * h + top });
					}
					_dragdata = { type:"empty_month_drag", target:$obj, sx:e.pageX, sy:e.pageY, pw:pw, xa:xa, ya:ya, h:h };
					break;
				case "std_item_month_drag":
					var row0 = $gridcontainer.find(".mvrow_0");
					var row1 = $gridcontainer.find(".mvrow_1");
					w = row0.width();
					offset = row0.offset();
					var diffset = row1.offset();
					moffset = $obj.offset();
					h = diffset.top - offset.top;
					left = offset.left;
					top = offset.top;
					l = 7;
					yl = row0.parent().children().length;
					py = w % l;
					pw = parseInt(w / l);
					if (py > l / 2 + 1) {
						pw++;
					}
					xa = [];
					ya = [];
					var xi = 0;
					var yi = 0;
					for (i = 0; i < l; i++) {
						xa.push({ s:i * pw + left, e:(i + 1) * pw + left });
						if (moffset.left >= xa[i].s && moffset.left < xa[i].e) {
							xi = i;
						}
					}
					for (i = 0; i < yl; i++) {
						ya.push({ s:i * h + top, e:(i + 1) * h + top });
						if (moffset.top >= ya[i].s && moffset.top < ya[i].e) {
							yi = i;
						}
					}
					fdi = { x:xi, y:yi, di:yi * 7 + xi };
					data = $obj.data("eventdata");
					dp = DateDiff("d", data["start"], data["end"]) + 1;
					_dragdata = { type:"std_item_month_drag", target:$obj, sx:e.pageX, sy:e.pageY, data:data, xa:xa, ya:ya, fdi:fdi, h:h, dp:dp, pw:pw };
					break;
			}
			$('body').noSelect();
		}

		function dragMove(e) {
			var d, sy, sx, x, y, diffy, gh, ny, tempdata, cpwrap, ndi, evid, nh, cp, w1;
			if (_dragdata) {
				if (e.pageX < 0 || e.pageY < 0
					|| e.pageX > document.documentElement.clientWidth
					|| e.pageY >= document.documentElement.clientHeight) {
					dragEnd(e);
					return false;
				}
				d = _dragdata;
				switch (d.type) {
					case "std_empty_drag":
						sy = d.sy;
						y = e.pageY;
						diffy = y - sy;
						if (diffy > (option.hour_height / 4) || diffy < (-1 * (option.hour_height / 4)) || d.cpwrap) {
							if (diffy == 0) {
								diffy = Math.ceil(option.hour_height / 2);
							}
							var dy = diffy % Math.ceil(option.hour_height / 2);
							if (dy != 0) {
								diffy = dy > 0 ? diffy + Math.ceil(option.hour_height / 2) - dy : diffy - Math.ceil(option.hour_height / 2) - dy;
								y = d.sy + diffy;
								if (diffy < 0) {
									sy = sy + Math.ceil(option.hour_height / 2);
								}
							}
							if (!d.tp) {
								d.tp = $(d.target).offset().top;
							}
							gh = gH(sy, y, d.tp);
							ny = gP(gh.sh, gh.sm);
							if (!d.cpwrap) {
								tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, "", "", "", option.std_color);
								cpwrap = $("<div class='ca-evpi drag-chip-wrapper' style='top:" + ny + "px'/>").html(tempdata);
								$(d.target).find("div.tg-col-overlaywrapper").append(cpwrap);
								d.cpwrap = cpwrap;
							}
							else {
								if (d.cgh.sh != gh.sh || d.cgh.eh != gh.eh || d.cgh.sm != gh.sm || d.cgh.em != gh.em) {
									tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, "", "", "", option.std_color);
									d.cpwrap.css("top", ny + "px").html(tempdata);
								}
							}
							d.cgh = gh;
						}
						break;
					case "fullday_empty_drag":
						sx = d.sx;
						x = e.pageX;
						diffx = x - sx;
						if (diffx > 5 || diffx < -5 || d.lasso) {
							if (!d.lasso) {
								d.lasso = $("<div style='z-index: 10; display: block' class='drag-lasso-container'/>");
								$(document.body).append(d.lasso);
							}
							if (!d.sdi) {
								d.sdi = getdi(d.xa, d.ya, sx, d.sy);
							}
							ndi = getdi(d.xa, d.ya, x, e.pageY);
							if (!d.fdi || d.fdi.di != ndi.di) {
								addlasso(d.lasso, d.sdi, ndi, d.xa, d.ya, d.h);
							}
							d.fdi = ndi;
						}
						break;
					case "empty_month_drag":
						sx = d.sx;
						x = e.pageX;
						sy = d.sy;
						y = e.pageY;
						diffx = x - sx;
						diffy = y - sy;
						if (diffx > 5 || diffx < -5 || diffy < -5 || diffy > 5 || d.lasso) {
							if (!d.lasso) {
								d.lasso = $("<div style='z-index: 10; display: block' class='drag-lasso-container'/>");
								$(document.body).append(d.lasso);
							}
							if (!d.sdi) {
								d.sdi = getdi(d.xa, d.ya, sx, sy);
							}
							ndi = getdi(d.xa, d.ya, x, y);
							if (!d.fdi || d.fdi.di != ndi.di) {
								addlasso(d.lasso, d.sdi, ndi, d.xa, d.ya, d.h);
							}
							d.fdi = ndi;
						}
						break;
					case "std_item_move":
						data = d.data;
						if (data != null && data["is_editable_quick"] == 1) {
							sx = d.sx;
							x = e.pageX;
							sy = d.sy;
							y = e.pageY;
							diffx = x - sx;
							diffy = y - sy;
							if (diffx > 5 || diffx < -5 || diffy > 5 || diffy < -5 || d.cpwrap) {
								if (!d.cpwrap) {
									gh = { sh:data["start"].getHours(),
										sm:data["start"].getMinutes(),
										eh:data["end"].getHours(),
										em:data["end"].getMinutes(),
										h:d.h
									};
									d.target.hide();
									ny = gP(gh.sh, gh.sm);
									d.top = ny;
									tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, data["subject"], false, false, data["color"]);
									cpwrap = $("<div class='ca-evpi drag-chip-wrapper' style='top:" + ny + "px'/>").html(tempdata);
									evid = ".tgOver" + d.target.parent().data("col");
									$gridcontainer.find(evid).append(cpwrap);
									d.cpwrap = cpwrap;
									d.ny = ny;
								}
								else {
									var pd = 0;
									if (x < d.pXMin) {
										pd = -1;
									}
									else if (x > d.pXMax) {
										pd = 1;
									}
									if (pd != 0) {

										d.cdi = d.cdi + pd;
										var ov = $gridcontainer.find(".tgOver" + d.cdi);
										if (ov.length == 1) {
											d.pXMin = d.pXMin + d.pw * pd;
											d.pXMax = d.pXMax + d.pw * pd;
											ov.append(d.cpwrap);
										}
										else {
											d.cdi = d.cdi - pd;
										}
									}
									ny = d.top + diffy;
									var pny = ny % Math.ceil(option.hour_height / 2);
									if (pny != 0) {
										ny = ny - pny;
									}
									if (d.ny != ny) {
										//log.info("ny=" + ny);
										gh = gW(ny, ny + d.h);
										//log.info("sh=" + gh.sh + ",sm=" + gh.sm);
										tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, data["subject"], false, false, data["color"]);
										d.cpwrap.css("top", ny + "px").html(tempdata);
									}
									d.ny = ny;
								}
							}
						}

						break;
					case "std_item_resize":
						var data = d.data;
						if (data != null && data["is_editable_quick"] == 1) {
							sy = d.sy;
							y = e.pageY;
							diffy = y - sy;
							if (diffy != 0 || d.cpwrap) {
								if (!d.cpwrap) {
									gh = { sh:data["start"].getHours(),
										sm:data["start"].getMinutes(),
										eh:data["end"].getHours(),
										em:data["end"].getMinutes(),
										h:d.h
									};
									d.target.hide();
									ny = gP(gh.sh, gh.sm);
									d.top = ny;
									tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, data["subject"], "100%", true, data["color"]);
									cpwrap = $("<div class='ca-evpi drag-chip-wrapper' style='top:" + ny + "px'/>").html(tempdata);
									evid = ".tgOver" + d.target.parent().data("col");
									$gridcontainer.find(evid).append(cpwrap);
									d.cpwrap = cpwrap;
								}
								else {
									nh = d.h + diffy;
									var pnh = nh % Math.ceil(option.hour_height / 2);
									nh = pnh > 1 ? nh - pnh + Math.ceil(option.hour_height / 2) : nh - pnh;
									if (d.nh != nh) {
										gh = gW(d.top, d.top + nh);
										tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, data["subject"], "100%", true, data["color"]);
										d.cpwrap.html(tempdata);
									}
									d.nh = nh;
								}
							}
						}
						break;
					case "fullday_item_move":
						sx = d.sx;
						x = e.pageX;
						y = e.pageY;
						diffx = x - sx;
						if (diffx > 5 || diffx < -5 || d.lasso) {
							if (!d.lasso) {
								w1 = d.dp > 1 ? (d.pw - 4) * 1.5 : (d.pw - 4);
								cp = d.target.clone();
								if (d.dp > 1) {
									cp.find("div.rb-i>span").prepend("(" + d.dp + " " + i18n.xgcalendar.day_plural + ")&nbsp;");
								}
								cpwrap = $("<div class='drag-event st-contents' style='width:" + w1 + "px'/>").append(cp).appendTo(document.body);
								d.cpwrap = cpwrap;
								d.lasso = $("<div style='z-index: 10; display: block' class='drag-lasso-container'/>");
								$(document.body).append(d.lasso);
								cp = cpwrap = null;
							}
							fixcppostion(d.cpwrap, e, d.xa, d.ya);
							ndi = getdi(d.xa, d.ya, x, e.pageY);
							if (!d.cdi || d.cdi.di != ndi.di) {
								addlasso(d.lasso, ndi, { x:ndi.x, y:ndi.y, di:ndi.di + d.dp - 1 }, d.xa, d.ya, d.h);
							}
							d.cdi = ndi;
						}
						break;
					case "std_item_month_drag":
						sx = d.sx;
						sy = d.sy;
						x = e.pageX;
						y = e.pageY;
						var diffx = x - sx;
						diffy = y - sy;
						if (diffx > 5 || diffx < -5 || diffy > 5 || diffy < -5 || d.lasso) {
							if (!d.lasso) {
								w1 = d.dp > 1 ? (d.pw - 4) * 1.5 : (d.pw - 4);
								cp = d.target.clone();
								if (d.dp > 1) {
									cp.find("div.rb-i>span").prepend("(" + d.dp + " " + i18n.xgcalendar.day_plural + ")&nbsp;");
								}
								cpwrap = $("<div class='drag-event st-contents' style='width:" + w1 + "px'/>").append(cp).appendTo(document.body);
								d.cpwrap = cpwrap;
								d.lasso = $("<div style='z-index: 10; display: block' class='drag-lasso-container'/>");
								$(document.body).append(d.lasso);
								cp = cpwrap = null;
							}
							fixcppostion(d.cpwrap, e, d.xa, d.ya);
							ndi = getdi(d.xa, d.ya, x, e.pageY);
							if (!d.cdi || d.cdi.di != ndi.di) {
								addlasso(d.lasso, ndi, { x:ndi.x, y:ndi.y, di:ndi.di + d.dp - 1 }, d.xa, d.ya, d.h);
							}
							d.cdi = ndi;
						}
						break;
				}
			}
			return false;
		}

		function dragEnd(e) {
			if (_dragdata) {
				var d = _dragdata;
				var tp, start, end, gh;
				switch (d.type) {
					case "std_empty_drag": //day view
						var wrapid = new Date().getTime();
						tp = d.target.offset().top;
						if (!d.cpwrap) {
							gh = gH(d.sy, d.sy + option.hour_height, tp);
							var ny = gP(gh.sh, gh.sm);
							var tempdata = buildtempdayevent(gh.sh, gh.sm, gh.eh, gh.em, gh.h, "", "", "", option.std_color);
							d.cpwrap = $("<div class='ca-evpi drag-chip-wrapper' style='top:" + ny + "px'/>").html(tempdata);
							$(d.target).find("div.tg-col-overlaywrapper").append(d.cpwrap);
							d.cgh = gh;
						}
						var pos = d.cpwrap.offset();
						pos.left = pos.left + 30;
						d.cpwrap.attr("id", wrapid);
						start = new Date(parseInt(d.target.data("abbr")) + (d.cgh.sh * 3600 + d.cgh.sm * 60) * 1000);
						end = new Date(parseInt(d.target.data("abbr")) + (d.cgh.eh * 3600 + d.cgh.em * 60) * 1000);
						_dragevent = function () {
							$("#" + wrapid).remove();
							$("#bbit-cal-buddle").css("visibility", "hidden");
						};
						quickadd(start, end, false, pos);
						break;
					case "fullday_empty_drag": //week view
					case "empty_month_drag": //month view
						var source = e.srcElement || e.target;
						var lassoid = new Date().getTime();
						if (!d.lasso) {
							if ($(source).hasClass("monthdayshow")) {
								weekormonthtoday.call($(source).parent()[0], e);
								break;
							}
							d.fdi = d.sdi = getdi(d.xa, d.ya, d.sx, d.sy);
							d.lasso = $("<div style='z-index: 10; display: block' class='drag-lasso-container'/>");
							$(document.body).append(d.lasso);
							addlasso(d.lasso, d.sdi, d.fdi, d.xa, d.ya, d.h);
						}
						d.lasso.attr("id", lassoid);
						var si = Math.min(d.fdi.di, d.sdi.di);
						var ei = Math.max(d.fdi.di, d.sdi.di);
						var firstday = option.vstart;
						start = DateAdd("d", si, firstday);
						end = DateAdd("d", ei, firstday);
						_dragevent = function () {
							$("#" + lassoid).remove();
						};
						quickadd(start, end, true, { left:e.pageX, top:e.pageY });
						break;
					case "std_item_move": // event moving
						if (d.cpwrap) {
							start = DateAdd("d", d.cdi, option.vstart);
							end = DateAdd("d", d.cdi, option.vstart);
							gh = gW(d.ny, d.ny + d.h);
							start.setHours(gh.sh, gh.sm);
							end.setHours(gh.eh, gh.em);
							if (start.getTime() == d.data["start"].getTime() && end.getTime() == d.data["end"].getTime()) {
								d.cpwrap.remove();
								d.target.show();
							}
							else {
								dayupdate(d.data, start, end);
							}
						}
						break;
					case "std_item_resize": //Resize
						if (d.cpwrap) {
							start = new Date(d.data["start"].toString());
							end = new Date(d.data["end"].toString());
							gh = gW(d.top, d.top + d.nh);
							start.setHours(gh.sh, gh.sm);
							end.setHours(gh.eh, gh.em);
							if (start.getTime() == d.data["start"].getTime() && end.getTime() == d.data["end"].getTime()) {
								d.cpwrap.remove();
								d.target.show();
							}
							else {
								dayupdate(d.data, start, end);
							}
						}
						break;
					case "fullday_item_move":
					case "std_item_month_drag":
						if (d.lasso) {
							d.cpwrap.remove();
							d.lasso.remove();
							start = new Date(d.data["start"].toString());
							end = new Date(d.data["end"].toString());
							var currrentdate = DateAdd("d", d.cdi.di, option.vstart);
							var diff = DateDiff("d", start, currrentdate);
							start = DateAdd("d", diff, start);
							end = DateAdd("d", diff, end);
							if (start.getTime() != d.data["start"].getTime() || end.getTime() != d.data["end"].getTime()) {
								dayupdate(d.data, start, end);
							}
						}
						break;
				}
				d = _dragdata = null;
				$('body').noSelect(false);
				return false;
			}
			return false;
		}

		function getdi(xa, ya, x, y) {
			var ty = 0;
			var tx = 0;
			var lx = 0;
			var ly = 0;
			if (xa && xa.length != 0) {
				lx = xa.length;
				if (x >= xa[lx - 1].e) {
					tx = lx - 1;
				}
				else {
					for (var i = 0; i < lx; i++) {
						if (x > xa[i].s && x <= xa[i].e) {
							tx = i;
							break;
						}
					}
				}
			}
			if (ya && ya.length != 0) {
				ly = ya.length;
				if (y >= ya[ly - 1].e) {
					ty = ly - 1;
				}
				else {
					for (var j = 0; j < ly; j++) {
						if (y > ya[j].s && y <= ya[j].e) {
							ty = j;
							break;
						}
					}
				}
			}
			return { x:tx, y:ty, di:ty * lx + tx };
		}

		function addlasso(lasso, sdi, edi, xa, ya, height) {
			var diff = sdi.di > edi.di ? sdi.di - edi.di : edi.di - sdi.di;
			diff++;
			var sp = sdi.di > edi.di ? edi : sdi;
			var l = xa.length > 0 ? xa.length : 1;
			var h = ya.length > 0 ? ya.length : 1;
			var play = [];
			var width = xa[0].e - xa[0].s;
			var i = sp.x;
			var j = sp.y;
			var max = Math.min(document.documentElement.clientWidth, xa[l - 1].e) - 2;

			while (j < h && diff > 0) {
				var left = xa[i].s;
				var d = i + diff > l ? l - i : diff;
				var wid = width * d;
				while (left + wid >= max) {
					wid--;
				}
				play.push(Tp(__LASSOTEMP, { left:left, top:ya[j].s, height:height, width:wid }));
				i = 0;
				diff = diff - d;
				j++;
			}
			lasso.html(play.join(""));
		}

		function fixcppostion(cpwrap, e, xa, ya) {
			var x = e.pageX - 6;
			var y = e.pageY - 4;
			var w = cpwrap.width();
			var h = 21;
			var lmin = xa[0].s + 6;
			var tmin = ya[0].s + 4;
			var lmax = xa[xa.length - 1].e - w - 2;
			var tmax = ya[ya.length - 1].e - h - 2;
			if (x > lmax) {
				x = lmax;
			}
			if (x <= lmin) {
				x = lmin + 1;
			}
			if (y <= tmin) {
				y = tmin + 1;
			}
			if (y > tmax) {
				y = tmax;
			}
			cpwrap.css({ left:x, top:y });
		}

		$(document)
			.mousemove(dragMove)
			.mouseup(dragEnd);
		//.mouseout(dragEnd);

		this[0].bcal = {
			sv:function (view) { //switch view                
				if (view == option.view) {
					return;
				}
				clearcontainer();
				option.view = view;
				render();
				populate();
			},
			rf:function () {
				populate();
			},
			gt:function (d) {
				if (!d) {
					d = new Date();
				}
				option.showday = d;
				render();
				populate();
			},

			pv:function () {
				switch (option.view) {
					case "day":
						option.showday = DateAdd("d", -1, option.showday);
						break;
					case "week":
						option.showday = DateAdd("w", -1, option.showday);
						break;
					case "multi_days":
						option.showday = DateAdd("w", -1, option.showday);
						break;
					case "month":
						option.showday = DateAdd("m", -1, option.showday);
						break;
				}
				render();
				populate();
			},
			nt:function () {
				switch (option.view) {
					case "day":
						option.showday = DateAdd("d", 1, option.showday);
						break;
					case "week":
						option.showday = DateAdd("w", 1, option.showday);
						break;
					case "multi_days":
						option.showday = DateAdd("w", 1, option.showday);
						break;
					case "month":
						var od = option.showday.getDate();
						option.showday = DateAdd("m", 1, option.showday);
						var nd = option.showday.getDate();
						if (od != nd) //we go to the next month
						{
							option.showday = DateAdd("d", 0 - nd, option.showday); //last day of last month
						}
						break;
				}
				render();
				populate();
			},
			go:function () {
				return option;
			},
			so:function (p) {
				option = $.extend(option, p);
			}
		};

		return this;
	};

	/**
	 * @description {Method} switchView To switch to another view.
	 * @param {String} view View name, one of 'day', 'week', 'multi_days', 'month'.
	 */
	$.fn.switchView = function (view) {
		return this.each(function () {
			if (this.bcal) {
				this.bcal.sv(view);
			}
		})
	};

	/**
	 * @description {Method} reload To reload event of current time range.
	 */
	$.fn.reload = function () {
		return this.each(function () {
			if (this.bcal) {
				this.bcal.rf();
			}
		})
	};

	/**
	 * @description {Method} gotoDate To go to a range containing date.
	 * If view is week, it will go to a week containing date.
	 * If view is month, it will got to a month containing date.
	 * @param {Date} d. Date to go.
	 */
	$.fn.gotoDate = function (d) {
		return this.each(function () {
			if (this.bcal) {
				this.bcal.gt(d);
			}
		})
	};

	/**
	 * @description {Method} previousRange To go to previous date range.
	 * If view is week, it will go to previous week.
	 * If view is month, it will got to previous month.
	 */
	$.fn.previousRange = function () {
		return this.each(function () {
			if (this.bcal) {
				this.bcal.pv();
			}
		})
	};

	/**
	 * @description {Method} nextRange To go to next date range.
	 * If view is week, it will go to next week.
	 * If view is month, it will got to next month.
	 */
	$.fn.nextRange = function () {
		return this.each(function () {
			if (this.bcal) {
				this.bcal.nt();
			}
		})
	};


	$.fn.BcalGetOp = function () {
		if (this[0].bcal) {
			return this[0].bcal.go();
		}
		return null;
	};


	$.fn.BcalSetOp = function (p) {
		if (this[0].bcal) {
			return this[0].bcal.so(p);
		}
	};

})(jQuery);