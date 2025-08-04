<?php

namespace App\Http\Controllers;

use App\Models\Core\User;
use App\Services\Core\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public $data;
    public $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
        $this->data = [];
    }

    public function registerSocial(Request $request)
    {
        $provider = $request->input('provider');
        $isSocial = in_array($provider, ['google', 'facebook', 'github', 'twitter']);

        try {
            $fields = $request->validate([
                'username'     => 'required|max:255',
                'first_name'   => 'required|max:255',
                'last_name'    => 'nullable|max:255',
                'email'        => 'required|email|unique:sx_users,email',
                'password'     => 'required|min:6|confirmed',
                'password_confirmation' => 'required|same:password'
            ]);

            $user = User::create([
                'username'     => $fields['username'],
                'email'        => $fields['email'],
                'first_name'   => $fields['first_name'],
                'last_name'    => $fields['last_name'],
                'group_id'     => '3',
                'active'       => $isSocial ? '1' : '0',
                'password'     => Hash::make($fields['password']),
            ]);

            $token = $user->createToken($request->username);

            return response()->json([
                'status' => 1,
                'message' => 'Register success (via ' . ucfirst($provider) . ')',
                'data' => [
                    'profile' => $user,
                    'token'   => $token->plainTextToken,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Register failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $fields = $request->validate([
                'username'     => 'required|max:255',
                'first_name'   => 'required|max:255',
                'last_name'    => 'nullable|max:255',
                'email'        => 'required|email|unique:sx_users,email',
                'password'     => 'required|min:6|confirmed',
                'password_confirmation' => 'required|same:password'
            ]);

            $user = User::create([
                'username'     => $fields['username'],
                'email'        => $fields['email'],
                'first_name'   => $fields['first_name'],
                'last_name'    => $fields['last_name'],
                'group_id'     => '3',
                'active'       => '0',
                'password'     => Hash::make($fields['password']),
            ]);

            if (config('sximo.cnf_activation') == 'manual') {
                $user->active = '0';
                $user->save();

                return response()->json([
                    'status' => 1,
                    'message' => 'Register success, please wait for admin approval',
                ], 201);
            } else if (config('sximo.cnf_activation') == 'activation') {
                $user->active = '0';
                $user->save();

                $to = $user->email;

                if (config('sximo.cnf_mail') == 'php') {

                    // $subject = "Account Activation SXIMO v7";

                    // $message = view('emails.ActivationEmail', ['user' => $user])->render();

                    // $headers = "MIME-Version: 1.0" . "\r\n";
                    // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    // $headers .= "From: no-reply@yourdomain.com" . "\r\n";
                    // $headers .= "Reply-To: no-reply@yourdomain.com" . "\r\n";

                    // mail($to, $subject, $message, $headers);
                } elseif (config('sximo.cnf_mail') == 'phpmailer') {
                    $content = [
                        'subject' => 'Your OTP Code for Password Reset',
                        'body' => view('emails.ActivationEmail', ['user' => $user])->render()
                    ];
                    $this->mailService->sendMail($user, $content);
                }

                return response()->json([
                    'status' => 1,
                    'message' => 'Register success, please check your email to activate your account',
                ], 201);
            } else if (config('sximo.cnf_activation') == "auto") {
                $token = $user->createToken($request->username);

                $this->data['profile'] = $user;
                $this->data['token'] = $token->plainTextToken;

                return response()->json([
                    'status' => 1,
                    'message' => 'Register success',
                    'data' => $this->data
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Register failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {

        $request->validate([
            'email'         => 'required|email',
            'password'      => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password,  $user->password)) {

            return response()->json([
                'message'   => 'Invalid Username Or Password',
                'status'    => 0
            ], 401);
        }

        if ($user->active == 0) {
            return response()->json([
                'message'   => 'Your account is not active',
                'status'    => 0
            ], 200);
        } else if ($user->active == 2) {
            return response()->json([
                'message'   => 'You are account is blocked',
                'status'    => 2
            ], 200);
        }

        $token = $user->createToken($user->username);
        $user->username = $user->username;

        $this->data['profile'] = $user;
        $this->data['token'] = $token->plainTextToken;

        return response()->json([
            'status' => 1,
            'message' => 'Login success',
            'data' => $this->data
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $checkEmail = User::where('email', $request->email)->first();
        if (!$checkEmail) {
            return response()->json([
                'status' => 0,
                'message' => 'Check your email, email not found',
            ], 404);
        }

        try {
            $request->validate([
                'email' => 'required',
            ]);

            $otp = rand(1000, 9999);
            $email = $request->email;
            $token = Str::random(64);

            DB::table('sx_users')->where('email', $email)->update(
                [
                    'otp' => $otp,
                    'otp_token' => $token,
                    'created_at' => now()
                ]
            );

            $user = User::where('email', $email)->first();

            $content = [
                'otp' => $otp,
                'subject' => 'Your OTP Code for Password Reset',
                'body' => view('emails.otpPassword', ['user' => $user, 'otp' => $otp])->render(),
            ];

            $this->mailService->sendMail($user, $content);

            return response()->json([
                'status' => 1,
                'message' => 'OTP sent to your email, please check your inbox or spam folder',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:4',
            ]);

            $otpData = DB::table('sx_users')
                ->where('email', $request->email)
                ->where('otp', $request->otp)
                ->first();

            if (!$otpData) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Please check your OTP, OTP not found'
                ], 404);
            }

            if (now()->diffInMinutes($otpData->updated_at) > 60) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Check your OTP'
                ], 400);
            }

            return response()->json([
                'status' => 1,
                'message' => 'OTP verified',
                'otp_token' => $otpData->otp_token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp_token' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            $otpData = DB::table('sx_users')
                ->where('email', $request->email)
                ->where('otp_token', $request->otp_token)
                ->first();

            if (!$otpData) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid token'
                ], 404);
            }

            if (now()->diffInMinutes($otpData->created_at) > 60) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Token expired'
                ], 400);
            }

            User::where('email', $request->email)->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('sx_users')->where('email', $request->email)->update([
                'otp' => null,
                'otp_token' => null
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'Password has been reset'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $email = $socialUser->getEmail();

        $user = User::where('email', $email)->first();

        if ($user) {
            $token = $user->createToken($user->username)->plainTextToken;

            return redirect(env('FRONTEND_URL') . "/auth/{$provider}?token={$token}&profile=" . urlencode(json_encode($user)));
        } else {
            return redirect(env('FRONTEND_URL') . "/auth/{$provider}?register=true&profile=" . urlencode(json_encode([
                'username' => $socialUser->getName() ?: ($socialUser->getNickname() ?: explode('@', $socialUser->getEmail())[0]),
                'email' => $socialUser->getEmail(),
                'first_name' => $socialUser->getName(),
                'last_name' => '',
                'avatar' => $socialUser->getAvatar(),
                'group_id' => '3',
                'active' => '1',
                'email_verified_at' => now(),
                'password' => 'password',
                'password_confirmation' => 'password',
            ])));
        }
    }
}
