<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, AuthRepositoryInterface $authRepository)
    {
        $credentials = $request->only('email', 'password');

        if ($authRepository->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('messages.auth.failed'),
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, AuthRepositoryInterface $authRepository)
    {
        $user = $authRepository->register($request->validated());

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request, AuthRepositoryInterface $authRepository)
    {
        $authRepository->logout($request);

        return redirect()->route('login')->with('success', __('messages.auth.logged_out'));
    }

    public function dashboard()
    {
        $displayLocale = in_array(app()->getLocale(), ['vi', 'en'], true) ? app()->getLocale() : 'vi';

        // Greeting based on time of day
        $hour = now()->hour;
        if ($hour < 12) {
            $greetingKey = 'messages.dashboard.greeting_morning';
        } elseif ($hour < 18) {
            $greetingKey = 'messages.dashboard.greeting_afternoon';
        } else {
            $greetingKey = 'messages.dashboard.greeting_evening';
        }

        $currentDate = now()->translatedFormat('l, j F Y');

        // Recent activities
        $recentActivities = \App\Models\ActivityNotification::query()
            ->latest()
            ->limit(5)
            ->get();

        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->where('status', 'active')->count();
        $totalUsers = User::query()->count();
        $totalStock = (int) Product::query()->sum('stock');
        $categoryCount = Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->count('category');
        $brandCount = Product::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->count('brand');
        $inventoryValue = (float) Product::query()->selectRaw('COALESCE(SUM(COALESCE(price, 0) * COALESCE(stock, 0)), 0) as total')->value('total');
        $featuredProducts = Product::query()->where('featured', true)->count();
        $syncedProducts = Product::query()->where('synced_to_meta', true)->count();
        $publishedProducts = Product::query()->whereNotNull('published_at')->count();

        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $months = $months->push(now()->startOfMonth());

        $productSeries = Product::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as aggregate')
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->groupBy('period')
            ->pluck('aggregate', 'period');

        $userSeries = User::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as aggregate')
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->groupBy('period')
            ->pluck('aggregate', 'period');

        $chartLabels = $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->values();
        $productChartData = $months->map(fn (Carbon $month) => (int) ($productSeries[$month->format('Y-m')] ?? 0))->values();
        $userChartData = $months->map(fn (Carbon $month) => (int) ($userSeries[$month->format('Y-m')] ?? 0))->values();

        $topCategories = Product::query()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $latestProducts = Product::query()
            ->select(['id', 'name', 'price', 'stock', 'status', 'created_at'])
            ->latest()
            ->limit(4)
            ->get();

        $formattedInventoryValue = $this->formatCurrencyForLocale($inventoryValue, $displayLocale);
        $latestProducts->transform(function (Product $product) use ($displayLocale) {
            $product->setAttribute('display_name', $product->name);
            $product->setAttribute('display_price', $this->formatCurrencyForLocale((float) ($product->price ?? 0), $displayLocale));

            return $product;
        });

        return view('dashboard', [
            'greetingKey' => $greetingKey,
            'currentDate' => $currentDate,
            'recentActivities' => $recentActivities,
            'dashboardStats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'total_users' => $totalUsers,
                'total_stock' => $totalStock,
                'category_count' => $categoryCount,
                'brand_count' => $brandCount,
                'inventory_value' => $inventoryValue,
                'featured_products' => $featuredProducts,
                'synced_products' => $syncedProducts,
                'published_products' => $publishedProducts,
            ],
            'displayLocale' => $displayLocale,
            'formattedInventoryValue' => $formattedInventoryValue,
            'chartLabels' => $chartLabels,
            'productChartData' => $productChartData,
            'userChartData' => $userChartData,
            'topCategories' => $topCategories,
            'latestProducts' => $latestProducts,
        ]);
    }

    private function formatCurrencyForLocale(float $value, string $locale): string
    {
        if ($locale === 'en') {
            $usdRate = (float) config('services.product_export.usd_rate', 25000);
            $usdRate = $usdRate > 0 ? $usdRate : 25000;

            return '$' . number_format($value / $usdRate, 2);
        }

        return number_format($value, 2) . ' VND';
    }
}
