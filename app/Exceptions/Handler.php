<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Illuminate\Session\TokenMismatchException; // TokenMismatchException をインポート　419

class Handler extends ExceptionHandler
{

    /**
     * 既存の render メソッドをオーバーライドします。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // 404エラーの場合、/homeにリダイレクト
        if ($e instanceof NotFoundHttpException) {
            return redirect('/home');
        }

        // 419エラー（TokenMismatchException）の場合、/homeにリダイレクト
        if ($e instanceof TokenMismatchException) {
            return redirect('/home');
        }

        // それ以外の例外は通常通り処理
        return parent::render($request, $e);
    }


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


}
