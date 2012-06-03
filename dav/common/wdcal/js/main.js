$(function () {
	"use strict";

	$.fn.animexxCalendar = function (option) {
		//(wdcal_view, std_theme, data_feed_url, readonly, height_diff) {

		var url_cal_add = "?";
		$(this).find(".calselect input[type=checkbox]").each(function() {
			if ($(this).prop("checked")) url_cal_add += "cal[]=" + $(this).val() + "&";
		});

		var def = {
			calendars:[],
			calendars_show:[],
			view:"week",
			theme:0,
			onWeekOrMonthToDay:wtd,
			onBeforeRequestData:cal_beforerequest,
			onAfterRequestData:cal_afterrequest,
			onRequestDataError:cal_onerror,
			autoload:true,
			data_feed_url:"",
			url:option.data_feed_url + url_cal_add + "method=list",
			quickAddUrl:option.data_feed_url + url_cal_add + "method=add",
			quickUpdateUrl:option.data_feed_url + url_cal_add + "method=update",
			quickDeleteUrl:option.data_feed_url + url_cal_add + "method=remove"
		};

		option = $.extend(def, option);

		var $animexxcal = $(this),
			$gridcontainer = $animexxcal.find(".gridcontainer"),
			$dv = $animexxcal.find(".calhead"),
			$caltoolbar = $animexxcal.find(".ctoolbar"),
			$txtdatetimeshow = $animexxcal.find(".txtdatetimeshow"),
			$loadingpanel = $animexxcal.find(".loadingpanel"),
			$loaderrpanel = $animexxcal.find(".loaderror");

		var _MH = document.documentElement.clientHeight;
		var dvH = $dv.height() + 2;

		option.height = _MH - dvH - option.height_diff;
		if (option.height < 300) option.height = 300;
		option.eventItems = [];

		$animexxcal.find(".hdtxtshow").datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect: function(dateText, inst) {
				var r = new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
				var p = $gridcontainer.gotoDate(r).BcalGetOp();
				if (p && p.datestrshow) {
					$animexxcal.find(".txtdatetimeshow").text(p.datestrshow);
				}
			}
		});
		$animexxcal.find(".txtdatetimeshow").css("cursor", "pointer").bind("click", function() {
			$animexxcal.find(".hdtxtshow").datepicker("show");
		});

		var p = $gridcontainer.bcalendar(option).BcalGetOp();
		if (p && p.datestrshow) {
			$txtdatetimeshow.text(p.datestrshow);
		}

		$caltoolbar.noSelect();

		function cal_beforerequest(type) {
			var t = "Lade Daten...";
			switch (type) {
				case 1:
					t = "Lade Daten...";
					break;
				case 2:
				case 3:
				case 4:
					t = "Wird bearbeitete ...";
					break;
			}
			$loaderrpanel.hide();
			$loadingpanel.html(t).show();
		}

		function cal_afterrequest(type) {
			var p = $gridcontainer.BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}

			switch (type) {
				case 1:
					$loadingpanel.hide();
					break;
				case 2:
				case 3:
				case 4:
					$loadingpanel.html("Erfolg!");
					$gridcontainer.reload();
					window.setTimeout(function () {
						$loadingpanel.hide();
					}, 2000);
					break;
			}

		}

		function cal_onerror(type, data) {
			$loaderrpanel.show();
		}

		function wtd(p) {
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			$caltoolbar.find("div.fcurrent").removeClass("fcurrent");
			$animexxcal.find(".showdaybtn").addClass("fcurrent");
		}

		//to show day view
		$animexxcal.find(".showdaybtn").on("click", function (e) {
			//document.location.href="#day";
			$caltoolbar.find("div.fcurrent").removeClass("fcurrent");
			$(this).addClass("fcurrent");
			var p = $gridcontainer.switchView("day").BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();
		});
		//to show week view
		$animexxcal.find(".showweekbtn").on("click", function (e) {
			//document.location.href="#week";
			$caltoolbar.find("div.fcurrent").removeClass("fcurrent");
			$(this).addClass("fcurrent");
			var p = $gridcontainer.switchView("week").BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();
		});
		//to show month view
		$animexxcal.find(".showmonthbtn").on("click", function (e) {
			//document.location.href="#month";
			$caltoolbar.find("div.fcurrent").removeClass("fcurrent");
			$(this).addClass("fcurrent");
			var p = $gridcontainer.switchView("month").BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();
		});

		$animexxcal.find(".showreflashbtn").on("click", function (e) {
			$gridcontainer.reload();
			e.preventDefault();
		});

		//go to today
		$animexxcal.find(".showtodaybtn").on("click", function (e) {
			var p = $gridcontainer.gotoDate().BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();

		});
		//previous date range
		$animexxcal.find(".sfprevbtn").on("click", function (e) {
			var p = $gridcontainer.previousRange().BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();
		});
		//next date range
		$animexxcal.find(".sfnextbtn").on("click", function (e) {
			var p = $gridcontainer.nextRange().BcalGetOp();
			if (p && p.datestrshow) {
				$txtdatetimeshow.text(p.datestrshow);
			}
			e.preventDefault();
		});

		$animexxcal.find(".calselect input[type=checkbox]").on("click change", function() {
			var url_cal_add = option.data_feed_url + "?";
			$animexxcal.find(".calselect input[type=checkbox]").each(function() {
				if ($(this).prop("checked")) url_cal_add += "cal[]=" + $(this).val() + "&";
			});
/*
 url:option.data_feed_url + url_cal_add + "method=list",
 quickAddUrl:option.data_feed_url + url_cal_add + "method=add",
 quickUpdateUrl:option.data_feed_url + url_cal_add + "method=update",
 quickDeleteUrl:option.data_feed_url + url_cal_add + "method=remove"

 */
			var url = url_cal_add + "method=list";
			var p = $gridcontainer.BcalGetOp();
			if (p.url != url) {
				$gridcontainer.BcalSetOp({
					"url": url_cal_add + "method=list",
					"quickAddUrl": url_cal_add + "method=add",
					"quickUpdateUrl": url_cal_add + "method=update",
					"quickDeleteUrl": url_cal_add + "method=remove"
				});
				$gridcontainer.reload();
			}
		});
	}

});