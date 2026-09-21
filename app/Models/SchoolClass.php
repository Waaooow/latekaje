<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['key', 'label'];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'kelas', 'label');
    }
}
