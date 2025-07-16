<?php

namespace App\Http\Controllers;

use App\Models\DhcpConnectionLog;
use App\Models\BoundMst;
use App\Constants\ResultCdConstants;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AggregateExistDateListRequest;
use App\Http\Requests\AggregateDataRequest;
use DateTime;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * 集計コントローラー.
 * @author Soma Takahashi
 */
class AggregateController extends Controller
{
    /** 取得種別: 月. */
    private const TYPE_MONTH = "MONTH";
    /** 取得種別: 週. */
    private const TYPE_WEEK = "WEEK";
    /** 取得種別: 日. */
    private const TYPE_DATE = "DATE";

    /**
     * 集計閲覧画面を表示します.
     * @return View 集計閲覧画面.
     */
    public function index(): View
    {
        return view("aggregate");
    }

    /**
     * 集計有効ボンディング一覧を取得します.
     * @return JsonResponse レスポンス.
     */
    public function active(): JsonResponse
    {
        try {
            // ボンディング一覧を取得.
            $rows = BoundMst::select(["bound_name", "vps_name"])->where(["delete_flg" => "0"])->get();
            $boundList = array();
            foreach ($rows as $row) {
                array_push($boundList, ["boundName" => $row["bound_name"], "vpsName" => $row["vps_name"]]);
            }
            return response()->json([
                "resultCd" => ResultCdConstants::SUCCESS,
                "boundList" => $boundList
            ]);
        } catch (Exception $e) {
            Log::warging("システムエラーが発生しました。");
            report($e);
            return response()->json([
                "resultCd" => ResultCdConstants::SYSTEM_ERROR
            ]);
        }
    }

    /**
     * 集計存在日一覧を取得します.
     * @param AggregateExistDateListRequest $request リクエスト.
     * @param JsonResponse レスポンス.
     */
    public function exist(AggregateExistDateListRequest $request): JsonResponse
    {
        try {
            // ボンディング名.
            $boundName = $request->input("boundName");

            // ボンディングの集計データ取得.
            $rows = DhcpConnectionLog::selectRaw("DATE_FORMAT(connection_datetime, '%Y-%m-%d') AS date")
                ->where(["bound_name" => $boundName, "delete_flg" => "0"])
                ->groupBy(["date"])
                ->orderBy("date")
                ->get();

            // 日付リストを作成.
            $dateList = array();
            foreach ($rows as $row) {
                array_push($dateList, $row["date"]);
            }

            return response()->json([
                "resultCd" => ResultCdConstants::SUCCESS,
                "dateList" => $dateList
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
     * 集計データを取得します.
     * @param AggregateDataRequest $request リクエスト.
     * @return JsonResponse レスポンス.
     */
    public function data(AggregateDataRequest $request): JsonResponse
    {
        try {
            // パラメータ取得.
            $boundName = $request->input("boundName");
            $type = $request->input("type");
            $startDate = new DateTime($request->input("startDate"));

            // 月初日付を取得.
            $firstDate = clone $startDate;
            $firstDate->modify("first day of");

            // 月末日付を取得.
            $lastDate = clone $firstDate;
            $lastDate->modify("+1 month");

            // 取得種別が週で1週間後が来月に入っている場合は2ヶ月分のデータを取得する.
            if ($type === AggregateController::TYPE_WEEK) {
                $testDate = clone $startDate;
                $testDate->modify("+1 week");
                if ($startDate->format("m") !== $testDate->format("m")) {
                    $lastDate->modify("+1 month");
                }
            }

            // 集計データ取得.
            $rows = DhcpConnectionLog::select(["connection_datetime", "mac_address"])
                ->where("delete_flg", "0")
                ->where("bound_name", $boundName)
                ->where("connection_datetime", ">=", $firstDate)
                ->where("connection_datetime", "<", $lastDate)
                ->groupBy(["connection_datetime", "mac_address"])
                ->orderBy("connection_datetime")
                ->get();

            // 開始日時を取得.
            $startDate = new DateTime($request->input("startDate"));

            // 終了日時を取得.
            $endDate = clone $startDate;
            if ($type === AggregateController::TYPE_MONTH) {
                // 月指定の場合.
                $endDate->modify("+1 month");
            } else if ($type === AggregateController::TYPE_WEEK) {
                // 週指定の場合.
                $endDate->modify("+7 day");
            } else if ($type === AggregateController::TYPE_DATE) {
                // 日付選択時の場合.
                $endDate->modify("+1 day");
            }

            // 日付フォーマット.
            $format = ($type === AggregateController::TYPE_DATE) ? "Y-m-d H:i:s" : "Y-m-d";

            // 接続数マップ作成.
            $connectionCountMap = array();
            foreach ($rows as $row) {
                // 取得した日時の分からを切り捨てて日時型に変換.
                $value = (new DateTime($row["connection_datetime"]))->format("Y-m-d H:00:00");
                $dateTime = new DateTime($value);

                // 指定日時の範囲外の取得日時だった場合はスキップ.
                if ($dateTime < $startDate || $dateTime >= $endDate) {
                    continue;
                }

                // 日時単位の接続数をカウントする.
                $strDateTime = $dateTime->format($format);
                if (array_key_exists($strDateTime, $connectionCountMap)) {
                    $connectionCountMap[$strDateTime] = intval($connectionCountMap[$strDateTime]) + 1;
                } else {
                    $connectionCountMap[$strDateTime] = 1;
                }
            }

            // 新規接続端末数(月ごと)マップ作成.
            $newConnectionCountMap = array();
            foreach ($this->getGroupByMonthData($rows) as $data) {
                // 取得した日時の分からを切り捨てて日時型に変換.
                $value = $data["connectionDatetime"]->format("Y-m-d H:00:00");
                $dateTime = new DateTime($value);

                // 指定日時の範囲外の取得日時だった場合はスキップ.
                if ($dateTime < $startDate || $dateTime >= $endDate) {
                    continue;
                }

                // 日時単位の接続数をカウントする.
                $strDateTime = $dateTime->format($format);
                if (array_key_exists($strDateTime, $newConnectionCountMap)) {
                    $newConnectionCountMap[$strDateTime] = intval($newConnectionCountMap[$strDateTime]) + 1;
                } else {
                    $newConnectionCountMap[$strDateTime] = 1;
                }
            }

            return response()->json([
                "resultCd" => ResultCdConstants::SUCCESS,
                "connectionCountMap" => $connectionCountMap,
                "newConnectionCountMap" => $newConnectionCountMap
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
     * 月ごとにグループ化したMACアドレスと接続日時の紐づけデータを取得します.
     * 同月且つ、同MACアドレスで複数レコードが存在する場合は一番過去の日付でグループ化されます.
     * @param mixed $rows ボンディング名.
     * @return array 月ごとにグループ化したMACアドレスと接続日時の紐づけデータ.
     */
    private function getGroupByMonthData(mixed $rows): array
    {
        // 同MACアドレスは一番過去の日付のみ取得.
        $list = array();
        foreach ($rows as $row) {
            // MACアドレス
            $macAddress = $row["mac_address"];

            // 接続日時.
            $connectionDatetime = new DateTime($row["connection_datetime"]);

            // 月初日付.
            $firstDate = clone $connectionDatetime;
            $firstDate->modify("first day of");

            // 既に同MACアドレス且つ、同月の要素が存在する場合は要素追加を行わない.
            $isNotExist = true;
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]["macAddress"] === $macAddress && $list[$i]["firstDate"]->format("Y-m-d") == $firstDate->format("Y-m-d")) {
                    $isNotExist = false;
                    break;
                }
            }
            if ($isNotExist) {
                array_push($list, [
                    "macAddress" => $macAddress,
                    "firstDate" => $firstDate,
                    "connectionDatetime" => $connectionDatetime
                ]);
            }
        }
        return $list;
    }
}
