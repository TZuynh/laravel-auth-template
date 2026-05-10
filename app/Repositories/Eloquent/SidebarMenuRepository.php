<?php

namespace App\Repositories\Eloquent;

use App\Models\SidebarMenuItem;
use App\Models\User;
use App\Repositories\Contracts\SidebarMenuRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SidebarMenuRepository implements SidebarMenuRepositoryInterface
{
    public function groupedForUser(?User $user): array
    {
        $items = $this->items($user);

        return $items
            ->groupBy('section')
            ->map(fn (Collection $sectionItems): array => [
                'section' => $sectionItems->first()['section'],
                'title' => __($sectionItems->first()['section_label_key']),
                'icon' => $sectionItems->first()['section_icon'],
                'items' => $sectionItems
                    ->map(fn (array $item): array => [
                        'label' => __($item['label_key']),
                        'route' => $item['route'],
                        'active' => $item['active_patterns'],
                        'icon' => $item['icon'],
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    private function items(?User $user): Collection
    {
        if (!Schema::hasTable('sidebar_menu_items')) {
            return $this->fallbackItems($user);
        }

        $isAdmin = $this->isAdministrator($user);

        return SidebarMenuItem::query()
            ->where('is_enabled', true)
            ->when(!$isAdmin, fn ($query) => $query->where('is_admin_only', false))
            ->orderBy('section_sort_order')
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (SidebarMenuItem $item): bool => Route::has($item->route))
            ->map(fn (SidebarMenuItem $item): array => [
                'section' => $item->section,
                'section_label_key' => $item->section_label_key,
                'section_icon' => $item->section_icon,
                'label_key' => $item->label_key,
                'route' => $item->route,
                'active_patterns' => $item->active_patterns ?: [$item->route],
                'icon' => $item->icon,
            ])
            ->values();
    }

    private function fallbackItems(?User $user): Collection
    {
        $isAdmin = $this->isAdministrator($user);

        return collect(config('sidebar.items', []))
            ->filter(fn (array $item): bool => (bool) ($item['is_enabled'] ?? true))
            ->filter(fn (array $item): bool => $isAdmin || !($item['is_admin_only'] ?? false))
            ->filter(fn (array $item): bool => Route::has($item['route']))
            ->sortBy([
                ['section_sort_order', 'asc'],
                ['sort_order', 'asc'],
            ])
            ->values();
    }

    private function isAdministrator(?User $user): bool
    {
        return in_array(strtolower(trim((string) ($user->role ?? ''))), ['administrator', 'admin'], true);
    }
}
