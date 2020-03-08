<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $saved_query_id
 * @property string $uid
 * @property string $sql
 * @property string $error
 * @property int $rows_count
 * @property array $preview
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Query extends Model
{
    protected $casts = [
        'rows_count' => 'int',
        'preview' => 'array',
    ];

    protected $visible = [
        'uid',
        'sql',
        'error',
    ];
}
