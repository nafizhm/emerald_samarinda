<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class HakAksesController extends Controller
{
    public static function getUserPermissions(?string $routeName = null)
    {
        $routeName ??= request()->route()?->getName();
        $userId    = Auth::id();

        $menu = Menu::where('route_name', $routeName)->first();

        if ($menu) {
            $hakAkses = HakAkses::where('id_user', $userId)
                ->where('id_menu', $menu->id)
                ->first();

            return $hakAkses ? [
                'tambah' => $hakAkses->tambah,
                'edit'   => $hakAkses->edit,
                'hapus'  => $hakAkses->hapus,
            ] : [
                'tambah' => 0,
                'edit'   => 0,
                'hapus'  => 0,
            ];
        }

        return [
            'tambah' => 0,
            'edit'   => 0,
            'hapus'  => 0,
        ];
    }

    public function index(Request $request)
    {
        $permissions = $this->getUserPermissions();

        $users = User::select('id', 'username')
            ->when(Auth::user()->username != 'dev', function ($query) {
                $query->where('username', '!=', 'dev');
            })
            ->get();

        return view('admin.pengaturan.hak_akses.index', compact('users', 'permissions'));
    }

    private function generateHakAkses($id)
    {
        $user = User::find($id);

        if (! $user) {
            return;
        }

        $menus        = Menu::all();
        $existingMenu = HakAkses::where('id_user', $user->id)->pluck('id_menu')->toArray();
        $missingMenus = $menus->whereNotIn('id', $existingMenu);

        if ($missingMenus->count() > 0) {
            $addedMenus = [];

            foreach ($missingMenus as $menu) {
                $akses = [
                    'id_user' => $user->id,
                    'id_menu' => $menu->id,
                    'lihat'   => 1,
                    'tambah'  => 0,
                    'edit'    => 0,
                    'hapus'   => 0,
                ];

                HakAkses::create($akses);
                $addedMenus[] = $menu->title ?? $menu->id;
            }
        }
    }

    public function getHakAkses(Request $request)
    {
        abort_unless(HakAkses::where('id_user', Auth::id())->where('lihat', 1)
            ->whereHas('menu', fn ($query) => $query->where('route_name', 'hak-akses.index'))->exists(), 403);
        if (! $request->has('id_user') || empty($request->id_user)) {
            return response()->json(['data' => []]);
        }

        $this->generateHakAkses($request->id_user);

        $permissions = self::getUserPermissions('hak-akses.index');

        $hakAkses = HakAkses::with('menu')
            ->where('id_user', $request->id_user)
            ->whereHas('menu', fn ($query) => $query->where('route_name', '!=', 'beranda'))
            ->get();

        $sorted = collect();

        $induk = $hakAkses->filter(fn($row) => $row->menu && $row->menu->id_parent == 0)
            ->sortBy(fn($row) => $row->menu->urutan ?? 0);

        foreach ($induk as $indukItem) {
            $sorted->push($indukItem);

            $anak = $hakAkses->filter(fn($row) => $row->menu && $row->menu->id_parent == $indukItem->id_menu)
                ->sortBy(fn($row) => $row->menu->urutan ?? 0);

            foreach ($anak as $anakItem) {
                $sorted->push($anakItem);
            }
        }

        return DataTables::of($sorted)
            ->addIndexColumn()
            ->addColumn('induk_menu', function ($row) {
                if (! $row->menu) {
                    return '-';
                }

                if ($row->menu->id_parent == 0) {
                    return 'Induk';
                }

                return Menu::find($row->menu->id_parent)?->title ?? 'Induk';
            })
            ->addColumn('title', fn($row) => $row->menu->title ?? '-')
            ->addColumn('route_name', fn($row) => $row->menu->route_name ?? '-')
            ->addColumn('lihat', function ($row) use ($permissions) {
                if (! $row->menu || $row->menu->lihat == 0 || $row->menu->route_name === '#') {
                    return '';
                }

                $checked  = $row->lihat == 1 ? 'checked' : '';
                $disabled = ($permissions['edit'] ?? 1) == 0 ? 'disabled' : '';
                return "<div class='text-center'><input type='checkbox' class='form-check-input' name='lihat[{$row->id}]' $checked $disabled></div>";
            })
            ->addColumn('beranda', function ($row) use ($permissions) {
                if (! $row->menu || $row->menu->title === "Beranda" || $row->menu->route_name === "#") {
                    return '';
                }

                $checked  = $row->beranda == 1 ? 'checked' : '';
                $disabled = ($permissions['edit'] ?? 1) == 0 ? 'disabled' : '';
                return "<div class='text-center'><input type='checkbox' class='form-check-input' name='beranda[{$row->id}]' $checked $disabled></div>";
            })
            ->addColumn('tambah', function ($row) use ($permissions) {
                if (! $row->menu || $row->menu->tambah == 0) {
                    return '';
                }

                $checked  = $row->tambah == 1 ? 'checked' : '';
                $disabled = ($permissions['edit'] ?? 1) == 0 ? 'disabled' : '';
                return "<div class='text-center'><input type='checkbox' class='form-check-input' name='tambah[{$row->id}]' $checked $disabled></div>";
            })
            ->addColumn('edit', function ($row) use ($permissions) {
                if (! $row->menu || $row->menu->edit == 0) {
                    return '';
                }

                $checked  = $row->edit == 1 ? 'checked' : '';
                $disabled = ($permissions['edit'] ?? 1) == 0 ? 'disabled' : '';
                return "<div class='text-center'><input type='checkbox' class='form-check-input' name='edit[{$row->id}]' $checked $disabled></div>";
            })
            ->addColumn('hapus', function ($row) use ($permissions) {
                if (! $row->menu || $row->menu->hapus == 0) {
                    return '';
                }

                $checked  = $row->hapus == 1 ? 'checked' : '';
                $disabled = ($permissions['edit'] ?? 1) == 0 ? 'disabled' : '';
                return "<div class='text-center'><input type='checkbox' class='form-check-input' name='hapus[{$row->id}]' $checked $disabled></div>";
            })
            ->rawColumns(['induk_menu', 'beranda', 'title', 'route_name', 'lihat', 'tambah', 'edit', 'hapus'])
            ->make(true);
    }

    public function updateHakAkses(Request $request)
    {
        abort_unless(self::getUserPermissions('hak-akses.index')['edit'], 403);
        $request->validate([
            'id_user' => 'required|integer|exists:users,id',
            'hak_akses_data' => 'required|array',
            'hak_akses_data.*' => 'array',
            'hak_akses_data.*.*' => 'boolean',
        ]);
        $hakAksesData = $request->hak_akses_data;

        $allIds = collect($hakAksesData)->map(function ($item) {
            return array_keys($item);
        })->flatten()->unique();

        foreach ($allIds as $id) {
            $hakAkses = HakAkses::with('menu')->where('id', $id)->where('id_user', $request->id_user)->first();
            if ($hakAkses) {
                foreach (['lihat', 'beranda', 'tambah', 'edit', 'hapus'] as $field) {
                    if (array_key_exists($id, $hakAksesData[$field] ?? [])) {
                        $allowed = $field === 'beranda' ? $hakAkses->menu?->route_name !== '#' : (bool) $hakAkses->menu?->{$field};
                        $hakAkses->{$field} = $allowed ? (int) $hakAksesData[$field][$id] : 0;
                    }
                }
                $hakAkses->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Hak Akses telah diperbarui.',
        ]);
    }
}
