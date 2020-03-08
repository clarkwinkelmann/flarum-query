<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uid
 * @property string $title
 * @property string $sql
 * @property array $preview
 * @property boolean $public
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SavedQuery extends Model
{
    protected $casts = [
        'preview' => 'array',
        'public' => 'bool',
    ];

    protected $visible = [
        'uid',
        'title',
        'sql',
        'preview',
        'public',
        'created_at',
    ];
}
