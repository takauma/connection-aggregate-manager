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
document.getElementById("msg-modal").addEventListener("hidden.bs.modal", event => onCloseMsgModal());

/** 編集モーダル. */
const EDIT_MODAL = new bootstrap.Modal(document.getElementById("edit-modal"));

/** プロセス種別. */
let processType = null;

/** ユーザー情報リスト. */
let userInfoList = [];
/** 編集プロセス種別. */
let editProcessType = null;
/** ログインユーザの変更フラグ. */
let changedLoginUserFlg = false;

// 画面ロード後イベント.
$(function() {
	// 表示画面リンク強調.
	$("#user-link").addClass("nav-active");
	// 権限一覧取得.
	requestRole(function(infoList) {
		infoList.forEach(function(info) {
			const id = info.roleId;
			const name = info.roleName;
			$("#inputRoleId").append("<option value='" + id + "'" + (id == defaultRole ? " selected" : "") + ">" + name + "</option>");
		});
	});

	// ユーザー一覧取得要求送信.
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
	$("#labelUserId").text("ユーザーID (必須)");
	$("#inputUserId").prop("disabled", false);
	$("#edit-modal-title").text("ユーザ新規登録");
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

	// ユーザーID取得.
	const userId = $(this).val();

	// モーダルのOKボタンにユーザーIDを設定.
	$("#submit").val(userId);

	// ユーザー情報取得.
	const info = userInfoList[userId];

	// ユーザー情報をモダールに設定.
	$("#inputUserId").val(userId);
	$("#inputUserName").val(info.userName);
	$("#inputPassword").val("");
	$("#inputPasswordConfirm").val("");
	$("#inputRoleId").val(info.roleId);

	// モーダル表示.
	clearError();
	$("#labelUserId").text("ユーザーID");
	$("#inputUserId").prop("disabled", true);
	$("#edit-modal-title").text("ユーザー情報編集");
	$("#modal-body-edit").show();
	$("#modal-body-detail").hide();
	$("#submit").text("更新");
	$("#clear").hide();
	$("#submit").show();
	EDIT_MODAL.show();
});

// 削除ボタン押下時処理.
$(document).on("click", ".btn-delete", function() {
	// プロセス種別設定.
	processType = PROCESS_TYPE.DELETE;

	// ユーザーID取得.
	const userId = $(this).val();

	// モーダルのOKボタンにユーザーIDを設定.
	$("#msg-modal-btn-ok").val(userId);

	// モーダル表示.
	$("#msg-modal-btn-ok").show();
	$("#msg-modal-btn-cancel").text("キャンセル");
	$("#msg-modal-body").text("[" + userId + "] を削除してよろしいですか？");
	MSG_MODAL.show();
});

// 編集モーダルSubmitボタン押下時処理.
$(document).on("click", "#submit", function() {
	if (editProcessType == EDIT_PROCESS_TYPE.REGIST) {
		requestRegist();
	} else if (editProcessType == EDIT_PROCESS_TYPE.EDIT) {
		requestUpdate($(this).val());
	}
});

// メッセージモーダルOKボタン押下時処理.
$(document).on("click", "#msg-modal-btn-ok", function() {
	// プロセス種別判定.
	if (processType == PROCESS_TYPE.DELETE) {
		// ユーザーID取得.
		const userId = $(this).val();

		// 削除要求送信.
		requestDelete(userId);
	} else if (processType == PROCESS_TYPE.LOGOUT) {
		logout();
	}
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

	// ユーザー一覧取得要求送信.
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

// メッセージモーダルクローズ時処理.
function onCloseMsgModal() {
	// ログインユーザーの変更の場合はログイン画面へ遷移.
	if (changedLoginUserFlg) {
		location.href = "/";
	}
}

/**
 * ログアウト処理を行います.
 */
function logout() {
	location.href = "/logout";
}

/**
 * ユーザーリスト取得要求を送信します.
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
		url: "/userList",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData)
	}).done(function(data) {
		if (data.resultCd == "00") {
			userInfoList = [];
			data.userInfoList.forEach(function(info) {
				userInfoList[info.userId] = {
					createdDatetime: info.createdDatetime,
					id: info.id,
					userName: info.userName,
					roleId: info.roleId,
					roleName: info.roleName,
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
 * ユーザー登録要求を送信します.
 */
function requestRegist() {
	// 要求データ作成.
	let sendData = {
		userId: $("#inputUserId").val(),
		userName: $("#inputUserName").val(),
		password: $("#inputPassword").val(),
		passwordConfirm: $("#inputPasswordConfirm").val(),
		roleId: $("#inputRoleId").val()
	}

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/userRegist",
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
			showProcessResultModal("[" + $("#inputUserId").val() + "] を登録しました。");
			clearInput();
			clearError();

			// ユーザー一覧取得要求送信.
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
 * ユーザー更新要求を送信します.
 */
function requestUpdate(userId) {
	// 要求データ作成.
	let sendData = {
		id: userInfoList[userId].id,
		userId: userId,
		userName: $("#inputUserName").val(),
		password: $("#inputPassword").val(),
		passwordConfirm: $("#inputPasswordConfirm").val(),
		roleId: $("#inputRoleId").val(),
	}

	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/userUpdate",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify(sendData),
	}).done(function(data) {
		if (data.resultCd == "00") {
			// ログインユーザーの変更の場合はフラグを立てておく.
			changedLoginUserFlg = data.changedLoginUserFlg;

			EDIT_MODAL.hide();
			showProcessResultModal("[" + $("#inputUserId").val() + "] を更新しました。");
			clearInput();
			clearError();

			if (!changedLoginUserFlg) {
				// ユーザー一覧取得要求送信.
				requestList(function() {
					// テーブル作成.
					createTable();
				});
			}
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
 * ユーザー削除要求を送信します.
 * @param {string} userId ユーザーID.
 */
function requestDelete(userId) {
	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/userDelete",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
		data: JSON.stringify({
			id: userInfoList[userId].id,
			userId: userId
		})
	}).done(function(data) {
		if (data.resultCd == "00") {
			// ログインユーザーの変更の場合はフラグを立てておく.
			changedLoginUserFlg = data.changedLoginUserFlg;

			showProcessResultModal("[" + userId + "] を削除しました。");

			if (!changedLoginUserFlg) {
				// ユーザー一覧取得要求送信.
				requestList(function() {
					// テーブル作成.
					createTable();
				});
			}
		} else {
			showProcessResultModal("削除処理に失敗しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("削除処理に失敗しました。<br>原因: サーバー通信失敗 (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * 権限一覧取得要求を送信します.
 */
function requestRole(successHandler) {
	// 要求送信.
	$.ajax({
		type: "POST",
		url: "/role",
		dataType: "json",
		headers: {
			"X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
			"Accept": "application/json",
			"Content-Type": "application/json"
		},
	}).done(function(data) {
		if (data.resultCd == "00") {
			successHandler(data.roleInfoList);
		} else {
			showProcessResultModal("システムエラーが発生しました。<br>結果コード: " + data.resultCd);
		}
	}).fail(function(jqXHR) {
		showProcessResultModal("サーバー通信に失敗しました (" + jqXHR.status + " " + jqXHR.statusText + ")");
	});
}

/**
 * テーブルを作成します.
 */
function createTable() {
	$("#records").empty();
	for (const userId in userInfoList) {
		let createdDatetime = userInfoList[userId].createdDatetime;
		let modifiedDatetime = emptyToHyphen(userInfoList[userId].modifiedDatetime);
		if (createdDatetime != "-") {
			createdDatetime = formatDate(new Date(userInfoList[userId].createdDatetime));
		}
		if (modifiedDatetime != "-") {
			modifiedDatetime = formatDate(new Date(userInfoList[userId].modifiedDatetime));
		}
		const rootUserId = $("#rootUserId").val();
		$("#records").append(
			"<tr id='" + userId + "'>"
				+ "<th>" + userId + "</th>"
				+ "<th>" + userInfoList[userId].userName + "</th>"
				+ "<th>" + userInfoList[userId].roleName + "</th>"
				+ "<th>" + createdDatetime + "</th>"
				+ "<th>" + modifiedDatetime + "</th>"
				+ "<th class='th-edit'><div class='d-flex justify-content-center'><button id='btn_" + userId + "' class='btn-edit btn btn-success' value='" + userId + "'" + (userId == rootUserId ? " disabled": "") + ">編集</button></div></th>"
				+ "<th class='th-delete'><div class='d-flex justify-content-center'><button id='btn_" + userId + "' class='btn-delete btn btn-danger' value='" + userId + "'" + (userId == rootUserId ? " disabled": "") + ">削除</button></div></th>"
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
		// ID取得.
		let id = $(item).attr("id").replaceAll("error", "");
		id = id.charAt(0).toLowerCase() + id.substring(1, id.length);

		// 編集の場合はユーザーIDの項目を除外する.
		if (editProcessType == EDIT_PROCESS_TYPE.EDIT) {
			if (id == "userId") {
				return;
			}
		}

		// 対象項目のエラーリスト取得.
		const itemErrors = errors[id];
		let msg = "";

		// エラーが存在しない場合は入力欄をSUCCESS状態にする.
		if (!itemErrors) {
			$(item).prev().addClass("is-valid");
			return;
		}

		// エラーメッセージのHTML作成.
		itemErrors.forEach(function(m) {
			msg += m + "<br>";
		});
		if (msg != "") {
			msg = msg.substring(0, msg.length - 4);
		}

		// 項目の入力欄をERROR状態にする.
		$(item).prev().addClass("is-invalid");

		if ($("#inputPassword").val() != $("#inputPasswordConfirm").val()) {
			$("#inputPassword").addClass("is-invalid");
		}

		// パスワード系のエラーの場合.
		if ((id == "password" && msg != "") || (id == "passwordConfirm" && msg != "")) {
			// パスワード、パスワード確認の入力クリア.
			$("#inputPassword").val("");
			$("#inputPasswordConfirm").val("");
			// パスワード、パスワード確認の入寮欄をERROR状態にする.
			$("#inputPassword").addClass("is-invalid");
			$("#inputPasswordConfirm").addClass("is-invalid");
		}

		// エラーメッセージのHTML設定.
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
 * 入力値をクリアします.
 */
function clearInput() {
		$("#inputUserId").val("");
		$("#inputUserName").val("");
		$("#inputPassword").val("");
		$("#inputPasswordConfirm").val("");
		$("#inputRoleId").val($("#defaultRole").val());
}

/**
 * エラーをクリアします.
 */
function clearError() {
	$(".modalInput").removeClass("is-valid").removeClass("is-invalid");
	$(".errorArea").empty();
}