@php
use App\Constants\AppConstants;
use App\Constants\RoleConstants;
$rootUserId = AppConstants::ROOT_USER_ID;
$defaultRole = RoleConstants::VIEW_ONLY;
@endphp
<!DOCTYPE html>
<html>
	<head>
		@include("meta")
		@vite(['resources/sass/app.scss','resources/js/app.js'])
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/common.css').'?'.time() }}">
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/user.css').'?'.time() }}">
		<script type="module" src="{{ asset('/js/user.js').'?'.time() }}"></script>
		<title>ボンディング接続数集計管理</title>
	</head>
	<body>
		@include("header")
		<input id="rootUserId" type="hidden" value="{{ $rootUserId }}">
		<input id="defaultRole" type="hidden" value="{{ $defaultRole }}">
		<nav class="navbar bg-dark justify-content-end">
			<div id="right-form" class="d-flex justify-content-end">
				<button id="btn-regist" class="btn btn-primary">新規登録</button>
			</div>
		</nav>
		<div id="contents">
			<!-- ボンディング一覧テーブル -->
			<table class="table table-striped table-bordered">
				<thead class="table-dark">
					<tr class="table-bordered">
						<th>
							<a id="sortUserId" class="sort" href="javascript:void(0);" value="user_id">ユーザーID</a>
							<i id="sort-icon" class="bi bi-sort-alpha-down" value="ASC"></i>
						</th>
						<th>
							<a id="sortUserName" class="sort" href="javascript:void(0);" value="user_name">ユーザー名</a>
						</th>
						<th>
							<a id="sortRoleName" class="sort" href="javascript:void(0);" value="role_name">権限</a>
						</th>
						<th>
							<a id="sortCreatedDatetime" class="sort" href="javascript:void(0);" value="created_datetime">登録日</a>
						</th>
						<th>
							<a id="sortModifiedDatetime" class="sort" href="javascript:void(0);" value="modified_datetime">更新日</a>
						</th>
						<th class="th-edit"></th>
						<th class="th-delete"></th>
					</tr>
				</thead>
				<tbody id="records">
				</tbody>
			</table>
			<!-- 編集モーダル. -->
			<div id="edit-modal" class="modal fade" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered modal-xl modal-fullscereen modal-dialog-scrollable">
					<div class="modal-content">
						<div class="modal-header">
							<h5 id="edit-modal-title" class="modal-title"></h5>
						</div>
						<div class="modal-body">
							<div id="modal-body-edit">
								<div class="form">
									<div class="form-group">
										<label id="labelUserId" for="inputUserId" class="col-form-label"></label>
										<input id="inputUserId" class="modalInput form-control" maxlength="64" disabled>
										<small id="errorUserId" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputUserName" class="col-form-label">ユーザー名 (必須)</label>
										<input id="inputUserName" class="modalInput form-control" maxlength="32">
										<small id="errorUserName" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputPassword" class="col-form-label">パスワード (必須)</label>
										<input id="inputPassword" class="modalInput form-control" type="password" maxlength="32" autocomplete="off">
										<small id="errorPassword" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputPasswordConfirm" class="col-form-label">パスワード確認 (必須)</label>
										<input id="inputPasswordConfirm" class="modalInput form-control" type="password" maxlength="32" autocomplete="off">
										<small id="errorPasswordConfirm" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRoleId" class="col-form-label">権限 (必須)</label>
										<select id="inputRoleId" class="modalInput form-control"></select>
										<small id="errorRoleId" class="errorArea invalid-feedback"></small>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button class="btn btn-secondary" type="button" data-bs-dismiss="modal">閉じる</button>
							<button id="clear" class="btn btn-danger" type="button">クリア</button>
							<button id="submit" class="btn btn-primary" type="button">登録</button>
						</div>
					</div>
				</div>
			</div>
			@include("msgModal")
		</div>
	</body>
</html>