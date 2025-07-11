<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View|JsonResponse
    {

        if ($request->user()->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'verified' => true,
                    'message' => 'Email is already verified.',
                ]);
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'verified' => false,
                'message' => 'Email is not verified.',
            ]);
        }

        return view('auth.verify-email');
    }
}
