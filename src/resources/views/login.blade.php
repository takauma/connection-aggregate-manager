<html>
	<head>
		@include("meta")
		<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/minty/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
		<link rel="stylesheet" type="text/css" href="{{ asset('/css/login.css').'?'.time() }}">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="{{ asset('/js/bootstrap-show-password.js') }}"></script>
		<script type="text/javascript" src="{{ asset('/js/login.js').'?'.time() }}"></script>
		<title>ボンディング接続数集計管理</title>
	</head>
	<body>
		<div id="contents-wrap" class="d-flex flex-column justify-content-center align-items-center">
			<div id="contents" class="d-flex flex-column justify-content-center align-items-center">
				<div id="header" class="d-inline-flex flex-column align-items-center">
					<h1>ボンディング接続数集計管理</h1>
				</div>
				
				<form method="post" action="/login" class="flex-grow-1 d-flex flex-column align-items-center">
					@csrf
					<div id="input" class="flex-grow-1 d-flex flex-column">
						<div class="form-group column">
							<label for="userId" class="col col-form-label">ユーザーID</label>
							<div class="col">
								<input id="userId" class="form-control" type="text" name="user_id">
							</div>
						</div>
						
						<div class="form-group column">
							<label for="password" class="col col-form-label">パスワード</label>
							<div class="col">
								<input id="password" class="form-control" type="password" data-toggle="password" name="password" autocomplete="off">
							</div>
						</div>
					</div>
					
					<div id="msg" class="d-flex flex-column">
						@if(isset($error))
						<p>ユーザー名、またはパスワードが一致しません。</p>
						@endif
					</div>
				</form>
						
				<div id="footer" class="d-flex">
					<input id="send" class="btn btn-primary col" type="button" value="ログイン">
				</div>
			</div>
		</div>
	</body>
</html>