/** プロセス種別. */
const PROCESS_TYPE = Object.freeze({
	/** 変更. */
	CHANGE: Symbol("change"),
	/** ログアウト. */
	LOGOUT: Symbol("logout")
});

/** メッセージモーダル. */
const MSG_MODAL = new bootstrap.Modal(document.getElementById("msg-modal"));

/** プロセス種別. */
let processType = null;

/** 修正前データ. */
let beforeDataMap = {};
/** 修正後データ. */
let afterDataMap = {};
/** VPSマップ. */
let vpsMap = {};
/** 変更カウント. */
let modifiedCount = 0;

// 画面ロード後イベント.
$(function() {
	// リンク強調.
	$("#aggregate-setting-link").addClass("nav-active");

	requestList(function() {
		createTable();
	})
});

// 変更反映ボタン押下時処理.
$(document).on("click", "#submit", function() {
	// プロセス種別設定.
	processType = PROCESS_TYPE.CHANGE;
	// メッセージ作成.
	let msg = "以下内容でボンディングの集計設定を変更します。<br>よろしいですか？<br><br>";
	for (const bound in afterDataMap) {
		msg += bound + " ⇒ " + (afterDataMap[bound] == 1 ? "集計する" : "集計しない") + "<br>"
	}
	msg = msg.substring(0, msg.length - 1);

	// モーダル表示.
	$("#msg-modal-btn-ok").show();
	$("#msg-modal-btn-cancel").text("キャンセル");
	$("#msg-modal-body").html(msg);
	MSG_MODAL.show();
});

// メッセージモーダルOKボタン押下時処理.
$(document).on("click", "#msg-modal-btn-ok", function() {
	// プロセス種別判定.
	if (processType == PROCESS_TYPE.CHANGE) {
		// 集計ボンディング設定変更要求送信.
		requestChange();
	} else if (processType == PROCESS_TYPE.LOGOUT) {
		logout();
	}
});

// クリアボタン押下時処理.
$(document).on("click", "#clear", function() {
	clear();
});

// ソートリンク押下時処理.
$(document).on("click", ".sort", function() {
	// ID取得.
	const id = $(this).attr("id");
	// ソートキー取得.
	const key = $(this).attr("value");

	// 順序取得.
	let order = $(this).next().attr("value");
	if (!order) {
		order = "ASC";
	} else {
		order = (order == "ASC" ? "DESC" : "ASC");
	}

	// 集計設定一覧取得要求送信.
	requestList(
		function() {
			// テーブル作成.
			createTable();
			
			// ソートアイコン設定.
			$("#sort-icon").remove();
			$("#" + id).parent().append(
				"<i id='sort-icon' class='bi "
					+ (order == "ASC" ? "bi-sort-alpha-down" : "bi-sort-alpha-down-alt")
					+ "' value='" + order + "'></i>"
			);
		},
		key,
		order
	);
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

// トグル変化時処理.
$(document).on("change", ".aggregateToggle", function() {
	// 変更のあったトグル.
	const toggle = $(this);
	// ボンディング名.
	const boundName = toggle.val();
	// 集計フラグ.
	const aggregateFlg = (toggle.prop("checked") ? 1 : 0);
	
	// 変更内容を保存.
	afterDataMap[boundName] = aggregateFlg;

	// 変更がある場合は変更有無にチェックを入れる.
	if (beforeDataMap[boundName] != aggregateFlg) {
		$("#" + boundName + ">.modifiedCheck").empty();
		$("#" + boundName + ">.modifiedCheck").append(
			"<div class='icon-check-wrap'>"
				+ "<div class='icon-check'></div>"
			+ "</div>"
		);
		modifiedCount++;
	} else {
		$("#" + boundName + ">.modifiedCheck").empty();
		delete afterDataMap[boundName];
		modifiedCount--;
	}

	// 変更が1以上の場合は設定反映ボタンを活性化.
	$("#submit").prop("disabled", modifiedCount == 0);
});

/**
 * ログアウト処理を行います.
 */
function logout() {
	location.href = "/logout";
}

/**
 * 集計ボンディング取得要求を送信します.
 * @param {function} successHandler 正常時ハンドラ.
 * @param {string} sortKey ソートキー.
 * @param {string} order 順序指定.
 */
function requestList(successHandler, sortKey, order) {
	let sendData = {};
	if (sortKey) {
		sendData["sortKey"] = sortKey;
	}
	if (order) {
		sendData["order"] = order;
	}

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/aggregateSettingList",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData)
	}).done(function(data) {
		if (data.resultCd == "00") {
			beforeDataMap = {};
			vpsMap = {};
			data.boundList.forEach(function(d) {
				beforeDataMap[d.boundName] = d.aggregateFlg;
				vpsMap[d.boundName] = d.vpsName;
		});
			successHandler();
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました。");
	})
}

/**
 * 集計ボンディング設定変更要求を送信します.
 */
function requestChange() {
	// 要求データ作成.
	let sendData = [];
	for (const boundName in afterDataMap) {
		sendData.push({
			boundName: boundName,
			aggregateFlg: afterDataMap[boundName]
		});
	};

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/aggregateSettingChange",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData)
	}).done(function(data) {
		$("#submit").prop("disabled", true);
		afterDataMap = {};
		modifiedCount = 0;
		requestList(function() {
			createTable();
			if (data.resultCd == "00") {
				showProcessResultModal("設定が反映されました。");
			} else {
				showProcessResultModal("設定反映に失敗しました。<br>結果コード: " + data.resultCd);
			}
		});
	}).fail(function(jqXHR) {
		showProcessResultModal("設定反映に失敗しました。<br>原因: サーバー通信失敗 (" + jqXHR.status + " " + jqXHR.statusText + ")");
	})
}

/**
 * テーブルを作成します.
 */
function createTable() {
	$("#records").empty();
	for (const boundName in beforeDataMap) {
		$("#records").append(
			"<tr id='" + boundName + "'>"
				+ "<th>" + boundName + "</th>"
				+ "<th>" + vpsMap[boundName] + "</th>"
				+ "<th>"
					+ "<div class='activeSwitch from-check form-switch d-flex justify-content-center'>"
						+ "<input id='toggle_" + boundName + "' class='aggregateToggle form-check-input' value='" + boundName + "' type='checkbox' role='switch' " + ((beforeDataMap[boundName] == 1) ? "checked": "") + ">"
					+ "</div>"
				+ "</th>"
				+ "<th id='check_" + boundName + "' class='modifiedCheck'></th>"
			+ "</tr>"
		);
	};
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
 * 変更内容のクリアを行います.
 */
function clear() {
	for (const boundName in beforeDataMap) {
		$("#toggle_" + boundName).prop("checked", beforeDataMap[boundName] == 1);
		$("#check_" + boundName).empty();
	}
	$("#submit").prop("disabled", true);
	afterDataMap = {};
	modifiedCount = 0;
}