<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoundMst;
use App\Models\AggregateConf;
use App\Constants\ResultCdConstants;
use App\Http\Requests\AggregateSettingChangeRequest;
use App\Http\Requests\AggregateSettingListRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * 集計設定コントローラー.
 * @author Soma Takahashi
 */
class AggregateSettingController extends Controller
{
	/**
	 * 集計設定画面を表示します.
	 * @return View 集計設定画面.
	 */
	public function index(): View
	{
		return view("aggregateSetting");
	}

	/**
	 * 集計ボンディング一覧を取得します.
	 * @param AggregateSettingListRequest $request リクエスト.
	 * @param JsonResponse レスポンス.
	 */
	public function list(AggregateSettingListRequest $request): JsonResponse
	{
		// ソートキー.
		$sortkey = $request->input("sortKey");
		if ($sortkey == null || $sortkey == "" || $sortkey === "aggregate_flg") {
			$sortkey = "bound_name";
		}

		// 順序指定.
		$order = $request->input("order");
		if ($order == null || $order == "") {
			$order = "ASC";
		}
		
		try {
			// 集計ボンディング一覧.
			$boundList = array();

			// ボンディングマスタテーブルレコード取得.
			$boundMstRows = BoundMst::select(["bound_name", "vps_name"])->where(["delete_flg" => "0"])->orderBy($sortkey, $order)->get();

			// 集計設定テーブルレコード取得.
			foreach ($boundMstRows as $row) {
				$confRow = AggregateConf::select(["bound_name", "aggregate_flg"])->where(["bound_name" => $row["bound_name"], "delete_flg" => "0"])->first();
				if ($confRow != null) {
					array_push($boundList, [
						"boundName" => $confRow["bound_name"],
						"vpsName" => $row["vps_name"],
						"aggregateFlg" => $confRow["aggregate_flg"]
					]);
				}
			}

			// 集計設定フラグの場合は配列を並び替え.
			if ($sortkey === "aggregate_flg") {
				uasort($boundList, function ($a, $b) use ($order) {
					if ($order === "ASC") {
						return strcmp($a["aggregateFlg"], $b["aggregateFlg"]);
					}
					return strcmp($b["aggregateFlg"], $a["aggregateFlg"]);
				 });
			}

			// 正常応答返却.
			return response()->json([
				"resultCd" => ResultCdConstants::SUCCESS,
				"boundList" => $boundList
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
	 * ボンディング集計設定変更処理をを行います.
	 * @param Request $request リクエスト.
	 * @return JsonResponse レスポンス.
	 */
	public function change(AggregateSettingChangeRequest $request): JsonResponse
	{
		try {
			// 要求データ種痘.
			$dataList = $request->json()->all();

			// トランザクション.
			DB::transaction(function () use ($dataList) {
				foreach ($dataList as $data) {
					$boundName = $data["boundName"];
					$aggregateFlg = $data["aggregateFlg"];

					AggregateConf::where([
						"bound_name" => $boundName
					])->update([
						"modified_datetime" => Carbon::now()->format('Y-m-d H:i:s.v'),
						"modified_program" => config("app.name"),
						"modified_user" => Auth::user()["user_id"],
						"aggregate_flg" => $aggregateFlg
					]);
				}
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
