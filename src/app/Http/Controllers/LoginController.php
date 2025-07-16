<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserMst;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Constants\AppConstants;
use App\Models\UserRole;
use App\Models\UserRoleView;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * ログインコントローラー.
 * @author Soma Takahashi
 */
class LoginController extends Controller
{
	/**
	 * ログイン画面を取得します.
	 * @return View ログイン画面.
	 */
	public function index(): View
	{
		// 管理者アカウントが存在しない場合は追加.
		$result = UserMst::where("user_id", AppConstants::ROOT_USER_ID)->count();
		if ($result == 0) {
			$appName = config("app.name");
			UserMst::insert([
				"created_program" => $appName,
				"user_id" => AppConstants::ROOT_USER_ID,
				"user_name" => "管理者",
				"password" => Hash::make(AppConstants::ROOT_USER_PASSWORD),
				"delete_flg" => "0"
			]);
			UserRole::insert([
				"created_program" => $appName,
				"user_id" => AppConstants::ROOT_USER_ID,
				"role_id" => "01",
				"delete_flg" => "0"
			]);
		}

		return view("login");
	}

	/**
	 * ログイン処理を実行します.
	 * @param Request $request リクエスト.
	 * @return View|RedirectResponse 成功時: ボンディング閲覧画面, 失敗時: ログイン画面.
	 */
	public function login(Request $request): View|RedirectResponse
	{
		// フォーム情報取得.
		$userId = $request->input("user_id");
		$password = $request->input("password");
		
		// バリデーションチェック.
		try {
			$request->validate([
				"user_id" => "required|max:64",
				"password" => "required|max:64"
			]);
		} catch (Exception $e) {
			return $this->getErorrResponse();
		}

		// ログイン認証.
		$result = Auth::attempt([
			"user_id" => $userId,
			"password" => $password
		]);
		if (!$result) {
			return $this->getErorrResponse();
		}

		//セッションの再作成.
		$request->session()->regenerate();

		// 権限取得.
		$roleRow = UserRoleView::select(["role_name", "role_id"])->where(["user_id" => $userId])->first();

		// 権限が取得できなかった場合.
		if ($roleRow == null) {
			// TODO 本来はエラー画面に飛ばしたがとりあえず...
			return $this->getErorrResponse();
		}

		// 権限をセッションに追加.
		$request->session()->put("roleId", $roleRow["role_id"]);
		$request->session()->put("roleName", $roleRow["role_name"]);

		// 集計画面へ遷移.
		return redirect()->intended("/aggregate");
	}
	
	/**
	 * エラー応答を取得します.
	 */
	private function getErorrResponse()
	{
		return view("login", ["error" => true]);
	}
}