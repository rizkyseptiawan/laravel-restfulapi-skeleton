<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $createdToken = $user->createToken('auth_token');

        $token = $createdToken->accessToken;

        $responseData = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->generateApiResponse(data: $responseData);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->generateApiResponse(message: 'Logged out successfully.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->accessToken;

        $responseData = [
            'user' => $user,
            'token' => $token
        ];

        return $this->generateApiResponse(data: $responseData, statusCode: 201);
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        $user = User::findOrFail($id);

        if($user->email_verified_at) {
            return $this->generateApiResponse('Email sudah diverifikasi.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->generateApiResponse('Email tidak valid.', statusCode: 404);
        }

        $expires = $request->query('expires');
        $expiresTimestamp = $expires ? strtotime($expires) : null;

        if ($expiresTimestamp && $expiresTimestamp < time()) {
            return $this->generateApiResponse('Link verifikasi sudah kadaluarsa.', statusCode: 404);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        return $this->generateApiResponse('Email berhasil diverifikasi. Silahkan kembali ke aplikasi untuk login.');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->generateApiResponse('User tidak ditemukan.', statusCode: 404);
        }

        if($user->email_verified_at) {
            return $this->generateApiResponse('Email sudah diverifikasi.');
        }
        $user->sendEmailVerificationNotification();
        return $this->generateApiResponse('Email verifikasi berhasil dikirim ke email anda.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $resetToken = Str::random(60);
        $user->sendPasswordResetNotification($resetToken);

        return $this->generateApiResponse('Link password berhasil dikirim ke email anda.');
    }

    public function resetPassword(Request $request, string $hash)
    {

    }
}
