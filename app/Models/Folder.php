<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Folder
 *
 * @package App
 * @property string $folder_name
 * @property string $created_by
 */
class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = ['folder_name', 'created_by_id'];

    /**
     * Binding for user
     * @return BelongsTo
     */
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
