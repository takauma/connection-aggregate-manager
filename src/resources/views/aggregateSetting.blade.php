<!DOCTYPE html>
<html>
	<head>
		@include("meta")
		@vite(['resources/sass/app.scss','resources/js/app.js'])
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/common.css').'?'.time() }}">
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/aggregateSetting.css').'?'.time() }}">
		<script type="module" src="{{ asset('/js/aggregateSetting.js').'?'.time() }}"></script>
		<title>ボンディング接続数集計管理</title>
	</head>
	<body>
		@include("header")
		<nav class="navbar bg-dark justify-content-end">
			<div id="right-form" class="d-flex justify-content-end">
				<button id="submit" class="btn btn-primary" disabled>設定反映</button>
				<button id="clear" class="btn btn-secondary">クリア</button>
			</div>
		</nav>
		<div id="contents">
			<table class="table table-striped table-bordered">
				<thead class="table-dark">
					<tr class="table-bordered">
						<th class="col-4">
							<a id="sortBoundName" class="sort" href="javascript:void(0);" value="bound_name">ボンディング名</a>
							<i id="sort-icon" class="bi bi-sort-alpha-down" value="ASC"></i>
						</th>
						<th class="col-4">
							<a id="sortVpsName" class="sort" href="javascript:void(0);" value="vps_name">VPS名</a>
						</th>
						<th class="th-toggle col-2">
							<a id="sortAggregateFlg" class="sort" href="javascript:void(0);" value="aggregate_flg">集計設定</a>
						</th>
						<th class="th-toggle col-2">
							変更有無
						</th>
					</tr>
				</thead>
				<tbody id="records">
				</tbody>
			</table>
			@include("msgModal")
		</div>
	</body>
</html>