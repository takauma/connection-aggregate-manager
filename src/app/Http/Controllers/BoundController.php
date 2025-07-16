<?php

namespace App\Http\Controllers;

use App\Models\BoundMst;
use App\Models\AggregateConf;
use App\Constants\ResultCdConstants;
use App\Http\Requests\BoundDeleteRequest;
use App\Http\Requests\BoundListRequest;
use App\Http\Requests\BoundRegistRequest;
use App\Http\Requests\BoundUpdateRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ボンディングコントローラー.
 * @author Soma Takahashi
 */
class BoundController extends Controller
{
	/**
	 * ボンディング管理画面を表示します.
	 * @return View ボンディング管理画面.
	 */
	public function index(): View
	{
		return view("bound");
	}

	/**
	 * ボンディング一覧を取得します.
	 * @param BoundListRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function list(BoundListRequest $request): JsonResponse
	{
		// ソートキー.
		$sortkey = $request->input("sortKey");
		if ($sortkey == null || $sortkey == "") {
			$sortkey = "bound_name";
		}

		// 順序指定.
		$order = $request->input("order");
		if ($order == null || $order == "") {
			$order = "ASC";
		}

		try {
			// ボンディング一覧を取得.
			$rows = BoundMst::where(["delete_flg" => "0"])->orderBy($sortkey, $order)->get();

			// 応答情報作成.
			$boundInfoList = array();
			foreach ($rows as $row) {
				array_push($boundInfoList, [
					"createdDatetime" => $row["created_datetime"],
					"boundName" => $row["bound_name"],
					"vpsName" => $row["vps_name"],
					"vpsKey" => $row["vps_key"],
					"vpsHost" => $row["vps_host"],
					"omrVersion" => $row["omr_version"],
					"configVersion" => $row["config_version"],
					"omrRemoteUrl" => $row["omr_remote_url"],
					"swRemoteUrl" => $row["sw_remote_url"],
					"apRemoteUrl" => $row["ap_remote_url"],
					"omrSshForwardPort" => $row["omr_ssh_forward_port"],
					"router39_1Id" => $row["router_39_1_id"],
					"router40_1Id" => $row["router_40_1_id"],
					"router41_1Id" => $row["router_41_1_id"],
					"router42_1Id" => $row["router_42_1_id"],
					"router43_1Id" => $row["router_43_1_id"],
					"router44_1Id" => $row["router_44_1_id"],
					"router45_1Id" => $row["router_45_1_id"],
					"modifiedDatetime" => $row["modified_datetime"]
				]);
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"boundInfoList" => $boundInfoList
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
	 * ボンディング登録を行います.
	 * @param BoundRegistRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function regist(BoundRegistRequest $request): JsonResponse
	{
		try {
			// トランザクション.
			DB::transaction(function() use($request) {
				// パラメータ取得.
				$boundName = $request->input("boundName");
				$vpsName = $request->input("vpsName");
				$vpsKey = $request->input("vpsKey");
				$vpsHost = $request->input("vpsHost");
				$omrVersion = $request->input("omrVersion");
				$configVersion = $request->input("configVersion");
				$omrRemoteUrl = $request->input("omrRemoteUrl");
				$swRemoteUrl = $request->input("swRemoteUrl");
				$apRemoteUrl = $request->input("apRemoteUrl");
				$omrSshForwardPort = $request->input("omrSshForwardPort");
				$router39_1Id = $request->input("router39_1Id");
				$router40_1Id = $request->input("router40_1Id");
				$router41_1Id = $request->input("router41_1Id");
				$router42_1Id = $request->input("router42_1Id");
				$router43_1Id = $request->input("router43_1Id");
				$router44_1Id = $request->input("router44_1Id");
				$router45_1Id = $request->input("router45_1Id");

				// アプリケーション名.
				$appName = config("app.name");
				// ユーザーID.
				$userId = Auth::user()["user_id"];

				// ボンディングマスタテーブルレコード登録.
				BoundMst::insert([
					"created_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"created_program" => $appName,
					"created_user" => $userId,
					"bound_name" => $boundName,
					"vps_name" => $vpsName,
					"vps_key" => $vpsKey,
					"vps_host" => $vpsHost,
					"omr_version" => $omrVersion,
					"config_version" => $configVersion,
					"omr_remote_url" => $omrRemoteUrl,
					"sw_remote_url" => $swRemoteUrl,
					"ap_remote_url" => $apRemoteUrl,
					"omr_ssh_forward_port" => $omrSshForwardPort,
					"router_39_1_id" => $router39_1Id,
					"router_40_1_id" => $router40_1Id,
					"router_41_1_id" => $router41_1Id,
					"router_42_1_id" => $router42_1Id,
					"router_43_1_id" => $router43_1Id,
					"router_44_1_id" => $router44_1Id,
					"router_45_1_id" => $router45_1Id,
					"delete_flg" => "0"
				]);

				// 集計設定テーブルレコード登録.
				AggregateConf::insert([
					"created_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
					"created_program" => $appName,
					"created_user" => $userId,
					"bound_name" => $boundName,
					"aggregate_flg" => "0",
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
	 * ボンディング更新を行います.
	 * @param BoundUpdateRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function update(BoundUpdateRequest $request): JsonResponse
	{
		try {
			// パラメータ取得.
			$boundName = $request->input("boundName");
			$vpsName = $request->input("vpsName");
			$vpsKey = $request->input("vpsKey");
			$vpsHost = $request->input("vpsHost");
			$omrVersion = $request->input("omrVersion");
			$configVersion = $request->input("configVersion");
			$omrRemoteUrl = $request->input("omrRemoteUrl");
			$swRemoteUrl = $request->input("swRemoteUrl");
			$apRemoteUrl = $request->input("apRemoteUrl");
			$omrSshForwardPort = $request->input("omrSshForwardPort");
			$router39_1Id = $request->input("router39_1Id");
			$router40_1Id = $request->input("router40_1Id");
			$router41_1Id = $request->input("router41_1Id");
			$router42_1Id = $request->input("router42_1Id");
			$router43_1Id = $request->input("router43_1Id");
			$router44_1Id = $request->input("router44_1Id");
			$router45_1Id = $request->input("router45_1Id");

			// ボンディングマスタテーブルレコード更新.
			BoundMst::where([
				"bound_name" => $boundName
			])->update([
				"modified_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
				"modified_program" => config("app.name"),
				"modified_user" => Auth::user()["user_id"],
				"vps_name" => $vpsName,
				"vps_key" => $vpsKey,
				"vps_host" => $vpsHost,
				"omr_version" => $omrVersion,
				"config_version" => $configVersion,
				"omr_remote_url" => $omrRemoteUrl,
				"sw_remote_url" => $swRemoteUrl,
				"ap_remote_url" => $apRemoteUrl,
				"omr_ssh_forward_port" => $omrSshForwardPort,
				"router_39_1_id" => $router39_1Id,
				"router_40_1_id" => $router40_1Id,
				"router_41_1_id" => $router41_1Id,
				"router_42_1_id" => $router42_1Id,
				"router_43_1_id" => $router43_1Id,
				"router_44_1_id" => $router44_1Id,
				"router_45_1_id" => $router45_1Id,
				"delete_flg" => "0"
			]);

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
	 * ボンディング削除を行います.
	 * @param BoundDeleteRequest $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function delete(BoundDeleteRequest $request): JsonResponse
	{
		try {
			// パラメータ取得.
			$boundName = $request->input("boundName");

			// トランザクション.
			DB::transaction(function() use($boundName) {
				// ボンディングマスタテーブルレコード削除.
				BoundMst::where(["bound_name" => $boundName])->delete();

				// ボンディングマスタテーブルレコード削除.
				AggregateConf::where(["bound_name" => $boundName])->delete();
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
}