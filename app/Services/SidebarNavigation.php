<?php

namespace App\Services;

use App\Models\HakAkses;
use App\Models\Menu;
use Illuminate\Support\Facades\Route;

class SidebarNavigation
{
    public function forUser(int $userId, ?string $currentRoute): array
    {
        $allowed = HakAkses::where('id_user', $userId)->where('lihat', 1)->pluck('id_menu');
        $menus = Menu::whereIn('id', $allowed)->get()->keyBy('route_name');
        $items = [];
        foreach (config('navigation') as $definition) {
            $item = $definition;
            $item['children'] = [];
            if (isset($definition['children'])) {
                foreach ($definition['children'] as $route => $title) {
                    if ($route === 'pengaturan-profile.index' && !$menus->has($route) && $menus->has('pengaturan-media.index')) {
                        $route = 'pengaturan-media.index';
                    }
                    if ($menus->has($route) && Route::has($route)) {
                        $item['children'][] = ['route' => $route, 'title' => $title, 'active' => $this->isActive($route, $currentRoute)];
                    }
                }
                if (!$item['children']) {
                    continue;
                }
                $item['active'] = in_array(true, array_column($item['children'], 'active'), true);
            } else {
                if (!$menus->has($item['route']) || !Route::has($item['route'])) {
                    continue;
                }
                $item['active'] = $this->isActive($item['route'], $currentRoute);
            }
            $items[] = $item;
        }
        return $items;
    }

    private function isActive(string $route, ?string $current): bool
    {
        if (!$current) {
            return false;
        }
        $aliases = [
            'st-penjualan.index' => ['st-penjualan.', 'denah-penjualan.'],
            'pengaturan-profile.index' => ['pengaturan-profile.', 'pengaturan-media.'],
            'pengaturan-media.index' => ['pengaturan-profile.', 'pengaturan-media.'],
            'hak-akses.index' => ['hak-akses.', 'admin.getHakAkses', 'admin.updateHakAkses'],
        ];
        if (isset($aliases[$route])) {
            foreach ($aliases[$route] as $prefix) {
                if (str_starts_with($current, $prefix)) {
                    return true;
                }
            }
            return false;
        }
        if (!str_contains($route, '.')) {
            return $current === $route;
        }
        return str_starts_with($current, strstr($route, '.', true).'.');
    }
}
