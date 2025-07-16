<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Models\UserMst;
use App\Constants\ResultCdConstants;
use App\Http\Requests\UserDeleteRequest;
use App\Http\Requests\UserListRequest;
use App\Http\Requests\UserRegistRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\RoleMst;
use App\Models\UserRole;
use App\Models\UserRoleView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * ユーザー管理コントローラー.
 * @author Soma Takahashi
 */
class UserController extends Controller
{
	/**
	 * ユーザー管理画面を表示します.
	 * @return View ユーザー管理画面.
	 */
	public function index(): View
	{
		return view("user");
	}

	/**
	 * ユーザー一覧を取得します.
	 * @param UserListRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function list(UserListRequest $request): JsonResponse
	{
		// ソートキー.
		$sortkey = $request->input("sortKey");
		if ($sortkey == null || $sortkey == "" || $sortkey === "role_name") {
			$sortkey = "user_id";
		}

		// 順序指定.
		$order = $request->input("order");
		if ($order == null || $order == "") {
			$order = "ASC";
		}

		try {
			// ユーザーマスタレコード取得.
			$userRows = UserMst::where(["delete_flg" => "0"])->orderBy($sortkey, $order)->get();

			// 応答情報作成.
			$userInfoList = array();
			foreach ($userRows as $userRow) {
				// ユーザー権限ビューレコード取得.
				$roleRow = UserRoleView::where(["user_id" => $userRow["user_id"]])->first();

				array_push($userInfoList, [
					"createdDatetime" => $userRow["created_datetime"],
					"id" => $userRow["id"],
					"userId" => $userRow["user_id"],
					"userName" => $userRow["user_name"],
					"roleId" => $roleRow["role_id"],
					"roleName" => $roleRow["role_name"],
					"modifiedDatetime" => $userRow["modified_datetime"]
				]);
			}

			// 権限名の場合は配列を並び替え.
			if ($sortkey === "role_name") {
				uasort($boundList, function ($a, $b) use ($order) {
					if ($order === "ASC") {
						return strcmp($a["roleName"], $b["roleName"]);
					}
					return strcmp($b["roleName"], $a["roleName"]);
				 });
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"userInfoList" => $userInfoList
			]);
		} catch (Exception $e) {
			Log::warning("システムエラーが発生しました。");
			report($e);
			return response()->json([
				"resultCd" => ResultCdConstants::SYSTEM_ERROR
			]);
		}
	}

	/**
	 * ユーザー登録を行います.
	 * @param UserRegistRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function regist(UserRegistRequest $request): JsonResponse
	{
		try {
			// トランザクション.
			DB::transaction(function () use ($request) {
				// パラメータ取得.
				$userId = $request->input("userId");
				$userName = $request->input("userName");
				$password = $request->input("password");
				$roleId = $request->input("roleId");

				// アプリケーション名.
				$appName = config("app.name");
				// ログインユーザーID.
				$loginUserId = Auth::user()["user_id"];

				// ボンディングマスタテーブルレコード登録.
				UserMst::insert([
					"created_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"created_program" => $appName,
					"created_user" => $loginUserId,
					"user_id" => $userId,
					"user_name" => $userName,
					"password" => Hash::make($password),
					"delete_flg" => "0"
				]);

				// 集計設定テーブルレコード登録.
				UserRole::insert([
					"created_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"created_program" => $appName,
					"created_user" => $loginUserId,
					"user_id" => $userId,
					"role_id" => $roleId,
					"delete_flg" => "0"
				]);
			});

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS
			]);
		} catch (Exception $e) {
			Log::warning("システムエラーが発生しました。");
			report($e);
			return response()->json([
				"resultCd" => ResultCdConstants::SYSTEM_ERROR
			]);
		}
	}

	/**
	 * ユーザー更新を行います.
	 * @param UserUpdateRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function update(UserUpdateRequest $request): JsonResponse
	{
		// システムデフォルトの管理者が指定されていた場合は操作権限エラー.
		if ($this->isRoot($request->input("id"), $request->input("userId"))) {
			return response()->json([
				"resultCd" => ResultCdConstants::PERMISSION_DENIED
			]);
		}

		try {
			// トランザクション.
			DB::transaction(function () use ($request) {
				// パラメータ取得.
				$id = $request->input("id");
				$userId = $request->input("userId");
				$userName = $request->input("userName");
				$password = $request->input("password");
				$roleId = $request->input("roleId");

				// アプリケーション名.
				$appName = config("app.name");
				// ログインユーザーID.
				$loginUserId = Auth::user()["user_id"];

				// ユーザーマスタテーブルレコード更新.
				UserMst::where([
					"id" => $id
				])->update([
					"modified_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"modified_program" => $appName,
					"modified_user" => $loginUserId,
					"user_name" => $userName,
					"password" => Hash::make($password),
					"delete_flg" => "0"
				]);

				// ユーザー権限テーブルレコード更新.
				UserRole::where([
					"user_id" => $userId
				])->update([
					"modified_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"modified_program" => $appName,
					"modified_user" => $loginUserId,
					"role_id" => $roleId
				]);
			});

			// ログインユーザーの更新の場合はフラグを立てる.
			$changedLoginUserFlg = false;
			if ($request->input("userId") === Auth::user()["user_id"]) {
				$changedLoginUserFlg = true;
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"changedLoginUserFlg" => $changedLoginUserFlg
			]);
		} catch (Exception $e) {
			Log::warning("システムエラーが発生しました。");
			report($e);
			return response()->json([
				"resultCd" => ResultCdConstants::SYSTEM_ERROR
			]);
		}
	}

	/**
	 * ユーザー削除を行います.
	 * @param UserDeleteRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function delete(UserDeleteRequest $request): JsonResponse
	{
		// システムデフォルトの管理者が指定されていた場合は操作権限エラー.
		if ($this->isRoot($request->input("id"), $request->input("userId"))) {
			return response()->json([
				"resultCd" => ResultCdConstants::PERMISSION_DENIED
			]);
		}

		try {
			// パラメータ取得.
			$id = $request->input("id");
			$userId = $request->input("userId");

			// トランザクション.
			DB::transaction(function () use ($id, $userId) {
				// ユーザーマスタテーブルレコード削除.
				UserMst::where(["id" => $id])->delete();

				// ユーザー権限テーブルレコード削除.
				UserRole::where(["user_id" => $userId])->delete();
			});

			// ログインユーザーの削除の場合はフラグを立てる.
			$changedLoginUserFlg = false;
			if ($request->input("userId") === Auth::user()["user_id"]) {
				$changedLoginUserFlg = true;
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"changedLoginUserFlg" => $changedLoginUserFlg
			]);
		} catch (Exception $e) {
			Log::warning("システムエラーが発生しました。");
			report($e);
			return response()->json([
				"resultCd" => ResultCdConstants::SYSTEM_ERROR
			]);
		}
	}

	/**
	 * 権限一覧取得を行います.
	 * @return JsonResponse レスポンス.
	 */
	public function role(): JsonResponse
	{
		try {
			// 権限マスタレコード取得.
			$rows = RoleMst::where(["delete_flg" => "0"])->orderBy("role_id", "desc")->get();

			// 応答情報作成.
			$roleInfoList = array();
			foreach ($rows as $row) {
				array_push($roleInfoList, [
					"roleId" => $row["role_id"],
					"roleName" => $row["role_name"]
				]);
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"roleInfoList" => $roleInfoList
			]);
		} catch (Exception $e) {
			Log::warning("システムエラーが発生しました。");
			report($e);
			return response()->json([
				"resultCd" => ResultCdConstants::SYSTEM_ERROR
			]);
		}
	}

	/**
	 * IDまたはユーザーIDがシステムデフォルトの管理者ユーザーであるかチェックします.
	 * @param string|int $id ID.
	 * @param string $userId ユーザーID.
	 * @return bool 判定結果.
	 */
	private function isRoot(string|int $id, string $userId): bool
	{
		if ($userId === AppConstants::ROOT_USER_ID) {
			return true;
		}

		$row = UserMst::select("id")->where(["user_id" => AppConstants::ROOT_USER_ID])->first();
		if ($id == $row["id"]) {
			return true;
		}

		return false;
	}
}
