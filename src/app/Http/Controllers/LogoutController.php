<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * ログアウトコントローラー.
 * @author Soma Takahashi
 */
class LogoutController extends Controller
{
	/**
	 * ログアウト処理を行います.
	 * @return RedirectResponse ログイン画面.
	 */
	public function logout(): RedirectResponse
	{
		Auth::logout();
		return redirect("/");
	}
}