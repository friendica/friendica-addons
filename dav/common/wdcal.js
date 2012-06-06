function wdcal_edit_init(dateFormat) {
	"use strict";

	$("#cal_color").colorPicker();

	$("#cal_start_time").timePicker({ step: 15 });
	$("#cal_end_time").timePicker();

	$("#cal_start_date").datepicker({
		"dateFormat": dateFormat
	});
	$("#cal_end_date").datepicker({
		"dateFormat": dateFormat
	});

	$("#notification").on("click change", function() {
		if ($(this).prop("checked")) $("#notification_detail").show();
		else ($("#notification_detail")).hide();
	}).change();

	$("#cal_allday").on("click change", function() {
		if ($(this).prop("checked")) $("#cal_end_time, #cal_start_time").hide();
		else $("#cal_end_time, #cal_start_time").show();
	}).change();
}