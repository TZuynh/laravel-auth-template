<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiChatService
{
    public function reply(string $message, array $history = []): string
    {
        $schemaContext = $this->buildDatabaseContext();

        if ($deterministic = $this->tryHandleDeterministicDatabaseQuestion($message, $schemaContext)) {
            return $deterministic;
        }

        if (!$this->isDatabaseRelatedQuestion($message, $schemaContext['tables'] ?? [], $history)) {
            return 'Xin lỗi, mình chỉ hỗ trợ các câu hỏi liên quan đến database của hệ thống (bảng/cột/quan hệ/dữ liệu).';
        }

        $apiKey = (string) config('services.gemini.api_key', '');
        $model = (string) config('services.gemini.model', 'gemini-2.5-flash');

        if ($apiKey === '') {
            return $this->buildAiUnavailableReply($schemaContext, 'Gemini API key is missing.');
        }

        $instruction = implode("\n", [
            'Bạn là AI chuyên gia hỗ trợ chỉ về database của hệ thống Laravel hiện tại.',
            'QUY TẮC BẮT BUỘC:',
            '- Chỉ trả lời dựa trên "Database Context" được cung cấp (schema + số liệu tổng quan).',
            '- Ưu tiên trả lời có cấu trúc rõ ràng: ngắn gọn, có gạch đầu dòng hoặc số liệu khi phù hợp.',
            '- Tự động hiểu các thuật ngữ tiếng Việt sau đây tương đương với database:',
            '  + "người dùng", "thành viên", "tài khoản" -> bảng `users`',
            '  + "sản phẩm", "product", "products" -> bảng `products`',
            '  + "import", "export", "csv", "upload file" -> hoạt động dữ liệu của bảng',
            '  + "tên", "họ tên" -> cột `name`',
            '  + "mật khẩu", "pass" -> cột `password`',
            '  + "quyền", "vai trò", "phân quyền" -> cột `role`',
            '  + "ảnh đại diện", "avatar" -> cột `avatar`',
            '  + "thống kê", "tổng hợp", "báo cáo", "số liệu", "dashboard", "metrics" -> câu hỏi thống kê database',
            '- Nếu câu hỏi không liên quan database hoặc cần kiến thức bên ngoài: từ chối lịch sự.',
            '- Không bịa ra bảng/cột/quan hệ không có trong context.',
            '- Không hướng dẫn các thao tác phá hoại (DROP/TRUNCATE/DELETE không điều kiện).',
            '',
            'Database Context:',
            $schemaContext['text'],
        ]);

        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role' => ($turn['role'] ?? '') === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => (string) ($turn['content'] ?? '')],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $instruction],
                ['text' => "Câu hỏi: {$message}"],
            ],
        ];

        try {
            $response = Http::timeout(60)
                ->acceptJson()
                ->contentType('application/json')
                ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => $contents,
                ]);
        } catch (\Throwable $exception) {
            return $this->buildAiUnavailableReply($schemaContext, $exception->getMessage());
        }

        if (!$response->ok()) {
            return $this->buildAiUnavailableReply(
                $schemaContext,
                'Gemini request failed (HTTP ' . $response->status() . ').'
            );
        }

        $data = $response->json();
        $text = data_get($data, 'candidates.0.content.parts.0.text');

        if (!is_string($text) || $text === '') {
            return $this->buildAiUnavailableReply($schemaContext, 'Gemini did not return text.');
        }

        return trim($text);
    }

    private function isDatabaseRelatedQuestion(string $text, array $tables, array $history = []): bool
    {
        if ($this->isDatabaseRelatedBase($text, $tables)) {
            return true;
        }

        if (!$this->isLikelyFollowup($text)) {
            return false;
        }

        foreach (array_reverse(array_slice($history, -4)) as $turn) {
            if (is_string($turn['content'] ?? null) && $this->isDatabaseRelatedBase($turn['content'], $tables)) {
                return true;
            }
        }

        return false;
    }

    private function isDatabaseRelatedBase(string $text, array $tables): bool
    {
        $lower = mb_strtolower($text);
        $keywords = [
            'database', 'db', 'schema', 'migration', 'seed', 'factory',
            'bảng', 'table', 'cột', 'column', 'trường', 'field',
            'khóa', 'index', 'unique', 'primary', 'foreign',
            'quan hệ', 'relationship', 'join', 'liên kết', 'khóa ngoại',
            'dữ liệu', 'data', 'record', 'row', 'dòng', 'bản ghi', 'dataset',
            'sql', 'select', 'insert', 'update', 'delete', 'create', 'alter',
            'xóa', 'thêm', 'sửa', 'tìm kiếm', 'lọc', 'sắp xếp', 'phân trang', 'import', 'export', 'csv', 'upload',
            'thống kê', 'tổng hợp', 'báo cáo', 'dashboard', 'metrics', 'statistics',
            'user', 'users', 'người dùng', 'thành viên', 'tài khoản', 'account', 'role', 'phân quyền',
            'product', 'products', 'sản phẩm', 'sku', 'giá', 'stock', 'tồn kho', 'ảnh', 'image',
            'category', 'danh mục', 'brand', 'thương hiệu', 'featured', 'synced to meta', 'product form',
            'email', 'e-mail', 'thư', 'password', 'mật khẩu', 'pass',
            'name', 'tên', 'họ tên', 'avatar', 'ảnh đại diện', 'hình đại diện',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        foreach ($tables as $table) {
            $tableLower = mb_strtolower((string) $table);
            if ($tableLower !== '' && str_contains($lower, $tableLower)) {
                return true;
            }
        }

        return false;
    }

    private function isLikelyFollowup(string $text): bool
    {
        $lower = mb_strtolower(trim($text));

        if ($lower === '' || mb_strlen($lower) > 40) {
            return false;
        }

        foreach (['đó', 'này', 'kia', 'ở trên', 'vừa nãy', 'còn', 'thế', 'vậy', 'như vậy'] as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function tryHandleDeterministicDatabaseQuestion(string $message, array $schemaContext): ?string
    {
        $lower = mb_strtolower(trim($message));
        $tables = $schemaContext['tables'] ?? [];

        $mentionsUsers = (bool) preg_match('/(users?|người\s*dùng|thành\s*viên|tài\s*khoản)/u', $lower);
        $mentionsProducts = (bool) preg_match('/(products?|sản\s*phẩm|sku|giá|stock|tồn\s*kho|danh\s*mục|thương\s*hiệu|featured|synced\s*to\s*meta)/u', $lower);
        $asksCount = (bool) preg_match('/(bao\s*nhiêu|tổng\s*số|số\s*lượng|đếm|count|mấy)/u', $lower);
        $asksStats = (bool) preg_match('/(thống\s*kê|tổng\s*hợp|báo\s*cáo|summary|overview|statistics|metrics|dashboard)/u', $lower);
        $productId = $this->extractProductId($message, $mentionsProducts);

        if ($productId !== null && !$mentionsUsers && in_array('products', $tables, true)) {
            return $this->describeProductById($productId);
        }

        if ($mentionsUsers && $asksCount) {
            $count = DB::table('users')->count();
            $admins = DB::table('users')->whereRaw('LOWER(role) in (?, ?)', ['administrator', 'admin'])->count();
            $staff = max(0, $count - $admins);

            return implode("\n", [
                "Hiện tại hệ thống có **{$count}** người dùng (tài khoản).",
                "• Administrator: **{$admins}**",
                "• Staff: **{$staff}**",
            ]);
        }

        if ($mentionsProducts && $asksCount && in_array('products', $tables, true)) {
            $count = DB::table('products')->count();
            $active = DB::table('products')->where('status', 'active')->count();
            $inactive = DB::table('products')->where('status', 'inactive')->count();

            return implode("\n", [
                "Hiện tại hệ thống có **{$count}** sản phẩm.",
                "• Active: **{$active}**",
                "• Inactive: **{$inactive}**",
            ]);
        }

        if ($mentionsUsers && $asksStats && in_array('users', $tables, true)) {
            $count = DB::table('users')->count();
            $admins = DB::table('users')->whereRaw('LOWER(role) in (?, ?)', ['administrator', 'admin'])->count();
            $staff = max(0, $count - $admins);

            $rows = DB::table('users')
                ->select(['id', 'name', 'email', 'role'])
                ->orderBy('id', 'asc')
                ->limit(5)
                ->get();

            $sample = $rows->map(fn ($row) => "#{$row->id} {$row->name} | {$row->email} | " . ($row->role ?? 'staff'))->implode("\n");

            return implode("\n", array_filter([
                "Tổng quan bảng `users`: **{$count}** dòng.",
                "• Administrator: **{$admins}**",
                "• Staff: **{$staff}**",
                $sample ? "• Mẫu dữ liệu:\n{$sample}" : null,
            ]));
        }

        if ($mentionsProducts && $asksStats && in_array('products', $tables, true)) {
            $count = DB::table('products')->count();
            $active = DB::table('products')->where('status', 'active')->count();
            $inactive = DB::table('products')->where('status', 'inactive')->count();
            $featured = DB::table('products')->where('featured', true)->count();
            $brands = DB::table('products')->whereNotNull('brand')->distinct()->count('brand');
            $categories = DB::table('products')->whereNotNull('category')->distinct()->count('category');

            $rows = DB::table('products')
                ->select(['id', 'name', 'sku', 'price', 'stock', 'status'])
                ->orderBy('id', 'asc')
                ->limit(5)
                ->get();

            $sample = $rows->map(fn ($row) => "#{$row->id} {$row->name} | SKU: {$row->sku} | {$row->status}")->implode("\n");

            return implode("\n", array_filter([
                "Tổng quan bảng `products`: **{$count}** dòng.",
                "• Active: **{$active}**",
                "• Inactive: **{$inactive}**",
                "• Featured: **{$featured}**",
                "• Brands: **{$brands}** | Categories: **{$categories}**",
                $sample ? "• Mẫu dữ liệu:\n{$sample}" : null,
            ]));
        }

        if ($mentionsProducts && preg_match('/(liệt\s*kê|danh\s*sách|xem\s*tất\s*cả|show|list|sample)/u', $lower) && in_array('products', $tables, true)) {
            $rows = DB::table('products')
                ->select(['id', 'name', 'sku', 'price', 'stock', 'category', 'brand', 'status'])
                ->orderBy('id', 'asc')
                ->limit(10)
                ->get();

            if ($rows->isEmpty()) {
                return 'Bảng `products` hiện chưa có dữ liệu.';
            }

            $lines = [];
            foreach ($rows as $row) {
                $lines[] = "#{$row->id} {$row->name} | SKU: {$row->sku} | Giá: " . number_format((float) ($row->price ?? 0), 2) . " | Stock: " . (int) ($row->stock ?? 0) . " | {$row->status}";
            }

            $total = DB::table('products')->count();
            return "Danh sách sản phẩm mẫu (tối đa 10/{$total}):\n" . implode("\n", $lines);
        }

        if ($mentionsProducts && preg_match('/(active|inactive|đang\s*hoạt\s*động|ngưng|trạng\s*thái)/u', $lower) && in_array('products', $tables, true)) {
            $active = DB::table('products')->where('status', 'active')->count();
            $inactive = DB::table('products')->where('status', 'inactive')->count();

            return "Sản phẩm đang active: **{$active}**, inactive: **{$inactive}**.";
        }

        $asksEmail = (bool) preg_match('/(email|e-mail)/u', $lower);
        $asksWhat = (bool) preg_match('/(là\s*gì|gì|what|danh\s*sách|liệt\s*kê)/u', $lower);

        if ($asksEmail && $asksWhat) {
            $rows = DB::table('users')
                ->select(['id', 'name', 'email', 'role'])
                ->orderBy('id', 'asc')
                ->limit(10)
                ->get();

            if ($rows->isEmpty()) {
                return 'Bảng `users` hiện chưa có dữ liệu.';
            }

            $lines = [];
            foreach ($rows as $row) {
                $lines[] = "#{$row->id} {$row->name} - {$row->email} (" . ($row->role ?? 'staff') . ')';
            }

            $total = DB::table('users')->count();
            return "Danh sách email (tối đa 10/{$total}) trong hệ thống:\n" . implode("\n", $lines);
        }

        if ((bool) preg_match('/(bao\s*nhiêu|tổng\s*số|count|số\s*lượng)/u', $lower) && (str_contains($lower, 'bảng') || str_contains($lower, 'table'))) {
            $tables = $schemaContext['tables'] ?? [];
            if (is_array($tables) && count($tables) > 0) {
                return 'Database hiện có **' . count($tables) . '** bảng: ' . implode(', ', array_map(fn ($table) => "`{$table}`", $tables)) . '.';
            }
        }

        return null;
    }

    private function extractProductId(string $message, bool $mentionsProducts): ?int
    {
        $trimmed = trim($message);

        if (preg_match('/^#?(\d{1,12})$/u', $trimmed, $match)) {
            $id = (int) $match[1];

            return $id > 0 ? $id : null;
        }

        $asksDetail = (bool) preg_match('/(chi\s*tiết|detail|details|xem|show|tìm|tim|tra\s*cứu|lookup|id|#|mã|ma)/iu', $message);

        if (!$mentionsProducts && !$asksDetail) {
            return null;
        }

        $patterns = [
            '/(?:products?|sản\s*phẩm|san\s*pham|sp)\D{0,24}(?:id|#|mã|ma)?\D{0,8}(\d{1,12})/iu',
            '/(?:id|#|mã|ma)\D{0,8}(\d{1,12})\D{0,24}(?:products?|sản\s*phẩm|san\s*pham|sp)/iu',
            '/(?:chi\s*tiết|detail|details|xem|show|tìm|tim|tra\s*cứu|lookup)\D{0,32}(?:products?|sản\s*phẩm|san\s*pham|sp)?\D{0,16}(?:id|#|mã|ma)?\D{0,8}(\d{1,12})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $match)) {
                $id = (int) $match[1];

                return $id > 0 ? $id : null;
            }
        }

        if ($mentionsProducts && $asksDetail && preg_match('/\b(\d{1,12})\b/u', $message, $match)) {
            $id = (int) $match[1];

            return $id > 0 ? $id : null;
        }

        return null;
    }

    private function describeProductById(int $productId): string
    {
        $product = DB::table('products')
            ->select([
                'id',
                'name',
                'sku',
                'image',
                'price',
                'stock',
                'category',
                'brand',
                'tags',
                'featured',
                'synced_to_meta',
                'status',
                'product_form',
                'published_at',
                'seo_title',
                'seo_description',
                'created_at',
                'updated_at',
            ])
            ->where('id', $productId)
            ->first();

        if (!$product) {
            return "Không tìm thấy sản phẩm có ID **{$productId}** trong bảng `products`.";
        }

        return implode("\n", array_filter([
            "Chi tiết sản phẩm #{$product->id}:",
            "- Tên: **" . $this->displayProductValue($product->name) . "**",
            "- Giá: **" . $this->formatProductPrice($product->price) . "**",
            "- SKU: `" . $this->displayProductValue($product->sku) . "`",
            "- Tồn kho: **" . number_format((int) ($product->stock ?? 0)) . "**",
            "- Trạng thái: **" . $this->formatProductStatus($product->status) . "**",
            "- Danh mục: " . $this->displayProductValue($product->category),
            "- Thương hiệu: " . $this->displayProductValue($product->brand),
            "- Loại/form sản phẩm: " . $this->displayProductValue($product->product_form),
            "- Nổi bật: " . $this->formatBooleanLabel($product->featured ?? false),
            "- Đồng bộ Meta: " . $this->formatBooleanLabel($product->synced_to_meta ?? false),
            "- Ngày đăng: " . $this->displayProductValue($product->published_at),
            "- Ảnh: " . $this->displayProductValue($product->image),
            "- Tags: " . $this->formatProductTags($product->tags),
            "- SEO title: " . $this->displayProductValue($product->seo_title),
            "- SEO description: " . $this->trimForChat($product->seo_description),
            "- Tạo lúc: " . $this->displayProductValue($product->created_at),
            "- Cập nhật lúc: " . $this->displayProductValue($product->updated_at),
        ]));
    }

    private function displayProductValue(mixed $value, string $empty = 'Chưa có'): string
    {
        $text = trim((string) $value);

        return $text === '' ? $empty : $text;
    }

    private function formatProductPrice(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return 'Chưa có';
        }

        $number = (float) $text;
        $decimals = abs($number - round($number)) < 0.01 ? 0 : 2;

        return number_format($number, $decimals, ',', '.') . ' VND';
    }

    private function formatProductStatus(mixed $value): string
    {
        $status = mb_strtolower(trim((string) $value));

        return match ($status) {
            'active', 'dang hoat dong', 'đang hoạt động' => 'Đang hoạt động',
            'inactive', 'ngung hoat dong', 'ngưng hoạt động' => 'Ngưng hoạt động',
            default => $this->displayProductValue($value),
        };
    }

    private function formatBooleanLabel(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Có' : 'Không';
    }

    private function formatProductTags(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : preg_split('/[|,;]+/', $value);
        }

        if (!is_array($value)) {
            return 'Chưa có';
        }

        $tags = array_values(array_filter(array_map(fn ($tag) => trim((string) $tag), $value)));

        return $tags === [] ? 'Chưa có' : implode(', ', $tags);
    }

    private function trimForChat(mixed $value, int $limit = 260): string
    {
        $text = $this->displayProductValue($value);

        if ($text === 'Chưa có' || mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 3) . '...';
    }

    private function buildAiUnavailableReply(array $schemaContext, string $reason = ''): string
    {
        $tables = $schemaContext['tables'] ?? [];
        $tableText = is_array($tables) && $tables !== []
            ? 'Bảng hiện có: ' . implode(', ', array_map(fn ($table) => "`{$table}`", $tables)) . '.'
            : null;

        return implode("\n", array_filter([
            'Mình chưa kết nối được Gemini/API ngoài lúc này, nhưng các câu hỏi database nội bộ vẫn có thể trả lời trực tiếp.',
            'Bạn có thể thử: `id 123`, `chi tiết sản phẩm id 123`, `có bao nhiêu sản phẩm`, `thống kê products`, hoặc `danh sách sản phẩm`.',
            $tableText,
            config('app.debug') && $reason !== ''
                ? 'Gợi ý kỹ thuật: kiểm tra mạng, `GEMINI_API_KEY` hoặc `GEMINI_MODEL`.'
                : null,
        ]));
    }

    private function buildDatabaseContext(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = (string) config('database.connections.' . config('database.default') . '.database', '');

        $tables = [];
        $lines = [];

        if ($driver === 'mysql') {
            $tables = array_map(
                fn ($row) => (string) $row->TABLE_NAME,
                DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME', [$database])
            );

            $tables = array_values(array_slice($tables, 0, 30));
            $lines[] = 'Driver: mysql';
            $lines[] = "Database: {$database}";
            $lines[] = 'Tables: ' . implode(', ', $tables);

            if (in_array('users', $tables, true)) {
                $userCount = DB::table('users')->count();
                $adminCount = DB::table('users')->whereRaw('LOWER(role) in (?, ?)', ['administrator', 'admin'])->count();
                $staffCount = max(0, $userCount - $adminCount);

                $lines[] = 'users_exact_rows: ' . $userCount;
                $lines[] = 'users_admin_rows: ' . $adminCount;
                $lines[] = 'users_staff_rows: ' . $staffCount;
                $lines[] = 'users_sample_fields: id, name, email, role, avatar';
            }

            if (in_array('products', $tables, true)) {
                $productCount = DB::table('products')->count();
                $activeProducts = DB::table('products')->where('status', 'active')->count();
                $featuredProducts = DB::table('products')->where('featured', true)->count();

                $lines[] = 'products_exact_rows: ' . $productCount;
                $lines[] = 'products_active_rows: ' . $activeProducts;
                $lines[] = 'products_featured_rows: ' . $featuredProducts;
                $lines[] = 'products_sample_fields: id, name, sku, image, price, stock, category, brand, status, product_form, published_at, featured, synced_to_meta';
            }

            $lines[] = '';

            foreach ($tables as $table) {
                $columns = DB::select(
                    'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY
                     FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                     ORDER BY ORDINAL_POSITION',
                    [$database, $table]
                );

                $approxRows = DB::selectOne(
                    'SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                    [$database, $table]
                );

                $rowCount = $approxRows?->TABLE_ROWS ?? null;
                $lines[] = "Table: {$table}" . (is_numeric($rowCount) ? " (approx_rows={$rowCount})" : '');

                foreach ($columns as $column) {
                    $defaultText = $column->COLUMN_DEFAULT === null ? 'NULL' : (string) $column->COLUMN_DEFAULT;
                    $keyText = $column->COLUMN_KEY !== '' ? " key={$column->COLUMN_KEY}" : '';
                    $lines[] = '- ' . $column->COLUMN_NAME . ': ' . $column->COLUMN_TYPE . ' nullable=' . $column->IS_NULLABLE . ' default=' . $defaultText . $keyText;
                }

                $lines[] = '';
            }
        } else {
            $lines[] = "Driver: {$driver}";
            $lines[] = "Database: {$database}";
            $lines[] = 'Context: Schema introspection for this driver is not implemented.';
        }

        return [
            'tables' => $tables,
            'text' => trim(implode("\n", $lines)),
        ];
    }
}
