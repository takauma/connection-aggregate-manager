<!DOCTYPE html>
<html>
	<head>
		@include("meta")
		@vite(['resources/sass/app.scss','resources/js/app.js'])
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/common.css').'?'.time() }}">
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/bound.css').'?'.time() }}">
		<script type="module" src="{{ asset('/js/bound.js').'?'.time() }}"></script>
		<title>ボンディング接続数集計管理</title>
	</head>
	<body>
		@include("header")
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
							<a id="sortBoundName" class="sort" href="javascript:void(0);" value="bound_name">ボンディング名</a>
							<i id="sort-icon" class="bi bi-sort-alpha-down" value="ASC"></i>
						</th>
						<th>
							<a id="sortVpsName" class="sort" href="javascript:void(0);" value="vps_name">VPS名</a>
						</th>
						<th>
							<a id="sortVpsHost" class="sort" href="javascript:void(0);" value="vps_host">VPSホスト</a>
						</th>
						<th>
							<a id="sortOmrVersion" class="sort" href="javascript:void(0);" value="omr_version">OMR Ver.</a>
						</th>
						<th>
							<a id="sortConfigVersion" class="sort" href="javascript:void(0);" value="config_version">Config Ver.</a>
						</th>
						<th>
							<a id="sortCreatedDate" class="sort" href="javascript:void(0);" value="created_datetime">登録日</a>
						</th>
						<th>
							<a id="sortModifiedDate" class="sort" href="javascript:void(0);" value="modified_datetime">更新日</a>
						</th>
						<th class="th-detail"></th>
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
							<div id="copy-msg">クリップボードにコピーしました</div>
							<div id="modal-body-detail">
								<div class="detail-item">
									<div class="detail-title">ボンディング名</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailBoundName" class="detail-value flex-fill"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">VPS名</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailVpsName" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">VPSキー</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailVpsKey" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">VPSホスト</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailVpsHost" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">OMRバージョン</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailOmrVersion" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">コンフィグバージョン</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailConfigVersion" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">OMR遠隔URL</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailOmrRemoteUrl" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">SW遠隔URL</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailSwRemoteUrl" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">AP遠隔URL</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailApRemoteUrl" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">OMR SSH転送ポート</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailOmrSshForwardPort" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：39.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter39_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：40.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter40_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：41.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter41_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：42.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter42_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：43.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter43_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：44.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter44_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
								<div class="detail-item">
									<div class="detail-title">ルーター：45.1 識別番号</div>
									<div class="d-flex flex-row align-items-center">
										<div id="detailRouter45_1Id" class="detail-value"></div>
										<div class="d-flex flex-row justify-content-end flex-fill">
											<button class="copy btn btn-secondary">コピー</button>
										</div>
									</div>
								</div>
							</div>
							<div id="modal-body-edit">
								<div class="form">
									<div class="form-group">
										<label id="labelBoundName" for="inputBoundName" class="col-form-label"></label>
										<input id="inputBoundName" class="modalInput form-control" maxlength="32" disabled>
										<small id="errorBoundName" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputVpsName" class="col-form-label">VPS名 (必須)</label>
										<input id="inputVpsName" class="modalInput form-control" maxlength="32">
										<small id="errorVpsName" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputVpsKey" class="col-form-label">VPSキー (必須)</label>
										<input id="inputVpsKey" class="modalInput form-control" maxlength="128">
										<small id="errorVpsKey" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputVpsHost" class="col-form-label">VPSホスト (必須)</label>
										<input id="inputVpsHost" class="modalInput form-control" maxlength="64">
										<small id="errorVpsHost" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputOmrVersion" class="col-form-label">OMRバージョン</label>
										<input id="inputOmrVersion" class="modalInput form-control" maxlength="16">
										<small id="errorOmrVersion" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputConfigVersion" class="col-form-label">コンフィグバージョン</label>
										<input id="inputConfigVersion" class="modalInput form-control" maxlength="8">
										<small id="errorConfigVersion" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputOmrRemoteUrl" class="col-form-label">OMR遠隔URL</label>
										<input id="inputOmrRemoteUrl" class="modalInput form-control" maxlength="64">
										<small id="errorOmrRemoteUrl" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputSwRemoteUrl" class="col-form-label">SW遠隔URL</label>
										<input id="inputSwRemoteUrl" class="modalInput form-control" maxlength="64">
										<small id="errorSwRemoteUrl" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputApRemoteUrl" class="col-form-label">AP遠隔URL</label>
										<input id="inputApRemoteUrl" class="modalInput form-control" maxlength="64">
										<small id="errorApRemoteUrl" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputOmrSshForwardPort" class="col-form-label">OMR SSH転送ポート</label>
										<input id="inputOmrSshForwardPort" class="modalInput form-control" maxlength="8">
										<small id="errorOmrSshForwardPort" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter39_1Id" class="col-form-label">ルーター：39.1 識別番号</label>
										<input id="inputRouter39_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter39_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter40_1Id" class="col-form-label">ルーター：40.1 識別番号</label>
										<input id="inputRouter40_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter40_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter41_1Id" class="col-form-label">ルーター：41.1 識別番号</label>
										<input id="inputRouter41_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter41_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter42_1Id" class="col-form-label">ルーター：42.1 識別番号</label>
										<input id="inputRouter42_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter42_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter43_1Id" class="col-form-label">ルーター：43.1 識別番号</label>
										<input id="inputRouter43_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter43_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter44_1Id" class="col-form-label">ルーター：44.1 識別番号</label>
										<input id="inputRouter44_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter44_1Id" class="errorArea invalid-feedback"></small>
									</div>
									<div class="form-group">
										<label for="inputRouter45_1Id" class="col-form-label">ルーター：45.1 識別番号</label>
										<input id="inputRouter45_1Id" class="modalInput form-control" maxlength="64">
										<small id="errorRouter45_1Id" class="errorArea invalid-feedback"></small>
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