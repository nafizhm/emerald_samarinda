<?php
namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\HakAkses;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = User::with('role')
                ->when(Auth::id() != 1, function ($query) {
                    $query->where('id', '!=', 1);
                })
                ->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('role', function ($row) {
                    if (! $row->role) {
                        return '<span class="badge bg-secondary">Tidak Diketahui</span>';
                    }

                    $colors = [
                        'Marketing'   => 'bg-info text-dark',
                        'Admin'       => 'bg-success',
                        'Manager'     => 'bg-warning text-dark',
                        'Super Admin' => 'bg-danger',
                    ];

                    $color = $colors[$row->role->role] ?? 'bg-secondary';

                    return '<span class="badge ' . $color . '">' . e($row->role->role) . '</span>';
                })

                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('pengguna.edit', $row->id);
                    $deleteUrl = route('pengguna.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit'] && $permissions['edit'] == 1) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus'] && $permissions['hapus'] == 1) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                        Hapus
                    </button>
                    </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['role', 'action'])
                ->make(true);
        }

        $roles = Role::select('id', 'role')->get();

        return view('admin.pengaturan.pengguna.index', compact('roles', 'permissions'));
    }

    public function edit($id)
    {
        $list = User::find($id);

        if (! $list) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data'    => $list,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'surname'      => 'required',
            'username'     => 'required|unique:users,username',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6',
            'status'       => 'required|in:AKTIF,BLOKIR',
            'id_role'      => 'required',
            'id_marketing' => 'required_if:role,1',
        ], [
            'surname.required'         => 'Nama lengkap wajib diisi.',
            'username.required'        => 'Username wajib diisi.',
            'username.unique'          => 'Username sudah digunakan.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah digunakan.',
            'password.required'        => 'Password wajib diisi.',
            'password.min'             => 'Password minimal 6 karakter.',
            'status.required'          => 'Status wajib dipilih.',
            'status.in'                => 'Status tidak valid.',
            'id_role.required'         => 'Role wajib dipilih.',
            'id_marketing.required_if' => 'Marketing wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {

            $db = [
                'surname'      => $request->surname,
                'username'     => $request->username,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'status'       => $request->status,
                'id_role'      => $request->id_role,
                'id_marketing' => $request->id_marketing ?? 0,
            ];

            $user = User::create($db);

            $menus = Menu::all();

            foreach ($menus as $menu) {
                $akses = [
                    'id_user' => $user->id,
                    'id_menu' => $menu->id,
                ];

                $akses['lihat']  = 1;
                $akses['tambah'] = 0;
                $akses['edit']   = 0;
                $akses['hapus']  = 0;

                HakAkses::create($akses);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil ditambahkan!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan Pada Server!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = User::findOrFail($id);

        $request->validate([
            'surname'      => 'required',
            'username'     => 'required|unique:users,username,' . $data->id . ',id',
            'email'        => 'required|unique:users,email,' . $data->id . ',id',
            'password'     => 'nullable|string|min:5',
            'status'       => 'required|in:AKTIF,BLOKIR',
            // 'id_role'      => 'required',
            'id_marketing' => 'required_if:role,1',

        ], [
            'surname.required'         => 'Nama lengkap wajib diisi.',
            'username.required'        => 'Username wajib diisi.',
            'username.unique'          => 'Username sudah digunakan.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah digunakan.',
            'password.min'             => 'Password minimal 5 karakter.',
            'status.required'          => 'Status wajib dipilih.',
            'status.in'                => 'Status tidak valid.',
            'id_role.required'         => 'Role wajib dipilih.',
            'id_marketing.required_if' => 'Marketing wajib dipilih.',

        ]);

        DB::beginTransaction();

        try {

            $db = [
                'surname'      => $request->surname,
                'username'     => $request->username,
                'email'        => $request->email,
                'status'       => $request->status,
                // 'id_role'      => $request->id_role,
                'id_marketing' => $request->id_marketing ?? 0,
            ];

            if ($request->filled('password')) {
                $db['password'] = Hash::make($request->password);
            }

            $data->update($db);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diubah!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Pengguna gagal diubah!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $data = User::findOrFail($id);
            HakAkses::where('id_user', $data->id)->delete();

            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => 'Gagal menghapus Pengguna!',
            ], 500);
        }
    }
}
