<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * エラーハンドラ.
 * @author Soma Takahashi
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * 例外をHTTP応答にレンダリングします.
     * @param  \Illuminate\Http\Request $request 要求.
     * @param  Throwable $e 例外.
     * @return \Symfony\Component\HttpFoundation\Response 応答.
     * @throws Throwable 例外発生時.
     */
    public function render($request, Throwable $e): Response
    {
        // コンテントタイプ取得.
        $contentType = $request->header("Content-Type");

        // コンテントタイプがapplication/json以外の場合は通常のレンダリングを行う.
        if ($contentType == null || $contentType === "" || explode(";", $contentType)[0] === "application/json") {
            return parent::render($request, $e);
        }

        // 認可エラー.
        if ($e instanceof AuthorizationException) {
            return response(null, 403);
        }
        // 存在しないリソースへのリクエスト.
        if ($e instanceof NotFoundHttpException) {
            return response(null, 404);
        }
        // セッションタイムアウト.
        if ($e instanceof AuthenticationException) {
            return response(null, 408);
        }

        Log::error($e);
        return response(null, 500);
    }
}
