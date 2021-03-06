<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Settings;
class AuthMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $username = Settings::where('serviceName', 'appUsername')->first();
        $password = Settings::where('serviceName', 'appPassword')->first();
        $condition = true;
        $user_id = null;
        $authHeader = $request->header('Authorization');
        if (!empty($authHeader)) {
            $authCode = explode(' ', $authHeader);
            if ($authCode[0] == 'Basic') {
                $auth = explode(':', base64_decode($authCode[1]));
                if ($auth[0] != $username->value || $auth[1] != $password->value) {
                    $condition = false;
                }
                $user_id = $auth[2];
            } else {
                $condition = false;
            }
        } else {
            $condition = false;
        }
        if ($condition) {
            return $next($request->merge(['user_id' => $user_id]));
        } else {
            return response()->json(['error' => 'Не авторизован'], 401);
        }
    }
}
