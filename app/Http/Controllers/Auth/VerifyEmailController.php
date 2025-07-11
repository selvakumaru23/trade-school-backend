<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'verified' => true,
                    'message' => 'Email already verified.',
                ]);
            }

            return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'verified' => true,
                'message' => 'Email has been successfully verified.',
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}
