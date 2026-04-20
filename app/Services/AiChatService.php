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
            return 'Missing GEMINI_API_KEY in .env';
        }

        $instruction = implode("\n", [
            'Bạn là AI chuyên gia hỗ trợ chỉ về database của hệ thống Laravel hiện tại.',
            'QUY TẮC BẮT BUỘC:',
            '- Chỉ trả lời dựa trên "Database Context" được cung cấp (schema + số liệu tổng quan).',
            '- Tự động hiểu các thuật ngữ tiếng Việt sau đây tương đương với database:',
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

        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'assistant' ? 'model' : 'user',
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

        $response = Http::timeout(60)
            ->acceptJson()
            ->contentType('application/json')
            ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => $contents,
            ]);

        if (!$response->ok()) {
            throw new \RuntimeException('Gemini request failed (HTTP ' . $response->status() . '): ' . (string) $response->body());
        }

        $data = $response->json();
        $text = data_get($data, 'candidates.0.content.parts.0.text');

        if (!is_string($text) || $text === '') {
            throw new \RuntimeException('AI did not return text.');
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
        $mentionsUsers = (bool) preg_match('/(users?|người\s*dùng|thành\s*viên|tài\s*khoản)/u', $lower);
        $asksCount = (bool) preg_match('/(bao\s*nhiêu|tổng\s*số|số\s*lượng|đếm|count|mấy)/u', $lower);

        if ($mentionsUsers && $asksCount) {
            $count = DB::table('users')->count();
            return "Hiện tại hệ thống có **{$count}** người dùng (tài khoản).";
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
                $lines[] = 'users_exact_rows: ' . DB::table('users')->count();
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
