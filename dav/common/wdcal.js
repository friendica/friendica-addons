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

function wdcal_edit_init(dateFormat) {
	"use strict";

	$("#cal_color").colorPicker();

	$("#cal_start_time").timePicker({ step: 15 }).on("change", wdcal_edit_checktime_startChanged);
	$("#cal_end_time").timePicker().on("change", wdcal_edit_checktime_endChanged);

	$("#cal_start_date").datepicker({
		"dateFormat": dateFormat
	}).on("change", wdcal_edit_checktime_startChanged);
	$("#cal_end_date").datepicker({
		"dateFormat": dateFormat
	}).on("change", wdcal_edit_checktime_endChanged);

	$("#notification").on("click change", function() {
		if ($(this).prop("checked")) $("#notification_detail").show();
		else ($("#notification_detail")).hide();
	}).change();

	$("#cal_allday").on("click change", function() {
		if ($(this).prop("checked")) $("#cal_end_time, #cal_start_time").hide();
		else $("#cal_end_time, #cal_start_time").show();
	}).change();
}