<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ProductExport extends Model
{
    private static ?array $availableColumns = null;

    protected $fillable = [
        'user_id',
        'status',
        'search_query',
        'filters',
        'export_format',
        'export_locale',
        'options',
        'disk',
        'path',
        'download_name',
        'error_message',
        'total_rows',
        'processed_rows',
        'started_at',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'options' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'started_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fillExisting(array $attributes): static
    {
        $columns = $this->availableColumns();
        $filtered = array_intersect_key($attributes, array_flip($columns));

        return $this->forceFill($filtered);
    }

    public function supportsColumn(string $column): bool
    {
        return in_array($column, $this->availableColumns(), true);
    }

    private function availableColumns(): array
    {
        if (self::$availableColumns === null) {
            self::$availableColumns = Schema::getColumnListing($this->getTable());
        }

        return self::$availableColumns;
    }
}
