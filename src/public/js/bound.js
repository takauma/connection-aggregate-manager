/** プロセス種別. */
const PROCESS_TYPE = Object.freeze({
	/** 削除. */
	DELETE: Symbol("delete"),
	/** ログアウト. */
	LOGOUT: Symbol("logout")
});

/** 編集プロセス種別. */
const EDIT_PROCESS_TYPE = Object.freeze({
	/** 登録. */
	REGIST: Symbol("regist"),
	/** 編集. */
	EDIT: Symbol("edit")
})

/** メッセージモーダル. */
const MSG_MODAL = new bootstrap.Modal(document.getElementById("msg-modal"));
/** 編集モーダル. */
const EDIT_MODAL = new bootstrap.Modal(document.getElementById("edit-modal"));

/** プロセス種別. */
let processType = null;

/** ボンディング情報リスト. */
let boundInfoList = [];
/** コピー中フラグ. */
let runningCopyFlg = false;
/** 編集プロセス種別. */
let editProcessType = null;

// 画面ロード後イベント.
$(function() {
	// 表示画面リンク強調.
	$("#bound-link").addClass("nav-active");

	// ボンディング一覧取得要求送信.
	requestList(function() {
		// テーブル作成.
		createTable();
	});
});

// 登録ボタン押下時処理.
$(document).on("click", "#btn-regist", function() {
	// 前回開いたモーダルが編集だった場合は入力情報をクリアする.
	if (editProcessType == EDIT_PROCESS_TYPE.EDIT) {
		clearInput();
		clearError();
	}

	// 編集プロセス種別設定.
	editProcessType = EDIT_PROCESS_TYPE.REGIST;

	// モーダル表示.
	$("#labelBoundName").text("ボンディング名 (必須)");
	$("#inputBoundName").prop("disabled", false);
	$("#edit-modal-title").text("ボンディング新規登録");
	$("#modal-body-edit").show();
	$("#modal-body-detail").hide();
	$("#submit").text("登録");
	$("#clear").show();
	$("#submit").show();
	EDIT_MODAL.show();
});

// 編集ボタン押下時処理.
$(document).on("click", ".btn-edit", function() {
	// 編集プロセス種別設定.
	editProcessType = EDIT_PROCESS_TYPE.EDIT;

	// ボンディング名取得.
	const boundName = $(this).val();
	// ボンディング情報取得.
	const info = boundInfoList[boundName];

	// ボンディング情報をモダールに設定.
	$("#inputBoundName").val(boundName);
	$("#inputVpsName").val(info.vpsName);
	$("#inputVpsKey").val(info.vpsKey);
	$("#inputVpsHost").val(info.vpsHost);
	$("#inputOmrVersion").val(info.omrVersion);
	$("#inputConfigVersion").val(info.configVersion);
	$("#inputOmrRemoteUrl").val(info.omrRemoteUrl);
	$("#inputSwRemoteUrl").val(info.swRemoteUrl);
	$("#inputApRemoteUrl").val(info.apRemoteUrl);
	$("#inputApRemoteUrl").val(info.apRemoteUrl);
	$("#inputOmrSshForwardPort").val(info.omrSshForwardPort);
	$("#inputRouter39_1Id").val(info.router39_1Id);
	$("#inputRouter40_1Id").val(info.router40_1Id);
	$("#inputRouter41_1Id").val(info.router41_1Id);
	$("#inputRouter42_1Id").val(info.router42_1Id);
	$("#inputRouter43_1Id").val(info.router43_1Id);
	$("#inputRouter44_1Id").val(info.router44_1Id);
	$("#inputRouter45_1Id").val(info.router45_1Id);

	// モーダル表示.
	clearError();
	$("#labelBoundName").text("ボンディング名");
	$("#inputBoundName").prop("disabled", true);
	$("#edit-modal-title").text("ボンディング情報編集");
	$("#modal-body-edit").show();
	$("#modal-body-detail").hide();
	$("#submit").text("更新");
	$("#clear").hide();
	$("#submit").show();
	EDIT_MODAL.show();
});

// 詳細ボタン押下時処理.
$(document).on("click", ".btn-detail", function() {
	// ボンディング名取得.
	const boundName = $(this).val();
	// ボンディング情報取得.
	const info = boundInfoList[boundName];

	// ボンディング情報をモーダルに設定.
	$("#detailBoundName").text(boundName);
	$("#detailVpsName").text(info.vpsName);
	$("#detailVpsKey").text(info.vpsKey);
	$("#detailVpsHost").text(info.vpsHost);
	$("#detailOmrVersion").text(emptyToHyphen(info.omrVersion));
	$("#detailConfigVersion").text(emptyToHyphen(info.configVersion));
	$("#detailOmrRemoteUrl").html(urlToATag(info.omrRemoteUrl));
	$("#detailSwRemoteUrl").html(urlToATag(info.swRemoteUrl));
	$("#detailApRemoteUrl").html(urlToATag(info.apRemoteUrl));
	$("#detailOmrSshForwardPort").text(emptyToHyphen(info.omrSshForwardPort));
	$("#detailRouter39_1Id").text(emptyToHyphen(info.router39_1Id));
	$("#detailRouter40_1Id").text(emptyToHyphen(info.router40_1Id));
	$("#detailRouter41_1Id").text(emptyToHyphen(info.router41_1Id));
	$("#detailRouter42_1Id").text(emptyToHyphen(info.router42_1Id));
	$("#detailRouter43_1Id").text(emptyToHyphen(info.router43_1Id));
	$("#detailRouter44_1Id").text(emptyToHyphen(info.router44_1Id));
	$("#detailRouter45_1Id").text(emptyToHyphen(info.router45_1Id));

	// 値が未設定の項目はコピーボタンを非表示にする.
	const detailValues = $(".detail-value");
	detailValues.each(function (_, detailValue) {
		if ($(detailValue).text() == "-") {
			$(detailValue).next().children().hide();
		}
	});

	// モーダル表示.
	$("#edit-modal-title").text("ボンディング詳細");
	$("#modal-body-detail").show();
	$("#modal-body-edit").hide();
	$("#clear").hide();
	$("#submit").hide();
	EDIT_MODAL.show();
});

// 削除ボタン押下時処理.
$(document).on("click", ".btn-delete", function() {
	// プロセス種別設定.
	processType = PROCESS_TYPE.DELETE;

	// ボンディング名取得.
	const boundName = $(this).val();

	// モーダルのOKボタンにボンディング名を設定.
	$("#msg-modal-btn-ok").val(boundName);

	// モーダル表示.
	$("#msg-modal-btn-ok").show();
	$("#msg-modal-btn-cancel").text("キャンセル");
	$("#msg-modal-body").text("[" + boundName + "] を削除してよろしいですか？");
	MSG_MODAL.show();
});

// 編集モーダルSubmitボタン押下時処理.
$(document).on("click", "#submit", function() {
	if (editProcessType == EDIT_PROCESS_TYPE.REGIST) {
		requestRegist();
	} else if (editProcessType == EDIT_PROCESS_TYPE.EDIT) {
		requestUpdate();
	}
});

// メッセージモーダルOKボタン押下時処理.
$(document).on("click", "#msg-modal-btn-ok", function() {
	// プロセス種別判定.
	if (processType == PROCESS_TYPE.DELETE) {
		// ボンディング名取得.
		const boundName = $(this).val();

		// 削除要求送信.
		requestDelete(boundName);
	} else if (processType == PROCESS_TYPE.LOGOUT) {
		logout();
	}
});

// コピーボタン押下時処理.
$(document).on("click", ".copy", function() {
	// コピー中フラグが有効の場合は処理を行わない.
	if (runningCopyFlg) {
		return;
	}

	// コピー中フラグ有効化.
	runningCopyFlg = true;

	// ボタンロック.
	$(".copy").each(function(_, copy) {
		$(copy).prop("disabled", true);
	});

	// コピーする値を取得.
	const value = $(this).parent().prev().text();
	
	// クリップボードに内容をコピー.
	if (navigator.clipboard) {
		navigator.clipboard.writeText(value);
	} else {
		// navigator.clipbordが使えない場合(スキームがhttpの時など).

		// テキストエリアを作成.
		$(this).parent().prev().append("<textarea id='copyText' readonly>" + value + "</textarea>");
		const textarea = $("#copyText")[0];

		// 作成したテキストエリアをフォーカス.
		textarea.select();
	
		// テキストエリアの文字列を全選択.
		var range = document.createRange();
		range.selectNodeContents(textarea);
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
		textarea.setSelectionRange(0, 999999);
	
		// execCommandでコピーを実行.
		document.execCommand("copy");
	
		// 作成したテキストエリアを削除.
		(textarea).remove();
	}

	// トースト表示.
	$("#copy-msg").show();
	
	// トースト表示後、指定時間非同期で待機.
	new Promise(resolve => setTimeout(resolve, 1000)).then(function() {
		// トーストフェードアウト.
		$("#copy-msg").fadeOut(400);
		// フェードアウト完了を待機.
		new Promise(resolve => setTimeout(resolve, 500)).then(function() {
			// ボタンロック解除.
			$(".copy").each(function(_, copy) {
				$(copy).prop("disabled", false);
			});
			// コピー中フラグ無効化.
			runningCopyFlg = false;
		});
	});
});

// クリアボタン押下時処理.
$(document).on("click", "#clear", function () {
	clearInput();
	clearError();
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

	// ボンディング一覧取得要求送信.
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

/**
 * ログアウト処理を行います.
 */
function logout() {
	location.href = "/logout";
}

/**
 * ボンディングリスト取得要求を送信します.
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
		url: "/boundList",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData)
	}).done(function(data) {
		if (data.resultCd == "00") {
			boundInfoList = [];
			data.boundInfoList.forEach(function(info) {
				boundInfoList[info.boundName] = {
					createdDatetime: info.createdDatetime,
					vpsName: info.vpsName,
					vpsKey: info.vpsKey,
					vpsHost: info.vpsHost,
					omrVersion: info.omrVersion,
					configVersion: info.configVersion,
					omrRemoteUrl: info.omrRemoteUrl,
					swRemoteUrl: info.swRemoteUrl,
					apRemoteUrl: info.apRemoteUrl,
					omrSshForwardPort: info.omrSshForwardPort,
					router39_1Id: info.router39_1Id,
					router40_1Id: info.router40_1Id,
					router41_1Id: info.router41_1Id,
					router42_1Id: info.router42_1Id,
					router43_1Id: info.router43_1Id,
					router44_1Id: info.router44_1Id,
					router45_1Id: info.router45_1Id,
					modifiedDatetime: info.modifiedDatetime
				};
		});
			successHandler();
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * ボンディング登録要求を送信します.
 */
function requestRegist() {
	// 要求データ作成.
	let sendData = {
		boundName: $("#inputBoundName").val(),
		vpsName: $("#inputVpsName").val(),
		vpsKey: $("#inputVpsKey").val(),
		vpsHost: $("#inputVpsHost").val(),
		omrVersion: $("#inputOmrVersion").val(),
		configVersion: $("#inputConfigVersion").val(),
		omrRemoteUrl: $("#inputOmrRemoteUrl").val(),
		swRemoteUrl: $("#inputSwRemoteUrl").val(),
		apRemoteUrl: $("#inputApRemoteUrl").val(),
		omrSshForwardPort: $("#inputOmrSshForwardPort").val(),
		router39_1Id: $("#inputRouter39_1Id").val(),
		router40_1Id: $("#inputRouter40_1Id").val(),
		router41_1Id: $("#inputRouter41_1Id").val(),
		router42_1Id: $("#inputRouter42_1Id").val(),
		router43_1Id: $("#inputRouter43_1Id").val(),
		router44_1Id: $("#inputRouter44_1Id").val(),
		router45_1Id: $("#inputRouter45_1Id").val()
	}

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/boundRegist",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData),
	}).done(function(data) {
		if (data.resultCd == "00") {
			EDIT_MODAL.hide();
			showProcessResultModal("[" + $("#inputBoundName").val() + "] を登録しました。");
			clearInput();
			clearError();

			// ボンディング一覧取得要求送信.
			requestList(function() {
				// テーブル作成.
				createTable();
			});
		} else if (data.resultCd == "10") {
			showErrorMsg(data.errors);
		} else {
			EDIT_MODAL.hide();
			showProcessResultModal("登録に失敗しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		EDIT_MODAL.hide();
		showProcessResultModal("登録に失敗しました。<br>原因: サーバー通信失敗 (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * ボンディング更新要求を送信します.
 */
function requestUpdate() {
	// 要求データ作成.
	let sendData = {
		boundName: $("#inputBoundName").val(),
		vpsName: $("#inputVpsName").val(),
		vpsKey: $("#inputVpsKey").val(),
		vpsHost: $("#inputVpsHost").val(),
		omrVersion: $("#inputOmrVersion").val(),
		configVersion: $("#inputConfigVersion").val(),
		omrRemoteUrl: $("#inputOmrRemoteUrl").val(),
		swRemoteUrl: $("#inputSwRemoteUrl").val(),
		apRemoteUrl: $("#inputApRemoteUrl").val(),
		omrSshForwardPort: $("#inputOmrSshForwardPort").val(),
		router39_1Id: $("#inputRouter39_1Id").val(),
		router40_1Id: $("#inputRouter40_1Id").val(),
		router41_1Id: $("#inputRouter41_1Id").val(),
		router42_1Id: $("#inputRouter42_1Id").val(),
		router43_1Id: $("#inputRouter43_1Id").val(),
		router44_1Id: $("#inputRouter44_1Id").val(),
		router45_1Id: $("#inputRouter45_1Id").val()
	}

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/boundUpdate",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData),
	}).done(function(data) {
		if (data.resultCd == "00") {
			EDIT_MODAL.hide();
			showProcessResultModal("[" + $("#inputBoundName").val() + "] を更新しました。");
			clearInput();
			clearError();

			// ボンディング一覧取得要求送信.
			requestList(function() {
				// テーブル作成.
				createTable();
			});
		} else if (data.resultCd == "10") {
			showErrorMsg(data.errors);
		} else {
			EDIT_MODAL.hide();
			showProcessResultModal("更新に失敗しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		EDIT_MODAL.hide();
		showProcessResultModal("更新に失敗しました。<br>原因: サーバー通信失敗 (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * ボンディング削除要求を送信します.
 * @param {string} boundName ボンディング名.
 */
function requestDelete(boundName) {
	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/boundDelete",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify({
			boundName: boundName
		})
	}).done(function(data) {
		if (data.resultCd == "00") {
			showProcessResultModal("[" + boundName + "] を削除しました。");

			// ボンディング一覧取得要求送信.
			requestList(function() {
				// テーブル作成.
				createTable();
			});
		} else {
			showProcessResultModal("削除処理に失敗しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("削除処理に失敗しました。<br>原因: サーバー通信失敗 (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * テーブルを作成します.
 */
function createTable() {
	$("#records").empty();
	for (const boundName in boundInfoList) {
		let createdDatetime = boundInfoList[boundName].createdDatetime;
		let modifiedDatetime = emptyToHyphen(boundInfoList[boundName].modifiedDatetime);
		if (createdDatetime != "-") {
			createdDatetime = formatDate(new Date(boundInfoList[boundName].createdDatetime));
		}
		if (modifiedDatetime != "-") {
			modifiedDatetime = formatDate(new Date(boundInfoList[boundName].modifiedDatetime));
		} 
		$("#records").append(
			"<tr id='" + boundName + "'>"
				+ "<th>" + boundName + "</th>"
				+ "<th>" + boundInfoList[boundName].vpsName + "</th>"
				+ "<th>" + emptyToHyphen(boundInfoList[boundName].vpsHost) + "</th>"
				+ "<th>" + emptyToHyphen(boundInfoList[boundName].omrVersion) + "</th>"
				+ "<th>" + emptyToHyphen(boundInfoList[boundName].configVersion) + "</th>"
				+ "<th>" + createdDatetime + "</th>"
				+ "<th>" + modifiedDatetime + "</th>"
				+ "<th class='th-detail'><div class='d-flex justify-content-center'><button id='detail_" + boundName + "' class='btn-detail btn btn-secondary' value='" + boundName + "'>詳細</button></div></th>"
				+ "<th class='th-edit'><div class='d-flex justify-content-center'><button id='btn_" + boundName + "' class='btn-edit btn btn-success' value='" + boundName + "'>編集</button></div></th>"
				+ "<th class='th-delete'><div class='d-flex justify-content-center'><button id='btn_" + boundName + "' class='btn-delete btn btn-danger' value='" + boundName + "'>削除</button></div></th>"
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
 * エラーメッセージを表示します.
 * @param {array} errors エラーリスト.
 */
function showErrorMsg(errors) {
	clearError();
	$(".errorArea").each(function(_, item) {
		let id = $(item).attr("id").replaceAll("error", "");
		id = id.charAt(0).toLowerCase() + id.substring(1, id.length);

		// 編集の場合はボンディング名の項目を除外する.
		if (editProcessType == EDIT_PROCESS_TYPE.EDIT) {
			if (id == "boundName") {
				return;
			}
		}

		const arr = errors[id];
		let msg = "";
		if (!arr) {
			$(item).prev().addClass("is-valid")
			return;
		}
		arr.forEach(function(m) {
			msg += m + "<br>";
		});
		if (msg != "") {
			msg = msg.substring(0, msg.length - 4);
		}
		$(item).prev().addClass("is-invalid")
		$(item).html(msg);
	});
}

/**
 * 文字列がnullまたは空文字の場合にハイフンへ置き換えます.
 * @param {string} str 文字列. 
 */
function emptyToHyphen(str) {
	if (str == null || str == "") {
		return "-"
	};
	return str;
}

/**
 * 日付型オブジェクトを文字列日付にフォーマットします.
 * @param {Date} date 日付型オブジェクト.
 * @returns 日付文字列.
 */
function formatDate(date) {
	return padStartZero(date.getFullYear())
		+ "/"
		+ padStartZero((date.getMonth() + 1))
		+ "/"
		+ padStartZero(date.getDate())
		+ "("
		+ getJpDay(date)
		+ ")"
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

/**
 * URLをaタグフォーマットに変換します.
 * @param {string} str 文字列.
 * @returns {string} aタグフォーマット文字列.
 */
function urlToATag(str) {
	str = emptyToHyphen(str);
	if (str != "-") {
		str = "<a href='" + str + "' target='_blank'>" + str + "</a>";
	}
	return str;
}

/**
 * 入力値をクリアします.
 */
function clearInput() {
		$("#inputBoundName").val("");
		$("#inputVpsName").val("");
		$("#inputVpsKey").val("");
		$("#inputVpsHost").val("");
		$("#inputOmrVersion").val("");
		$("#inputConfigVersion").val("");
		$("#inputOmrRemoteUrl").val("");
		$("#inputSwRemoteUrl").val("");
		$("#inputApRemoteUrl").val("");
		$("#inputApRemoteUrl").val("");
		$("#inputOmrSshForwardPort").val("");
		$("#inputRouter39_1Id").val("");
		$("#inputRouter40_1Id").val("");
		$("#inputRouter41_1Id").val("");
		$("#inputRouter42_1Id").val("");
		$("#inputRouter43_1Id").val("");
		$("#inputRouter44_1Id").val("");
		$("#inputRouter45_1Id").val("");
}

/**
 * エラーをクリアします.
 */
function clearError() {
	$(".modalInput").removeClass("is-valid").removeClass("is-invalid");
	$(".errorArea").empty();
}