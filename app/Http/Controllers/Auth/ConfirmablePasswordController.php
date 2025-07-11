<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('auth.password'),
                    'errors' => [
                        'password' => [__('auth.password')],
                    ],
                ], 422);
            }

            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Password confirmed.',
                'redirect' => route('dashboard', absolute: false),
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
