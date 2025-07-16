<?php

namespace App\Constants;

/**
 * 権限定義.
 * @author Soma Takahashi
 */
class RoleConstants {
	private function __construct()
	{
		// No member.
	}
	
	/** 管理者. */
	const ADMIN = "01";
	/** ボンディング管理者. */
	const BOUND_ADMIN = "02";
	/** 閲覧ユーザー. */
	const VIEW_ONLY = "03";
}