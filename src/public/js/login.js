// 画面ロード後処理.
$(function() {
	// 平文パスワード入力のname属性削除.
	$(".input-group>input[type='text']").removeAttr("name");
});

// 送信ボタン押下時処理.
$(document).on("click", "#send", submit);

// パスワード表示ボタン押下時処理.
$(document).on("click", ".input-group-append>button", function() {
	// エスケープ・平文パスワード入力のname属性設定切り替え.
	const typeText = $(".input-group>input[type='text']");
	const typePassword = $(".input-group>input[type='password']");
	if (typeText.is(":visible")) {
		typePassword.removeAttr("name");
		typeText.attr("name", "password");
	} else {
		typeText.removeAttr("name");
		typePassword.attr("name", "password");
	}
});

// テキスト入力時キー押下時処理.
$(document).on("keypress", "input", function(e) {
	// 入力でエンターキーが押下されたらフォーム送信する.
	if (e.keyCode === 13) {
		submit();
	}
});

/**
 * フォーム送信処理を行います.
 */
function submit() {
	$("form").submit();
}