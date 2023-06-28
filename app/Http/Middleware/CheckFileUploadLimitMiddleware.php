<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFileUploadLimitMiddleware
{
    /**
     * Check file limit for user
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->file) {
            $user = auth()->user();
            $limit = config('constants.UPLOAD_LIMIT');

            if ($user->upload_limit < $limit) {
                $fileSize = null;
                foreach ($request->file as $file) {
                    $fileSize += $file->getSize();
                }

                if ($fileSize > $limit - $user->upload_limit) {
                    return response()->json(['status' => 'error', 'message' => 'File upload limit exceeded'], 422);
                }
            }
        }

        return $next($request);
    }
}
