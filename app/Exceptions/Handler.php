<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        // not login
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                $res = [
                    'code' => 401,
                    'errors' => 'Unauthorized' . ' : ' . $e->getMessage(),
                    'message' => "Không được phép truy cập"
                ];
                return response()->json($res, 401);
            }
        });

        // save(), delete()
        $this->renderable(function (QueryException $e, $request) {
            if ($request->is('api/*')) {
                $res = [
                    'code' => 400,
                    'errors' => 'Truy xuất dữ liệu thất bại' . ' : ' . $e->getMessage(),
                    'message' => "Thất bại"
                ];
                return response()->json($res, 400);
            }
        });

        // findOrFail() firstOrFail()
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                $res = [
                    'code' => 404,
                    'errors' => 'Tài nguyên không tồn tại' . ' : ' . $e->getMessage(),
                    'message' => "Thất bại"
                ];
                return response()->json($res, 404);
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
