<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function getLogin()
    {
        return view('admin.login');
    }

    public function postLogin(Request $request)
    {
        $rules = [
            'username' => 'required|exists:users,username',
            'password' => 'required',
        ];

        $messages = [
            'username.required' => 'Username wajib diisi.',
            'username.exists'   => 'Username belum terdaftar.',
            'password.required' => 'Password wajib diisi.',
        ];

        $request->validate($rules, $messages);

        $user = User::where('username', $request->username)->first();

        if ($user->status === 'BLOKIR') {
            return response()->json([
                'message' => 'Akun tidak dapat digunakan.',
                'errors' => ['username' => ['Akun Anda telah diblokir.']],
            ], 422);
        }

        if (! Auth::attempt([
                'username' => $request->username,
                'password' => $request->password,
            ])) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'password' => ['Password salah.'],
                ],
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function master()
    {

    }

    public function pengaturan()
    {

    }

    public function pengaturanWa()
    {

    }

    public function customer()
    {

    }

    public function Marketing()
    {

    }
    public function keuangan()
    {

    }
    public function transaksi()
    {

    }

    public function logout()
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout Berhasil.');
    }
}
