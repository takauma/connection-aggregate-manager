<!DOCTYPE html>
<html>
	<head>
		@include("meta")
		@vite(['resources/sass/app.scss','resources/js/app.js'])
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/plugins/monthSelect/style.min.css">
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/common.css').'?'.time() }}">
		<link rel="stylesheet" type="text/css" href=" {{ asset('/css/aggregate.css').'?'.time() }}">
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/ja.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/plugins/weekSelect/weekSelect.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
		<script type="module" src="{{ asset('/js/aggregate.js').'?'.time() }}"></script>
		<title>ボンディング接続数集計管理</title>
	</head>
	<body>
		@include("header")
		<div id="contents" class="container-fluid">
			<div id="bound-list-area">
				<div id="bound-list-title">ボンディング選択</div>
				<div id="bound-list-block" class="d-flex align-items-center">
					<select id="boundList" size="1" class="form-control">
					</select>
					<div id="noRegisteredBoundMsg">ボンディングが未登録です。</div>
				</div>
			</div>
			<div id="chart-area">
				<div id="chart-title">接続数</div>
				<div id="chart-wrap">
					<div id="noDataMsg">データが存在しません。</div>
					<form id="chart-controll">
						<select id="viewType" size="1" class="form-control">
							<option value="month" selected>月</option>
							<option value="week">週</option>
							<option value="date">日</option>
						</select>
						<input id="calendar" type="text" class="form-control">
					</form>

					<div id="chart"></div>
				</div>
			</div>
			@include("msgModal")
		</div>
	</body>
</html>