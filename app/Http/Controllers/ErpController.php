<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErpController extends Controller
{
    public function timekeeping(): \Illuminate\Contracts\View\View
    {
        return view('erp.timekeeping', [
            'departments' => $this->departments(),
            'attendanceRows' => $this->attendanceRows(),
        ]);
    }

    public function leaves(): \Illuminate\Contracts\View\View
    {
        return view('erp.proposals', [
            'pageTitle' => __('messages.erp.ui.proposals_title'),
            'activeGroup' => 'workspace',
            'proposalTypes' => $this->proposalTypes(),
            'myProposals' => $this->proposalRows(),
            'approvalQueue' => $this->approvalRows(),
        ]);
    }

    public function approvals(): \Illuminate\Contracts\View\View
    {
        return view('erp.proposals', [
            'pageTitle' => __('messages.erp.ui.approval_page_title'),
            'activeGroup' => 'approval',
            'proposalTypes' => $this->proposalTypes(),
            'myProposals' => $this->proposalRows(),
            'approvalQueue' => $this->approvalRows(),
        ]);
    }

    public function myKpi(): \Illuminate\Contracts\View\View
    {
        return view('erp.my-kpi', [
            'kpiRows' => [
                ['target' => 'Complete 100% of assigned leads', 'weight' => 35, 'evidence' => 'Automated CRM report', 'self_score' => 90],
                ['target' => 'Keep customer response time under 2h', 'weight' => 25, 'evidence' => 'Support ticket log', 'self_score' => 85],
                ['target' => 'Contribute to internal process improvements', 'weight' => 15, 'evidence' => 'Improvement notes', 'self_score' => 80],
            ],
        ]);
    }

    public function evaluateKpi(): \Illuminate\Contracts\View\View
    {
        return view('erp.kpi-evaluate', [
            'departments' => $this->departments(),
            'kpiEmployees' => [
                ['name' => 'Nguyen Hoang Phi', 'department' => __('messages.erp.ui.executive_board'), 'self' => 28, 'level_one' => 38, 'final' => 91, 'status' => __('messages.erp.ui.waiting_manager')],
                ['name' => 'Tran Minh Anh', 'department' => __('messages.erp.ui.sales'), 'self' => 26, 'level_one' => 35, 'final' => 86, 'status' => __('messages.erp.ui.needs_action')],
                ['name' => 'Le Quoc Bao', 'department' => __('messages.erp.ui.production'), 'self' => 30, 'level_one' => 39, 'final' => 94, 'status' => __('messages.erp.ui.closed')],
            ],
        ]);
    }

    public function employees(Request $request): \Illuminate\Contracts\View\View
    {
        $q = trim((string) $request->query('q', ''));
        $employees = collect($this->employeeRows());

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $employees = $employees->filter(function (array $employee) use ($needle): bool {
                return str_contains(mb_strtolower($employee['name']), $needle)
                    || str_contains(mb_strtolower($employee['phone']), $needle);
            });
        }

        return view('erp.employees', [
            'q' => $q,
            'departments' => $this->departments(),
            'employees' => $employees->values(),
        ]);
    }

    public function payroll(): \Illuminate\Contracts\View\View
    {
        return view('erp.payroll', [
            'departments' => $this->departments(),
            'payrollRows' => $this->payrollRows(),
        ]);
    }

    public function recruitment(): \Illuminate\Contracts\View\View
    {
        return view('erp.recruitment', [
            'atsColumns' => [
                ['key' => 'new', 'title' => 'New CV', 'tone' => 'blue'],
                ['key' => 'interview', 'title' => 'Interview', 'tone' => 'amber'],
                ['key' => 'test', 'title' => 'Skill test', 'tone' => 'purple'],
                ['key' => 'offer', 'title' => 'Offer sent', 'tone' => 'rose'],
                ['key' => 'hired', 'title' => 'Hired', 'tone' => 'green'],
            ],
            'candidates' => [
                ['name' => 'Pham Gia Han', 'position' => 'Sale Admin', 'phone' => '0908 112 233', 'stage' => 'new', 'score' => 78],
                ['name' => 'Do Minh Quan', 'position' => 'CNC technician', 'phone' => '0917 555 881', 'stage' => 'interview', 'score' => 84],
                ['name' => 'Huynh Bao Tram', 'position' => 'General accountant', 'phone' => '0933 204 886', 'stage' => 'offer', 'score' => 91],
            ],
        ]);
    }

    public function contracts(Request $request): \Illuminate\Contracts\View\View
    {
        $stages = [
            __('messages.erp.ui.pending_survey'),
            __('messages.erp.ui.confirmed'),
            __('messages.erp.ui.producing'),
            __('messages.erp.ui.installing'),
            __('messages.erp.ui.completed'),
        ];

        return view('erp.contracts', [
            'viewMode' => $request->query('view', 'table'),
            'contracts' => [
                ['code' => 'HD-2026-001', 'customer' => $this->locale() === 'en' ? 'Nam Phuong Furniture' : 'Noi That Nam Phuong', 'phone' => '0901 234 567', 'total' => 185000000, 'deposit' => 55000000, 'debt' => 130000000, 'status' => $stages[0]],
                ['code' => 'HD-2026-002', 'customer' => $this->locale() === 'en' ? 'The Metropole Apartment' : 'Can ho The Metropole', 'phone' => '0912 444 888', 'total' => 94000000, 'deposit' => 47000000, 'debt' => 47000000, 'status' => $stages[2]],
                ['code' => 'HD-2026-003', 'customer' => 'Showroom Q7', 'phone' => '0988 222 111', 'total' => 260000000, 'deposit' => 260000000, 'debt' => 0, 'status' => $stages[4]],
            ],
            'contractStages' => $stages,
        ]);
    }

    public function procurementAlerts(): \Illuminate\Contracts\View\View
    {
        return view('erp.procurement-alerts', [
            'alerts' => $this->procurementRows(),
        ]);
    }

    public function categories(): \Illuminate\Contracts\View\View
    {
        return view('erp.categories', [
            'categories' => $this->categoryRows(),
        ]);
    }

    public function inventory(ProductRepositoryInterface $productRepository): \Illuminate\Contracts\View\View
    {
        return view('erp.inventory', [
            'inventoryRows' => $this->inventoryRows($productRepository),
        ]);
    }

    public function purchaseOrders(): \Illuminate\Contracts\View\View
    {
        return view('erp.purchase-orders', [
            'purchaseOrders' => $this->purchaseOrderRows(),
        ]);
    }

    public function stockReport(ProductRepositoryInterface $productRepository): \Illuminate\Contracts\View\View
    {
        return view('erp.stock-report', [
            'reportRows' => $this->stockReportRows($productRepository),
        ]);
    }

    public function analytics(): \Illuminate\Contracts\View\View
    {
        return view('erp.analytics', [
            'metrics' => $this->liveMetrics(),
            'securityAlerts' => $this->securityAlerts(),
            'events' => $this->activityEvents(),
        ]);
    }

    public function analyticsLive(): JsonResponse
    {
        return response()->json([
            'metrics' => $this->liveMetrics(),
            'securityAlerts' => $this->securityAlerts(),
            'events' => $this->activityEvents(),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    private function locale(): string
    {
        return in_array(app()->getLocale(), ['vi', 'en'], true) ? app()->getLocale() : 'vi';
    }

    private function departments(): array
    {
        return [
            __('messages.erp.ui.all_departments'),
            __('messages.erp.ui.executive_board'),
            __('messages.erp.ui.sales'),
            __('messages.erp.ui.production'),
            __('messages.erp.ui.accounting'),
            __('messages.erp.ui.hr'),
            __('messages.erp.ui.warehouse_ops'),
        ];
    }

    private function proposalTypes(): array
    {
        return [
            __('messages.erp.ui.annual_leave_paid'),
            __('messages.erp.ui.unpaid_leave'),
            __('messages.erp.ui.business_trip'),
            __('messages.erp.ui.overtime'),
            __('messages.erp.ui.material_purchase'),
            __('messages.erp.ui.advance_refund'),
        ];
    }

    private function proposalRows(): array
    {
        return [
            ['code' => 'DX-2605-001', 'type' => __('messages.erp.ui.annual_leave'), 'date_range' => '08/05/2026 - 09/05/2026', 'status' => __('messages.erp.ui.waiting_manager'), 'progress' => 45],
            ['code' => 'DX-2605-002', 'type' => __('messages.erp.ui.material_purchase'), 'date_range' => '06/05/2026', 'status' => __('messages.erp.ui.approved'), 'progress' => 100],
        ];
    }

    private function approvalRows(): array
    {
        return [
            ['employee' => 'Tran Minh Anh', 'type' => __('messages.erp.ui.annual_leave'), 'date_range' => '10/05/2026 - 11/05/2026', 'status' => __('messages.erp.ui.needs_action')],
            ['employee' => 'Le Quoc Bao', 'type' => __('messages.erp.ui.overtime'), 'date_range' => '07/05/2026', 'status' => __('messages.erp.ui.needs_action')],
        ];
    }

    private function attendanceRows(): array
    {
        $users = User::query()->select(['id', 'name', 'role', 'created_at'])->orderBy('id')->limit(12)->get();
        $locations = [
            __('messages.erp.ui.office_hcm'),
            __('messages.erp.ui.branch_hanoi'),
            __('messages.erp.ui.workshop_cnc'),
            __('messages.erp.ui.warehouse_ops'),
        ];

        if ($users->isNotEmpty()) {
            return $users->map(function (User $user, int $index) use ($locations): array {
                $late = $index % 4 === 1;
                $working = $index % 5 === 2;

                return [
                    'name' => $user->name,
                    'department' => $index % 3 === 0 ? __('messages.erp.ui.executive_board') : ($index % 3 === 1 ? __('messages.erp.ui.sales') : __('messages.erp.ui.production')),
                    'shift' => $index % 2 === 0 ? __('messages.erp.ui.main_shift') : __('messages.erp.ui.morning_shift'),
                    'check_in' => $late ? '08:12' : '08:01',
                    'check_out' => $working ? '-' : '17:' . str_pad((string) (2 + $index * 3), 2, '0', STR_PAD_LEFT),
                    'location' => $locations[$index % count($locations)],
                    'status' => $working ? __('messages.erp.ui.currently_working') : ($late ? __('messages.erp.ui.late_minutes', ['minutes' => 12]) : __('messages.erp.ui.on_time')),
                    'tone' => $working ? 'blue' : ($late ? 'amber' : 'emerald'),
                ];
            })->all();
        }

        return [
            ['name' => 'Nguyen Hoang Phi', 'department' => __('messages.erp.ui.executive_board'), 'shift' => __('messages.erp.ui.main_shift'), 'check_in' => '08:01', 'check_out' => '17:35', 'location' => __('messages.erp.ui.office_hcm'), 'status' => __('messages.erp.ui.on_time'), 'tone' => 'emerald'],
            ['name' => 'Tran Minh Anh', 'department' => __('messages.erp.ui.sales'), 'shift' => __('messages.erp.ui.morning_shift'), 'check_in' => '08:12', 'check_out' => '17:02', 'location' => __('messages.erp.ui.branch_hanoi'), 'status' => __('messages.erp.ui.late_minutes', ['minutes' => 12]), 'tone' => 'amber'],
            ['name' => 'Le Quoc Bao', 'department' => __('messages.erp.ui.production'), 'shift' => __('messages.erp.ui.afternoon_shift'), 'check_in' => '13:00', 'check_out' => '-', 'location' => __('messages.erp.ui.workshop_cnc'), 'status' => __('messages.erp.ui.currently_working'), 'tone' => 'blue'],
        ];
    }

    private function payrollRows(): array
    {
        $users = User::query()->select(['id', 'name', 'role'])->orderBy('id')->limit(12)->get();

        if ($users->isNotEmpty()) {
            return $users->map(function (User $user, int $index): array {
                $isAdmin = in_array(strtolower((string) $user->role), ['administrator', 'admin'], true);
                $salary = $isAdmin ? 42000000 : 18000000 + ($index * 1750000);
                $commission = $index % 2 === 0 ? 2500000 + ($index * 450000) : 0;
                $deduction = 600000 + ($index * 120000);

                return [
                    'name' => $user->name,
                    'department' => $isAdmin ? __('messages.erp.ui.executive_board') : ($index % 2 === 0 ? __('messages.erp.ui.sales') : __('messages.erp.ui.production')),
                    'salary' => $salary,
                    'commission' => $commission,
                    'deduction' => $deduction,
                    'net' => max(0, $salary + $commission - $deduction),
                    'status' => $index % 3 === 0 ? __('messages.erp.ui.draft') : ($index % 3 === 1 ? __('messages.erp.ui.waiting_manager') : __('messages.erp.ui.closed')),
                ];
            })->all();
        }

        return [
            ['name' => 'Nguyen Hoang Phi', 'department' => __('messages.erp.ui.executive_board'), 'salary' => 42000000, 'commission' => 8500000, 'deduction' => 1200000, 'net' => 49300000, 'status' => __('messages.erp.ui.draft')],
            ['name' => 'Tran Minh Anh', 'department' => __('messages.erp.ui.sales'), 'salary' => 22000000, 'commission' => 4200000, 'deduction' => 900000, 'net' => 25300000, 'status' => __('messages.erp.ui.waiting_manager')],
            ['name' => 'Le Quoc Bao', 'department' => __('messages.erp.ui.production'), 'salary' => 18000000, 'commission' => 0, 'deduction' => 600000, 'net' => 17400000, 'status' => __('messages.erp.ui.closed')],
        ];
    }

    private function categoryRows(): array
    {
        $categories = Product::query()
            ->select('category', DB::raw('COUNT(*) as products'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('products')
            ->get();

        if ($categories->isNotEmpty()) {
            return $categories->map(fn ($row): array => [
                'name' => (string) $row->category,
                'slug' => str($row->category)->slug()->toString(),
                'description' => __('messages.erp.ui.updated_description'),
                'products' => (int) $row->products,
            ])->all();
        }

        return [
            ['name' => 'Industrial board materials', 'slug' => 'industrial-board-materials', 'description' => 'Boards used for CNC production.', 'products' => 10],
            ['name' => 'Hardware and accessories', 'slug' => 'hardware-accessories', 'description' => 'Hinges, slides, screws, handles, and hanging rails.', 'products' => 10],
        ];
    }

    private function inventoryRows(ProductRepositoryInterface $productRepository): array
    {
        $locale = $this->locale();
        $products = Product::query()
            ->select(['sku', 'name', 'category', 'stock', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        if ($products->isNotEmpty()) {
            return $products->map(function (Product $product) use ($productRepository, $locale): array {
                $display = $productRepository->transformProductForDisplay($product, $locale);

                return [
                    'sku' => $product->sku,
                    'name' => $display['name'] ?: $product->name,
                    'category' => $display['category'] ?: __('messages.erp.ui.uncategorized'),
                    'stock' => (int) $product->stock,
                    'unit' => __('messages.erp.ui.unit_piece'),
                    'last' => __('messages.erp.ui.updated_at_time', ['time' => optional($product->updated_at)->format('d/m/Y H:i')]),
                ];
            })->all();
        }

        return [
            ['sku' => 'PLY-18-BIRCH', 'name' => $locale === 'en' ? 'Plywood 18mm' : 'Van plywood 18mm', 'category' => $locale === 'en' ? 'Materials - Industrial board' : 'Vat tu - Van cong nghiep', 'stock' => 46, 'unit' => __('messages.erp.ui.unit_piece'), 'last' => __('messages.erp.ui.incoming_from', ['quantity' => 20, 'code' => 'PO-026'])],
            ['sku' => 'HINGE-SOFT-35', 'name' => $locale === 'en' ? 'Soft-close hinge 35mm' : 'Ban le giam chan 35mm', 'category' => $locale === 'en' ? 'Hardware & accessories' : 'Ngu kim & Phu kien', 'stock' => 180, 'unit' => __('messages.erp.ui.unit_piece'), 'last' => __('messages.erp.ui.outgoing_for', ['quantity' => 32, 'code' => 'HD-2026-002'])],
        ];
    }

    private function procurementRows(): array
    {
        $products = Product::query()
            ->select(['sku', 'name', 'stock'])
            ->orderBy('stock')
            ->limit(12)
            ->get();

        if ($products->isNotEmpty()) {
            return $products->map(function (Product $product): array {
                $minimum = max(10, (int) ceil(((int) $product->stock + 10) * 0.35));

                return [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'minimum' => $minimum,
                    'status' => (int) $product->stock <= $minimum ? __('messages.erp.ui.waiting_receipt') : __('messages.erp.ui.on_time'),
                ];
            })->all();
        }

        return [
            ['sku' => 'PLY-18-BIRCH', 'name' => 'Plywood 18mm', 'stock' => 46, 'minimum' => 20, 'status' => __('messages.erp.ui.on_time')],
            ['sku' => 'EDGE-OAK-22', 'name' => 'Oak edge banding 22mm', 'stock' => 31, 'minimum' => 30, 'status' => __('messages.erp.ui.waiting_receipt')],
        ];
    }

    private function purchaseOrderRows(): array
    {
        $groups = Product::query()
            ->select(['brand', 'category', 'price', 'stock'])
            ->limit(200)
            ->get()
            ->groupBy(fn (Product $product): string => trim((string) ($product->brand ?: $product->category)) ?: __('messages.erp.ui.internal_supplier'))
            ->map(fn ($items, string $supplier): array => [
                'supplier' => $supplier,
                'total' => $items->sum(fn (Product $product): float => (float) $product->price * max(1, (int) $product->stock)),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(8);

        if ($groups->isNotEmpty()) {
            return $groups->map(fn ($group, int $index): array => [
                'code' => 'PO-' . now()->format('Y') . '-' . str_pad((string) ($index + 26), 3, '0', STR_PAD_LEFT),
                'supplier' => (string) $group['supplier'],
                'total' => (int) $group['total'],
                'status' => $index % 2 === 0 ? __('messages.erp.ui.waiting_receipt') : __('messages.erp.ui.draft'),
                'created_at' => now()->subDays($index + 1)->format('Y-m-d'),
            ])->all();
        }

        return [
            ['code' => 'PO-2026-026', 'supplier' => $this->locale() === 'en' ? 'Minh Long Supplier' : 'Nha cung cap Minh Long', 'total' => 68000000, 'status' => __('messages.erp.ui.waiting_receipt'), 'created_at' => '2026-05-03'],
            ['code' => 'PO-2026-027', 'supplier' => $this->locale() === 'en' ? 'An Phat Warehouse' : 'Kho van An Phat', 'total' => 32500000, 'status' => __('messages.erp.ui.draft'), 'created_at' => '2026-05-05'],
        ];
    }

    private function stockReportRows(ProductRepositoryInterface $productRepository): array
    {
        $locale = $this->locale();
        $products = Product::query()
            ->select(['sku', 'name', 'stock'])
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        if ($products->isNotEmpty()) {
            return $products->map(function (Product $product, int $index) use ($productRepository, $locale): array {
                $incoming = $index % 2 === 0 ? min(20, max(0, (int) $product->stock)) : 0;
                $outgoing = $index % 2 === 1 ? min(12, max(0, (int) $product->stock)) : 0;
                $closing = (int) $product->stock;
                $display = $productRepository->transformProductForDisplay($product, $locale);

                return [
                    'sku' => $product->sku,
                    'name' => $display['name'] ?: $product->name,
                    'opening' => max(0, $closing - $incoming + $outgoing),
                    'incoming' => $incoming,
                    'outgoing' => $outgoing,
                    'closing' => $closing,
                    'unit' => __('messages.erp.ui.unit_piece'),
                ];
            })->all();
        }

        return [
            ['sku' => 'PLY-18-BIRCH', 'name' => $locale === 'en' ? 'Plywood 18mm' : 'Van plywood 18mm', 'opening' => 26, 'incoming' => 20, 'outgoing' => 0, 'closing' => 46, 'unit' => __('messages.erp.ui.unit_piece')],
            ['sku' => 'HINGE-SOFT-35', 'name' => $locale === 'en' ? 'Soft-close hinge 35mm' : 'Ban le giam chan 35mm', 'opening' => 212, 'incoming' => 0, 'outgoing' => 32, 'closing' => 180, 'unit' => __('messages.erp.ui.unit_piece')],
        ];
    }

    private function liveMetrics(): array
    {
        $onlineUsers = $this->onlineUsersCount();
        $activityCount = ActivityNotification::query()->where('created_at', '>=', now()->subDay())->count();
        $productCount = Product::query()->count();
        $featuredCount = Product::query()->where('featured', true)->count();

        return [
            'online' => $onlineUsers,
            'views_24h' => max(1, ($activityCount * 8) + ($productCount * 12)),
            'ad_clicks' => $featuredCount * 3,
            'ad_spend_today' => $featuredCount * 120000,
        ];
    }

    private function onlineUsersCount(): int
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('sessions')) {
                return max(1, (int) DB::table('sessions')
                    ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                    ->count());
            }
        } catch (\Throwable) {
            return auth()->check() ? 1 : 0;
        }

        return auth()->check() ? 1 : 0;
    }

    private function securityAlerts(): array
    {
        return collect(['/api/.env', '/.git/config', '/wp-admin', '/vendor/phpunit', '/admin/login'])
            ->map(fn (string $path, int $index): array => [
                'message' => 'Blocked restricted path: ' . $path,
                'ip' => request()->ip() ?: '127.0.0.1',
                'level' => 'CRITICAL',
                'time' => now()->subMinutes($index * 17)->format('H:i:s'),
            ])
            ->all();
    }

    private function activityEvents(): array
    {
        $events = ActivityNotification::query()->latest()->limit(6)->get();

        if ($events->isNotEmpty()) {
            return $events->map(fn (ActivityNotification $event): array => [
                'message' => $event->subject_name ?: $event->action,
                'actor' => $event->actor_name ?: 'System',
                'time' => optional($event->created_at)->diffForHumans(),
            ])->all();
        }

        return [
            ['message' => 'Live tracking is active', 'actor' => auth()->user()->name ?? 'System', 'time' => now()->diffForHumans()],
        ];
    }

    private function employeeRows(): array
    {
        $users = User::query()->select(['id', 'name', 'email', 'role'])->orderBy('id')->limit(8)->get();

        if ($users->isNotEmpty()) {
            return $users->map(function (User $user, int $index): array {
                return [
                    'id' => 'NS-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                    'name' => $user->name,
                    'phone' => '09' . str_pad((string) (10000000 + ($index * 13759)), 8, '0', STR_PAD_LEFT),
                    'email' => $user->email,
                    'department' => $index % 2 === 0 ? __('messages.erp.ui.executive_board') : __('messages.erp.ui.sales'),
                    'title' => in_array(strtolower((string) $user->role), ['administrator', 'admin'], true) ? 'Administrator' : 'Staff',
                    'status' => __('messages.erp.ui.currently_working'),
                ];
            })->all();
        }

        return [
            ['id' => 'NS-0001', 'name' => 'Nguyen Hoang Phi', 'phone' => '0901 234 567', 'email' => 'phi@owlagency.vn', 'department' => __('messages.erp.ui.executive_board'), 'title' => 'CEO', 'status' => __('messages.erp.ui.currently_working')],
            ['id' => 'NS-0002', 'name' => 'Tran Minh Anh', 'phone' => '0912 444 888', 'email' => 'anh@owlagency.vn', 'department' => __('messages.erp.ui.sales'), 'title' => 'Sales specialist', 'status' => __('messages.erp.ui.currently_working')],
            ['id' => 'NS-0003', 'name' => 'Le Quoc Bao', 'phone' => '0988 222 111', 'email' => 'bao@owlagency.vn', 'department' => __('messages.erp.ui.production'), 'title' => 'CNC lead', 'status' => __('messages.erp.ui.currently_working')],
        ];
    }
}
