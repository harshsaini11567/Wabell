<?php
namespace App\Http\Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use Closure;
use App\Http\Controllers\APIController;

class TokenVersion extends APIController
{
    public function handle($request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $payload = JWTAuth::getPayload();
        $tokenVersion = $payload->get('token_version');

        if ($user->token_version !== $tokenVersion) {
            return $this->apiError(trans('messages.access_token_expired'));
        }

        // if ($user->is_ban) {
        //     JWTAuth::invalidate(JWTAuth::getToken());

        //     return $this->apiError(trans('messages.account_ban'), 401);
        // }

        return $next($request);
    }
}

?>