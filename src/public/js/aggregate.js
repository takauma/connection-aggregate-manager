// カレンダーを月曜始まりにする.
flatpickr.l10ns.ja.firstDayOfWeek = 1;

/** プロセス種別. */
const PROCESS_TYPE = Object.freeze({
	/** ログアウト. */
	LOGOUT: Symbol("logout")
});

/** 表示種別. */
const VIEW_TYPE = Object.freeze({
	/** 日. */
	DATE: Symbol(0),
	/** 週. */
	WEEK: Symbol(1),
	/** 月. */
	MONTH: Symbol(2)
});

/** チャートカラム. */
const CHART_COLUMS = Object.freeze([
	["x"],
	["接続数"],
	["新規接続端末数(月ごと)"]
]);

/** メッセージモーダル. */
const MSG_MODAL = new bootstrap.Modal(document.getElementById("msg-modal"));

/** フェードイン速度. */
const FADE_IN_SPEED = 300;

/** チャート高さ. */
const CHART_HEIGTH = 580;

/** プロセス種別. */
let processType = null;

/** 集計存在日リスト. */
let existDateList = null;
/** 接続数マップ. */
let connectionCountMap = null;
/** 新規接続端末数(月ごと)マップ. */
let newConnectionCountMap = null;
/** チャート. */
let chart = null;

// 画面ロード後イベント.
$(function() {
	// 表示画面リンク強調.
	$("#aggregate-link").addClass("nav-active");

	// 集計有効ボンディング一覧取得要求送信.
	requestActive(function() {
		// 集計存在日一覧取得要求送信.
		requestExist(function() {
			// 初期処理.
			initialize();
		});
	});
});

// メッセージモーダルOKボタン押下時処理.
$(document).on("click", "#msg-modal-btn-ok", function() {
	// プロセス種別判定.
	if (processType == PROCESS_TYPE.LOGOUT) {
		logout();
	}
});

// 更新ボタン押下時イベント.
$(document).on("click", "#update-btn", function() {
	$("#viewType").val("date");
	initialize();
});

// ログアウトリンク押下時処理.
$(document).on("click", "#logout", function() {
	processType = PROCESS_TYPE.LOGOUT;

	// モーダル表示.
	$("#msg-modal-btn-ok").show();
	$("#msg-modal-btn-cancel").text("キャンセル");
	$("#msg-modal-body").text("ログアウトしてよろしいですか？");
	MSG_MODAL.show();
});

// ボンディングリスト変化時イベント.
$(document).on("change", "#boundList", function() {
	// チャート操作非表示.
	$("#chart-controll").hide();

	// メッセージ非表示.
	$("#noDataMsg").hide();

	// 集計存在日リスト初期化.
	existDateList = null;

	// 接続数マップ初期化.
	connectionCountMap = null;
	newConnectionCountMap = null;

	// チャート初期化.
	if (chart != null) {
		chart.destroy();
		chart = null;
	}

	// 表示種別初期化.
	$("#viewType").val("month");

	// 集計存在日一覧取得要求.
	requestExist(function() {
		// 初期処理.
		initialize()
	});
});

// 表示種別変化時イベント.
$(document).on("change", "#viewType", function() {
	// 変化した表示種別を取得.
	const viewType = parseViewType($("#viewType").val());
	
	// チャートを再生成.
	generateChart(viewType);
	
	// カレンダー表示を更新.
	updateCalendar(viewType);
	
	// カレンダー表示の値を取得.
	const calendarValue = $("#calendar").val().substring(0, 10);

	if (viewType == VIEW_TYPE.DATE) {
		// 日付選択の場合.
		const beforeDate = new Date(calendarValue);
		let afterDate = new Date(calendarValue);
		
		// 選択日にデータがない場合は一番近い未来日を指定.
		while (true) {
			const strDate = formatSimpleDate(afterDate);
			let resultDate = null;
			for (let i = 0; i < existDateList.length; i++) {
				const existDate = existDateList[i].substring(0, 10);
				if (existDate == strDate) {
					resultDate = existDate;
					break;
				}
			}
			if (resultDate != null) {
				afterDate = new Date(resultDate);
				break;
			}
			afterDate.setDate(afterDate.getDate() + 1);
		}
		if (beforeDate != afterDate) {
			$("#calendar").val(formatDate(afterDate));
			updateCalendar(viewType);
		}

		requestData("date", afterDate, function() {
			updateChart();
		});
	} else if (viewType == VIEW_TYPE.WEEK) {
		// 週選択の場合.
		const beforeDate = new Date(calendarValue);
		let afterDate = new Date(calendarValue);

		// 選択日にデータがない場合は一番近い未来日を指定.
		while (true) {
			const strDate = formatSimpleDate(afterDate);
			let resultDate = null;
			for (let i = 0; i < existDateList.length; i++) {
				const existDate = existDateList[i].substring(0, 10);
				if (existDate == strDate) {
					resultDate = existDate;
					break;
				}
			}
			if (resultDate != null) {
				afterDate = new Date(resultDate);
				break;
			}
			afterDate.setDate(afterDate.getDate() + 1);
		}

		// 週の開始日を取得.
		afterDate = getWeekStartEndDate(afterDate)[0];

		if (beforeDate != afterDate) {
			$("#calendar").val(formatDate(afterDate));
			updateCalendar(viewType);
		}

		requestData("week", afterDate, function() {
			updateChart();
		});
	} else if (viewType == VIEW_TYPE.MONTH) {
		// 月選択の場合.
		const beforeDate = new Date(calendarValue.substring(0, 7));
		let afterDate = new Date(calendarValue.substring(0, 7));

		while (true) {
			let strDate = formatSimpleDate(afterDate).substring(0, 7);
			let resultDate = null;
			for (let i = 0; i < existDateList.length; i++) {
				const existDate = existDateList[i].substring(0, 7);
				if (existDate == strDate) {
					resultDate = existDate;
					break;
				}
			}
			if (resultDate != null) {
				afterDate = new Date(resultDate);
				break;
			}
			afterDate.setMonth(afterDate.getMonth() + 1);
		}
		if (beforeDate != afterDate) {
			$("#calendar").val(formatDate(afterDate).substring(0, 7));
			updateCalendar(viewType);
		}

		requestData("month", afterDate, function() {
			updateChart();
		});
	}
});

// カレンダー変化時イベント.
$(document).on("change", "#calendar", function() {
	// 表示種別を取得.
	const viewType = parseViewType($("#viewType").val());
	// カレンダー表示の値を取得.
	const calendarValue = $("#calendar").val().substring(0, 10);

	if (viewType == VIEW_TYPE.DATE) {
		const startDate = new Date(calendarValue);
		requestData("date", startDate, function() {
			updateChart();
		});
	} else if (viewType == VIEW_TYPE.WEEK) {
		const startDate = new Date(calendarValue);
		requestData("week", startDate, function() {
			updateChart();
		});
	} else if (viewType == VIEW_TYPE.MONTH) {
		const startDate = new Date(calendarValue.substring(0, 7));
		requestData("month", startDate, function() {
			updateChart();
		});
	}
});

/**
 * ログアウト処理を行います.
 */
function logout() {
	location.href = "/logout";
}

/**
 * 初期処理を行います.
 */
function initialize() {
	initChart();
	
	// 最終データ存在日の月初日を取得.
	const firstDate = existDateList[existDateList.length - 1].substring(0, 8) + "01";

	requestData("month", firstDate, function() {
		let date = new Date(firstDate);
		if (connectionCountMap != null && connectionCountMap.length > 0) {
			date = new Date(Object.keys(connectionCountMap)[0]);
		}

		initCalendar(date);
		updateChart();
	});
}

/**
 * 集計データ取得要求を送信します.
 * @param viewType {String} 表示種別.
 * @param startDate {Date} 開始日時.
 * @param successHandle {Function} 正常時処理.
 */
function requestData(viewType, startDate, successHandler) {
	// Date型で来た場合は文字列型へ変換.
	if (startDate instanceof Date) {
		startDate = formatSimpleDate(startDate);
	}

	$.ajax({
		type: "POST",
		url: "/aggregateData",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify({
			"boundName": $("#boundList").val(),
			"type" : viewType.toUpperCase(),
			"startDate" : startDate
		})
	}).done(function(data) {
		if (data.resultCd == "00") {
			connectionCountMap = data.connectionCountMap;
			newConnectionCountMap = data.newConnectionCountMap;
			successHandler();
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました (" + jqXHR.status + " " + jqXHR.statusText + ")");
	})
}

/**
 * 集計有効ボンディング一覧取得要求を送信します.
 * @param successHandle {Function} 正常時処理.
 */
function requestActive(successHandler) {
	// 集計有効ボンディング一覧主取得要求送信.
	$.ajax({
		type: "POST",
		url: "/aggregateActive",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json"
		}
	}).done(function(data) {
		if (data.resultCd == "00") {
			// ボンディングリスト.
			const boundList = data.boundList;

			$("#bound-list-area").fadeIn(FADE_IN_SPEED);

			// ボンディングが0件の場合.
			if (boundList.length == 0) {
				$("#noRegisteredBoundMsg").fadeIn(FADE_IN_SPEED);
				$("#boundList").prop("disabled", true);
				$("#chart-area").hide();
				return;
			}

			// セレクトリストにボンディングを追加.
			boundList.forEach(function(d) {
				$("#boundList").append("<option value='" + d.boundName + "'>" + d.boundName + " (" + d.vpsName + ")" + "</option>");
			});

			// 正常時ハンドラ呼び出し.
			successHandler();
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました (" + jqXHR.status + " " + jqXHR.statusText + ")");
	})
}

/**
 * 集計存在日一覧取得要求を送信します.
 * @param successHandle {Function} 正常時処理.
 */
function requestExist(successHandler) {
	// ボンディング名取得.
	const bound = $("#boundList").val();

	// 集計存在日一覧取得要求送信.
	$.ajax({
		type: "POST",
		url: "/aggregateExist",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Content-Type": "application/json",
			"Accept": "application/json"
		},
		data: JSON.stringify({
			"boundName": bound
		})
	}).done(function(data) {
		if (data.resultCd == "00") {
			
			$("#chart-area").fadeIn(FADE_IN_SPEED);

			// データが0件の場合.
			if (data.dateList == 0) {
				// チャートを非表示.
				$("#chart-controll").hide();
				// チャートの高さの最小値をクリア.
				$("#chart").css("min-height", "");
				// メッセージ表示.
				$("#noDataMsg").fadeIn(FADE_IN_SPEED);
				return;
			}

			// チャート更新時にスクロール位置が変わらぬよう高さの最小値を設定.
			$("#chart").css("min-height", CHART_HEIGTH);
			// チャートの表示.
			$("#chart-controll").fadeIn(FADE_IN_SPEED);

			existDateList = data.dateList;

			successHandler();
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました (" + jqXHR.status + " " + jqXHR.statusText + ")");
	})
}

/**
 * カレンダーの初期化を行います.
 * @param {Date} date 日付.
 */
function initCalendar(date) {
	$("#calendar").val(formatViewDate(date));

	let config = {
		locale: "ja",
		altInput: true,
		altFormat: "Y/m/d(D)",
		altInputClass: "altInput form-control",
		disableMobile: "true",
		plugins: [
			new monthSelectPlugin({
				altFormat: "Y/m",
				dateFormat: "Y-m"
			})
		]
	};

	// データがある月をカレンダーにて指定可能にする.
	let statusExistMonthFirstDate = [];
	if (existDateList != null) {
		existDateList.forEach(function(existDate) {
			const monthFirstDate = new Date(existDate);
			monthFirstDate.setDate(1);
			if ($.inArray(monthFirstDate, statusExistMonthFirstDate) != 1) {
				statusExistMonthFirstDate.push(monthFirstDate);
			}
		});
		config.enable = statusExistMonthFirstDate;
	}

	flatpickr("#calendar", config);
}

/**
 * カレンダーの更新を行います.
 * @param {ViewType} viewType 表示種別.
 */
function updateCalendar(viewType) {
	let config = {
		locale: "ja",
		altInput: true,
		altFormat: "Y/m/d(D)",
		altInputClass: "altInput form-control",
		disableMobile: "true",
		enable: existDateList
	};

	switch (viewType) {
		case VIEW_TYPE.DATE:
			flatpickr("#calendar", config);
			break;

		case VIEW_TYPE.WEEK:
			config.altInput = false;
			config.plugins = [
				new weekSelect({})
			];
			config.onChange = [
				function() {
					// カレンダー設定値取得.
					const date = this.selectedDates[0];
					const weekStartEndDate = getWeekStartEndDate(date);

					// カレンダー表示値を更新.
					$("#calendar").val(formatViewDate(weekStartEndDate[0]) + " ～ " + formatViewDate(weekStartEndDate[1]));
				}
			];

			// 現在のカレンダー設定値取得.
			const weekStartEndDate = getWeekStartEndDate(new Date($("#calendar").val()));

			// 選択日にデータがない場合は一番近い未来日を指定.
			let date = new Date(weekStartEndDate[0]);
			while (true) {
				const strDate = formatSimpleDate(date);
				let resultDate = null;
				for (let i = 0; i < existDateList.length; i++) {
					const existDate = existDateList[i].substring(0, 10);
					if (existDate == strDate) {
						resultDate = existDate;
						break;
					}
				}
				if (resultDate != null) {
					date = new Date(resultDate);
					break;
				}
				date.setDate(date.getDate() + 1);
			}
			config.defaultDate = formatSimpleDate(date);

			flatpickr("#calendar", config);

			// カレンダー表示値を更新.
			$("#calendar").val(formatViewDate(weekStartEndDate[0]) + " ～ " + formatViewDate(weekStartEndDate[1]));

			break;

		case VIEW_TYPE.MONTH:
			// データがある月をカレンダーにて指定可能にする.
			let statusExistMonthFirstDate = [];
			if (existDateList != null) {
				existDateList.forEach(function(existDate) {
					const monthFirstDate = new Date(existDate);
					monthFirstDate.setDate(1);
					if ($.inArray(monthFirstDate, statusExistMonthFirstDate) != 1) {
						statusExistMonthFirstDate.push(monthFirstDate);
					}
				});
				config.enable = statusExistMonthFirstDate;
			}

			config.plugins = [
				new monthSelectPlugin({
					altFormat: "Y/m",
					dateFormat: "Y-m"
				})
			];
			flatpickr("#calendar", config);

			break;
	}
}

/**
 * チャートの初期化を行います.
 */
function initChart() {
	generateChart(VIEW_TYPE.MONTH);
}

/**
 * チャートを更新します.
 */
function updateChart() {
	let columns = [];

	for (let i = 0; i < CHART_COLUMS.length; i++) {
		columns[i] = [...CHART_COLUMS[i]];
	}
	
	if (connectionCountMap != null) {
		Object.keys(connectionCountMap).forEach(function(key) {
			columns[0].push(new Date(key));
			columns[1].push(connectionCountMap[key]);
		});
	}

	for (const key in newConnectionCountMap) {
		const date = new Date(key);
		for (let i = 1; i < columns[0].length; i++) {
			if (columns[0][i].getTime() === date.getTime()) {
				columns[2].push(newConnectionCountMap[key]);
			} else {
				columns[2].push(0);
			}
		}
	}
	
	chart.load({
		columns: columns
	});
}

/**
 * チャートを生成します.
 * @param viewType {ViewType} 表示種別.
 */
function generateChart(viewType) {
	let format = null;
	
	switch(viewType) {
		case VIEW_TYPE.DATE:
			format = formatViewTime;
			break;
		default:
			format = formatViewDate;
			break;
	}
	
	chart = c3.generate({
		bindto: "#chart",
		size: {
			height: CHART_HEIGTH
		},
		data: {
			x: "x",
			columns: CHART_COLUMS,
			// type: "area-step",
			hide: null
		},
		axis: {
			x: {
				type: "timeseries",
				tick: {
					rotate: 75,
					multiline: false,
					format: (date) => format(date)
				}
			}
		}
	});
}

/**
 * 処理結果モーダルを表示します.
 * @param {string} msg メッセージ.
 */
function showProcessResultModal(msg) {
	$("#msg-modal-btn-ok").hide();
	$("#msg-modal-btn-cancel").text("閉じる");
	$("#msg-modal-body").html(msg);
	MSG_MODAL.show();
}

/**
 * 日付型オブジェクトを文字列日付(yyyy/MM/dd)にフォーマットします.
 * @param {Date} date 日付型オブジェクト.
 * @returns 日付文字列(yyyy/MM/dd).
 */
function formatDate(date) {
	return date
		.toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit",day: "2-digit"})
		.substring(0, 10);
}

/**
 * 日付型オブジェクトを文字列日付(yyyy-MM-dd)にフォーマットします.
 * @param {Date} date 日付型オブジェクト.
 * @returns 日付文字列(yyyy-MM-dd).
 */
function formatSimpleDate(date) {
	return date
		.toLocaleDateString("ja-JP", {year: "numeric",month: "2-digit",day: "2-digit"})
		.replaceAll("/", "-")
		.substring(0, 10);
}

/**
 * 日付型オブジェクトを画面表示用の文字列時間(HH:mm)にフォーマットします.
 * @param {Date} date 日付型オブジェクト.
 * @returns 時間文字列(HH:mm).
 */
function formatViewTime(date) {
	return padStartZero(date.getHours()) + ":" + padStartZero(date.getMinutes())
}

/**
 * 日付型オブジェクトを画面表示用の文字列日付(yyyy/MM/dd(E))にフォーマットします.
 * @param {Date} date 日付型オブジェクト.
 * @returns 日付文字列(yyyy/MM/dd(E)).
 */
function formatViewDate(date) {
	return padStartZero(date.getFullYear())
		+ "/"
		+ padStartZero((date.getMonth() + 1))
		+ "/"
		+ padStartZero(date.getDate())
		+ "("
		+ getJpDay(date)
		+ ")";
}

/**
 * 数値の先頭桁を0埋めした文字列を返します(2桁).
 * @param {number} num 数値. 
 * @returns 処理後文字列.
 */
function padStartZero(num) {
	return num.toString().padStart(2, "0");
}

/**
 * 表示種別を解析します.
 * @param {ViewType} textViewType 文字列表示種別.
 * @returns 表示種別.
 */
function parseViewType(textViewType) {
	let type = VIEW_TYPE.DATE;
	switch (textViewType) {
		case "week":
			type = VIEW_TYPE.WEEK;
			break;
		case "month":
			type = VIEW_TYPE.MONTH;
			break;
		case "year":
			type = VIEW_TYPE.YEAR;
			break;
	}
	return type;
}

/**
 * 指定日の週の開始日、終了日を取得します.
 * @param {Date} date 指定日.
 * @returns {Array} 週の開始日, 週の終了日.
 */
function getWeekStartEndDate(date) {
	const weekNumber = getMondayBasedWeekNumber(date);

	date.setDate(date.getDate() - weekNumber);
	const weekStart = new Date(date.getTime());

	date.setDate(date.getDate() + 6);
	const weekEnd = new Date(date.getTime());

	return [weekStart, weekEnd];
}

/**
 * 曜日を和名表記で取得します.
 * @param {Date} date 日付.
 * @param {string} 曜日(和名表記).
 */
function getJpDay(date) {
	const weekNumber = getMondayBasedWeekNumber(date);

	switch (weekNumber) {
		case 0:
			return "月";
		case 1:
			return "火";
		case 2:
			return "水";
		case 3:
			return "木";
		case 4:
			return "金";
		case 5:
			return "土";
		case 6:
			return "日";
		default:
			return null;
	}
}

/**
 * 月曜始まりの日付番号を取得します.
 * @param {number} date 日付.
 * @returns {number} 日付番号.
 */
function getMondayBasedWeekNumber(date) {
	return date.getDay() === 0 ? 6 : date.getDay() - 1;
}