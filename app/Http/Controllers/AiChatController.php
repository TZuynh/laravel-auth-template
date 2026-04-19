<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['sometimes', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:2000'],
        ]);

        $apiKey = (string) config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $model = (string) config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash'));

        if ($apiKey === '') {
            return response()->json([
                'error' => 'Missing GEMINI_API_KEY in .env',
            ], 500);
        }

        $schemaContext = $this->buildDatabaseContext();
        $message = $validated['message'];

        $deterministic = $this->tryHandleDeterministicDatabaseQuestion($message, $schemaContext);
        if (is_string($deterministic) && $deterministic !== '') {
            return response()->json([
                'reply' => $deterministic,
            ]);
        }

        if (!$this->isDatabaseRelatedQuestion($message, $schemaContext['tables'] ?? [], $validated['history'] ?? [])) {
            return response()->json([
                'reply' => 'Xin lỗi, mình chỉ hỗ trợ các câu hỏi liên quan đến database của hệ thống (bảng/cột/quan hệ/dữ liệu).',
            ]);
        }

       $instruction = implode("\n", [
            'Bạn là AI chuyên gia hỗ trợ *chỉ* về database của hệ thống Laravel hiện tại.',
            'QUY TẮC BẮT BUỘC:',
            '- Chỉ trả lời dựa trên "Database Context" được cung cấp (schema + số liệu tổng quan).',
            '- TỰ ĐỘNG HIỂU các thuật ngữ tiếng Việt sau đây tương đương với database:',
            '  + "người dùng", "thành viên", "tài khoản" -> bảng `users`',
            '  + "tên", "họ tên" -> cột `name`',
            '  + "mật khẩu", "pass" -> cột `password`',
            '  + "quyền", "vai trò" -> cột `role`',
            '  + "ảnh đại diện", "avatar" -> cột `avatar`',
            '- Nếu câu hỏi không liên quan database hoặc cần kiến thức bên ngoài: từ chối lịch sự.',
            '- Không bịa ra bảng/cột/quan hệ không có trong context.',
            '- Không hướng dẫn các thao tác phá hoại (DROP/TRUNCATE/DELETE không điều kiện).',
            '',
            'Database Context:',
            $schemaContext['text'],
        ]);

        $contents = [];
        foreach (($validated['history'] ?? []) as $turn) {
            $role = $turn['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $turn['content']],
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

        $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(60)
                ->acceptJson()
                ->contentType('application/json')
                ->post($url, [
                    'contents' => $contents,
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Gemini request failed',
                'details' => $e->getMessage(),
            ], 502);
        }

        if (!$response->ok()) {
            return response()->json([
                'error' => 'Gemini request failed',
                'status' => $response->status(),
                'details' => $response->json(),
                'body' => mb_substr((string) $response->body(), 0, 2000),
            ], 502);
        }

        $data = $response->json();
        $text = data_get($data, 'candidates.0.content.parts.0.text');

        if (!is_string($text) || $text === '') {
            return response()->json([
                'error' => 'AI did not return text',
                'details' => $data,
            ], 502);
        }

        return response()->json([
            'reply' => trim($text),
        ]);
    }

    private function isDatabaseRelatedQuestion(string $text, array $tables, array $history = []): bool
    {
        if ($this->isDatabaseRelatedBase($text, $tables)) {
            return true;
        }

        // Allow short follow-up questions (e.g. "email đó là gì") when the recent context is database-related.
        if (!$this->isLikelyFollowup($text)) {
            return false;
        }

        $recent = array_values(array_slice($history, -4));
        foreach (array_reverse($recent) as $turn) {
            $content = $turn['content'] ?? null;
            if (is_string($content) && $this->isDatabaseRelatedBase($content, $tables)) {
                return true;
            }
        }

        return false;
    }

    private function isDatabaseRelatedBase(string $text, array $tables): bool
    {
        $lower = mb_strtolower($text);

        $keywords = [
            'database', 'db', 'schema', 'migration',
            'bảng', 'table', 'cột', 'column', 'trường', 'field',
            'khóa', 'index', 'unique', 'primary', 'foreign',
            'quan hệ', 'relationship', 'join', 'liên kết',
            'dữ liệu', 'data', 'record', 'row', 'dòng', 'bản ghi',
            'sql', 'select', 'insert', 'update', 'delete', 'xóa', 'thêm', 'sửa',
            'user', 'users', 'người dùng', 'thành viên', 'tài khoản', 'account',
            'email', 'e-mail', 'thư',
            'role', 'quyền', 'vai trò',
            'password', 'mật khẩu', 'pass',
            'name', 'tên', 'họ tên',
            'avatar', 'ảnh đại diện', 'hình đại diện',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
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
        $t = mb_strtolower(trim($text));
        if ($t === '') {
            return false;
        }

        // Short / referential phrases indicating context dependency.
        if (mb_strlen($t) <= 40) {
            $signals = [
                'đó', 'do', 'này', 'nay', 'kia', 'ở trên', 'o tren', 'vừa nãy', 'vua nay',
                'còn', 'thế', 'vậy', 'như vậy', 'nhu vay',
            ];
            foreach ($signals as $s) {
                if (str_contains($t, $s)) {
                    return true;
                }
            }
        }

        return false;
    }

   private function tryHandleDeterministicDatabaseQuestion(string $message, array $schemaContext): ?string
    {
        $lower = mb_strtolower(trim($message));

        // Cải thiện Regex để nhận diện nhiều từ vựng đếm user hơn
        $mentionsUsers = (bool) preg_match('/(users?|người\s*dùng|thành\s*viên|tài\s*khoản)/u', $lower);
        $asksCount = (bool) preg_match('/(bao\s*nhiêu|bao\s*nhieu|tổng\s*số|tong\s*so|số\s*lượng|so\s*luong|đếm|dem|count|mấy|may)/u', $lower);
        
        if ($mentionsUsers && $asksCount) {
            $count = DB::table('users')->count();
            return "Hiện tại hệ thống có **{$count}** người dùng (tài khoản).";
        }

        // Cải thiện Regex hỏi Email
        $asksEmail = (bool) preg_match('/(email|e-mail)/u', $lower);
        $asksWhat = (bool) preg_match('/(là\s*gì|la\s*gi|gì|gi|what|danh\s*sách|liệt\s*kê)/u', $lower);
        
        if ($asksEmail && $asksWhat) {
            $rows = DB::table('users')
                ->select(['id', 'name', 'email', 'role'])
                ->orderBy('id')
                ->limit(10)
                ->get();

            if ($rows->isEmpty()) {
                return "Bảng `users` hiện chưa có dữ liệu.";
            }

            $lines = [];
            foreach ($rows as $r) {
                $lines[] = "#{$r->id} {$r->name} — {$r->email} (" . ($r->role ?? 'staff') . ')';
            }

            $total = DB::table('users')->count();
            return "Danh sách email (tối đa 10/{$total}) trong hệ thống:\n" . implode("\n", $lines);
        }

        // Đếm số bảng
        if ((bool) preg_match('/(bao\s*nhiêu|tổng\s*số|count|số\s*lượng)/u', $lower) && (str_contains($lower, 'bảng') || str_contains($lower, 'table'))) {
            $tables = $schemaContext['tables'] ?? [];
            if (is_array($tables) && count($tables) > 0) {
                $n = count($tables);
                return "Database hiện có **{$n}** bảng: " . implode(', ', array_map(fn ($t) => "`{$t}`", $tables)) . '.';
            }
        }

        return null;
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
                DB::select(
                    'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
                    [$database]
                )
            );

            $tables = array_values(array_slice($tables, 0, 30));

            $lines[] = "Driver: mysql";
            $lines[] = "Database: {$database}";
            $lines[] = 'Tables: ' . implode(', ', $tables);
            if (in_array('users', $tables, true)) {
                $usersCount = DB::table('users')->count();
                $lines[] = "users_exact_rows: {$usersCount}";
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

                foreach ($columns as $col) {
                    $name = (string) $col->COLUMN_NAME;
                    $type = (string) $col->COLUMN_TYPE;
                    $nullable = (string) $col->IS_NULLABLE;
                    $default = $col->COLUMN_DEFAULT;
                    $key = (string) $col->COLUMN_KEY;
                    $defaultText = $default === null ? 'NULL' : (string) $default;
                    $keyText = $key !== '' ? " key={$key}" : '';
                    $lines[] = "- {$name}: {$type} nullable={$nullable} default={$defaultText}{$keyText}";
                }

                $lines[] = '';
            }
        } else {
            // Fallback (driver not handled): minimal context
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
