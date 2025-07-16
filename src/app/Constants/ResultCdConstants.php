<?php

namespace App\Constants;

/**
 * 結果コード定数定義.
 * @author Soma Takahashi
 */
class ResultCdConstants {
	private function __construct()
	{
		// No member.
	}
	
	/** 正常. */
	public const SUCCESS = "00";
	/** パラメータエラー. */
	public const PARAMERTER_ERROR = "10";
	/* 操作権限エラー. */
	public const PERMISSION_DENIED = "11";
	/** システムエラー. */
	public const SYSTEM_ERROR = "99";
}