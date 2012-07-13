function wdcal_edit_getStartEnd() {
	"use strict";

	var start = $("#cal_start_date").datepicker("getDate");
	var start_time = $.timePicker("#cal_start_time").getTime();
	start.setHours(start_time.getHours());
	start.setMinutes(start_time.getMinutes());

	var end = $("#cal_end_date").datepicker("getDate");
	var end_time = $.timePicker("#cal_end_time").getTime();
	end.setHours(end_time.getHours());
	end.setMinutes(end_time.getMinutes());

	return {"start": start, "end": end};
}

function wdcal_edit_checktime_startChanged() {
	"use strict";

	var time = wdcal_edit_getStartEnd();
	if (time.start.getTime() >= time.end.getTime()) {
		var newend = new Date(time.start.getTime() + 3600000);
		$("#cal_end_date").datepicker("setDate", newend);
		$.timePicker("#cal_end_time").setTime(newend);
	}
	wdcal_edit_recur_recalc();
}

function wdcal_edit_checktime_endChanged() {
	"use strict";

	var time = wdcal_edit_getStartEnd();
	if (time.start.getTime() >= time.end.getTime()) {
		var newstart = new Date(time.end.getTime() - 3600000);
		$("#cal_start_date").datepicker("setDate", newstart);
		$.timePicker("#cal_start_time").setTime(newstart);
	}
}

function wdcal_edit_recur_recalc() {
	"use strict";

	var start = $("#cal_start_date").datepicker("getDate");
	$(".rec_month_name").text($.datepicker._defaults.monthNames[start.getMonth()]);
	$("#rec_yearly_day option[value=bymonthday]").text($("#rec_yearly_day option[value=bymonthday]").data("orig").replace("#num#", start.getDate()));
	$("#rec_monthly_day option[value=bymonthday]").text($("#rec_monthly_day option[value=bymonthday]").data("orig").replace("#num#", start.getDate()));
	var month = new Date(start.getFullYear(), start.getMonth() + 1, 0);
	var monthlast = month.getDate() - start.getDate() + 1;
	$("#rec_yearly_day option[value=bymonthday_neg]").text($("#rec_yearly_day option[value=bymonthday_neg]").data("orig").replace("#num#", monthlast));
	$("#rec_monthly_day option[value=bymonthday_neg]").text($("#rec_monthly_day option[value=bymonthday_neg]").data("orig").replace("#num#", monthlast));
	var wk = Math.ceil(start.getDate() / 7);
	var wkname = $.datepicker._defaults.dayNames[start.getDay()];
	$("#rec_yearly_day option[value=byday]").text($("#rec_yearly_day option[value=byday]").data("orig").replace("#num#", wk).replace("#wkday#", wkname));
	$("#rec_monthly_day option[value=byday]").text($("#rec_monthly_day option[value=byday]").data("orig").replace("#num#", wk).replace("#wkday#", wkname));
	var wk_inv = Math.ceil(monthlast / 7);
	$("#rec_yearly_day option[value=byday_neg]").text($("#rec_yearly_day option[value=byday_neg]").data("orig").replace("#num#", wk_inv).replace("#wkday#", wkname));
	$("#rec_monthly_day option[value=byday_neg]").text($("#rec_monthly_day option[value=byday_neg]").data("orig").replace("#num#", wk_inv).replace("#wkday#", wkname));
}

function wdcal_edit_init(dateFormat, base_path) {
	"use strict";

	$("#cal_color").colorPicker();
	$("#color_override").on("click", function() {
		if ($("#color_override").prop("checked")) $("#cal_color_holder").show();
		else $("#cal_color_holder").hide();
	});

	$("#cal_start_time").timePicker({ step: 15 }).on("change", wdcal_edit_checktime_startChanged);
	$("#cal_end_time").timePicker().on("change", wdcal_edit_checktime_endChanged);

	$("#cal_start_date").datepicker({
		"dateFormat": dateFormat
	}).on("change", wdcal_edit_checktime_startChanged);
	$("#cal_end_date").datepicker({
		"dateFormat": dateFormat
	}).on("change", wdcal_edit_checktime_endChanged);

	$("#rec_until_date").datepicker({ "dateFormat": dateFormat });

	$("#notification").on("click change", function() {
		if ($(this).prop("checked")) $("#notification_detail").show();
		else ($("#notification_detail")).hide();
	}).change();

	$("#cal_allday").on("click change", function() {
		if ($(this).prop("checked")) $("#cal_end_time, #cal_start_time").hide();
		else $("#cal_end_time, #cal_start_time").show();
	}).change();

	$("#rec_frequency").on("click change", function() {
		var val = $("#rec_frequency").val();
		if (val == "") $("#rec_details").hide();
		else $("#rec_details").show();

		if (val == "daily") $(".rec_daily").show();
		else $(".rec_daily").hide();

		if (val == "weekly") $(".rec_weekly").show();
		else $(".rec_weekly").hide();

		if (val == "monthly") $(".rec_monthly").show();
		else $(".rec_monthly").hide();

		if (val == "yearly") $(".rec_yearly").show();
		else $(".rec_yearly").hide();
	}).change();

	$("#rec_until_type").on("click change", function() {
		var val = $("#rec_until_type").val();

		if (val == "count") $("#rec_until_count").show();
		else $("#rec_until_count").hide();

		if (val == "date") $("#rec_until_date").show();
		else $("#rec_until_date").hide();
	}).change();

	$("#rec_yearly_day option, #rec_monthly_day option").each(function() {
		$(this).data("orig", $(this).text());
	});

	wdcal_edit_recur_recalc();

	$(document).on("click", ".exception_remover", function(ev) {
		ev.preventDefault();
		var $this = $(this),
			$par = $this.parents(".rec_exceptions");
		$this.parents(".except").remove();
		if ($par.find(".rec_exceptions_holder").children().length == 0) {
			$par.find(".rec_exceptions_holder").hide();
			$par.find(".rec_exceptions_none").show();
		}
	});

	$(".exception_adder").click(function(ev) {
		ev.preventDefault();

		var exceptions = [];
		$(".rec_exceptions .except input").each(function() {
			exceptions.push($(this).val());
		});
		var rec_weekly_byday = [];
		$(".rec_weekly_byday:checked").each(function() {
			rec_weekly_byday.push($(this).val());
		});
		var rec_daily_byday = [];
		$(".rec_daily_byday:checked").each(function() {
			rec_daily_byday.push($(this).val());
		});
		var opts = {
			"start_date": $("input[name=start_date]").val(),
			"start_time": $("input[name=start_time]").val(),
			"end_date": $("input[name=end_date]").val(),
			"end_time": $("input[name=end_time]").val(),
			"rec_frequency": $("#rec_frequency").val(),
			"rec_interval": $("#rec_interval").val(),
			"rec_until_type": $("#rec_until_type").val(),
			"rec_until_count": $("#rec_until_count").val(),
			"rec_until_date": $("#rec_until_date").val(),
			"rec_weekly_byday": rec_weekly_byday,
			"rec_daily_byday": rec_daily_byday,
			"rec_weekly_wkst": $("input[name=rec_weekly_wkst]:checked").val(),
			"rec_monthly_day": $("#rec_monthly_day").val(),
			"rec_yearly_day": $("#rec_yearly_day").val(),
			"rec_exceptions": exceptions
		};
		if ($("#cal_allday").prop("checked")) opts["allday"] = 1;
		var $dial = $("<div id='exception_setter_dialog'>Loading...</div>");
		$dial.appendTo("body");
		$dial.dialog({
			"width": 400,
			"height": 300,
			"title": "Exceptions"
		});
		$dial.load(base_path + "getExceptionDates/", opts, function() {
			$dial.find(".exception_selector_link").click(function(ev2) {
				ev2.preventDefault();
				var ts = $(this).data("timestamp");
				var str = $(this).html();
				var $part = $("<div data-timestamp='" + ts + "' class='except'><input type='hidden' class='rec_exception' name='rec_exceptions[]' value='" + ts + "'><a href='#' class='exception_remover'>[remove]</a> " + str + "</div>");
				var found = false;
				$(".rec_exceptions_holder .except").each(function() {
					if (!found && ts < $(this).data("timestamp")) {
						found = true;
						$part.insertBefore(this);
					}
				});
				if (!found) $(".rec_exceptions_holder").append($part);
				$(".rec_exceptions .rec_exceptions_holder").show();
				$(".rec_exceptions .rec_exceptions_none").hide();

				$dial.dialog("destroy").remove();
			})
		});
	});
}


function wdcal_edit_calendars_start(dateFormat, base_path) {
	"use strict";

	$(".cal_color").colorPicker();

	$(".delete_cal").click(function(ev) {
		if (!confirm("Do you really want to delete this calendar? All events will be moved to another private calendar.")) ev.preventDefault();
	});

	$(".calendar_add_caller").click(function(ev) {
		$(".cal_add_row").show();
		$(this).parents("div").hide();
		ev.preventDefault();
	});
}
