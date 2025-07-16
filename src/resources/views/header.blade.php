@php
use App\Constants\RoleConstants;
$userName = Auth::user()->user_name;
$roleId = session('roleId');
$roleName = session('roleName');
@endphp
<nav class="navbar navbar-expand navbar-dark bg-dark">
	<a class="navbar-brand text-light">ボンディング接続数集計管理</a>
	<div class="collapse navbar-collapse">
		<ul class="navbar-nav col">
			<li class="nav-item">
				<a id="aggregate-link" class="nav-link" href="/aggregate" id="navbarDropdownMenuLink">集計閲覧</a>
			</li>
			@if($roleId === RoleConstants::ADMIN or $roleId === RoleConstants::BOUND_ADMIN)
			<li class="nav-item">
				<a id="bound-link" class="nav-link" href="/bound" id="navbarDropdownMenuLink">ボンディング管理</a>
			</li>
			@endif
			@if($roleId === RoleConstants::ADMIN or $roleId === RoleConstants::BOUND_ADMIN)
			<li class="nav-item">
				<a id="aggregate-setting-link" class="nav-link" href="/aggregateSetting" id="navbarDropdownMenuLink">集計設定</a>
			</li>
			@endif
			@if($roleId === RoleConstants::ADMIN)
			<li class="nav-item">
				<a id="user-link" class="nav-link" href="/user" id="navbarDropdownMenuLink">ユーザー管理</a>
			</li>
			@endif
		</ul>
		<div class="form-inline d-flex justify-content-end col-5">
			<ul class="navbar-nav align-items-center">
				<li id="header-li-user" class="nav-item">
					<small><span>ユーザー: </span><span id="header-user">{{ $userName }}</span></small>
				</li>
				<li id="header-li-role" class="nav-item">
					<small><span>権限: </span><span></span id="header-role">{{ $roleName }}</small>
				</li>
				<li id="header-li-logout" class="nav-item">
					<a id="logout" class="nav-link logout-link" href="javascript:void(0);" id="navbarDropdownMenuLink" style="color:#FFFFFF;">ログアウト</a>
				</li>
			</ul>
		</div>
	</div>
</nav>