<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        // 1. Verify Google reCAPTCHA Token
        $captchaToken = $request->json('captcha_token') ?? $request->input('captcha_token');

        $secretKey = config('services.recaptcha.secret_key')
            ?: env('RECAPTCHA_SECRET_KEY')
            ?: getenv('RECAPTCHA_SECRET_KEY');

        $secretKey = trim(trim((string) $secretKey), "\"' \t\n\r\0\x0B");

        $recaptchaEnabled = app()->environment('testing')
            ? $request->has('captcha_token')
            : (filter_var(env('RECAPTCHA_ENABLED', true), FILTER_VALIDATE_BOOLEAN) && !empty($secretKey));

        if ($recaptchaEnabled) {
            if (empty($captchaToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi Captcha gagal, token reCAPTCHA kosong.'
                ], 422);
            }

            try {
                $postData = [
                    'secret'   => $secretKey,
                    'response' => trim(trim((string) $captchaToken), "\"' \t\n\r\0\x0B"),
                ];

                $clientIp = $request->ip();
                // Only send remoteip if it is a valid public IP (avoid sending internal Docker bridge IPs)
                if ($clientIp && filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    $postData['remoteip'] = $clientIp;
                }

                $response = Http::timeout(5)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', $postData);

                Log::info('ReCAPTCHA Response: ' . $response->body());

                if (!$response->json('success')) {
                    // Toleransi khusus untuk tahap development lokal
                    if (config('app.env') === 'local') {
                        Log::warning('reCAPTCHA validation failed but bypassed because APP_ENV is local.');
                    } else {
                        $errorCodes = (array) ($response->json('error-codes') ?? []);
                        $reasons = [];

                        foreach ($errorCodes as $code) {
                            switch ($code) {
                                case 'invalid-input-secret':
                                    $reasons[] = 'Secret key reCAPTCHA tidak valid. Pastikan RECAPTCHA_SECRET_KEY di .env.backend benar dan sesuai dengan Site Key.';
                                    break;
                                case 'invalid-input-response':
                                    $reasons[] = 'Token reCAPTCHA tidak valid. Pastikan Site Key di frontend dan Secret Key di backend berasal dari pasangan kunci yang sama di Google Console, serta bertipe reCAPTCHA v2 Checkbox ("I am not a robot").';
                                    break;
                                case 'hostname-mismatch':
                                    $reasons[] = 'Domain web (' . $request->getHost() . ') belum didaftarkan di Google reCAPTCHA Admin Console untuk Site Key tersebut.';
                                    break;
                                case 'timeout-or-duplicate':
                                    $reasons[] = 'Token reCAPTCHA sudah kadaluarsa atau sudah digunakan. Silakan centang ulang kotak reCAPTCHA.';
                                    break;
                                default:
                                    $reasons[] = "reCAPTCHA error: {$code}.";
                                    break;
                            }
                        }

                        $message = !empty($reasons)
                            ? implode(' ', $reasons)
                            : "Validasi Captcha gagal, silakan centang ulang kotak I'm not a robot.";

                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'error_codes' => $errorCodes,
                        ], 422);
                    }
                }
            } catch (\Exception $e) {
                Log::error('reCAPTCHA connection failed: ' . $e->getMessage());
                if (config('app.env') !== 'local' && !app()->environment('testing')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koneksi ke layanan Google reCAPTCHA gagal, silakan coba lagi nanti.'
                    ], 500);
                }
            }
        }

        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Construct unique throttle key based on username and client IP
        $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();

        // Check if user has exceeded max login attempts (3)
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Akun Anda dibekukan sementara selama {$minutes} menit."
            ], 429);
        }

        // Fallback: Create default admin user if it does not exist in the database
        if ($credentials['username'] === 'admin' && !User::where('username', 'admin')->exists()) {
            User::create([
                'name' => 'Admin SIPPOL',
                'username' => 'admin',
                'email' => 'admin@sippol.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin'),
                'role' => 'super_admin',
            ]);
        }

        if (Auth::attempt($credentials)) {
            // Clear attempts on successful login
            RateLimiter::clear($throttleKey);

            /** @var User $user */
            $user = Auth::user();
            // Create Sanctum token
            $token = $user->createToken('admin-token')->plainTextToken;

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'description' => 'Admin ' . $user->name . ' berhasil masuk ke sistem.',
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                ]
            ], 200);
        }

        // Record a failed attempt, locked out for 5 minutes (300 seconds)
        RateLimiter::hit($throttleKey, 300);

        return response()->json([
            'success' => false,
            'message' => 'Username atau Password yang Anda masukkan salah.'
        ], 401);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        if ($user) {
            // Revoke current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak terotentikasi.'
        ], 401);
    }

    /**
     * Handle admin registration / creation of new users.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.mixed' => 'Password harus mengandung kombinasi huruf besar dan huruf kecil.',
            'password.numbers' => 'Password harus mengandung setidaknya satu angka.',
            'password.symbols' => 'Password harus mengandung setidaknya satu simbol atau karakter khusus.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => 'user', // Security: public registration cannot elevate itself to admin
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna baru berhasil didaftarkan.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * Store a newly created admin user in database (Super Admin endpoint).
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|string|in:admin,super_admin,bidang_trantib,bidang_linmas,bidang_perada,bidang_sda',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.mixed' => 'Password harus mengandung kombinasi huruf besar dan huruf kecil.',
            'password.numbers' => 'Password harus mengandung setidaknya satu angka.',
            'password.symbols' => 'Password harus mengandung setidaknya satu simbol atau karakter khusus.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role ?? 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);



        return response()->json([
            'success' => true,
            'message' => 'Pengguna baru berhasil didaftarkan.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * Handle user password change request.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'new_password.min' => 'Password baru minimal harus 8 karakter.',
            'new_password.mixed' => 'Password baru harus mengandung kombinasi huruf besar dan huruf kecil.',
            'new_password.numbers' => 'Password baru harus mengandung setidaknya satu angka.',
            'new_password.symbols' => 'Password baru harus mengandung setidaknya satu simbol atau karakter khusus.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama yang Anda masukkan salah.'
            ], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password Anda berhasil diperbarui.'
        ], 200);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ], 200);
    }

    /**
     * List all registered users.
     */
    public function users(Request $request)
    {
        $userRole = auth()->user()->role;
        if ($userRole !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ], 403);
        }

        $users = User::orderBy('name', 'asc')->get(['id', 'name', 'username', 'email', 'role', 'created_at']);
        return response()->json([
            'success' => true,
            'users' => $users
        ], 200);
    }

    /**
     * Delete an admin user from database (Super Admin endpoint).
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        if (auth()->id() == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.'
            ], 400);
        }

        $username = $user->username;



        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.'
        ], 200);
    }

    /**
     * Change the role of a user (Super Admin only).
     */
    public function changeRole(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Super Admin yang memiliki hak akses ini.'
            ], 403);
        }

        $request->validate([
            'role' => 'required|string|in:admin,super_admin,bidang_trantib,bidang_linmas,bidang_perada,bidang_sda',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        $user->update(['role' => $request->role]);



        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengubah hak akses user menjadi ' . $request->role
        ], 200);
    }
}
