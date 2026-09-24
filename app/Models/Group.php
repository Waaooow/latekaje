<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ['key', 'label'];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'group', 'label');
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'group', 'label')->where('status', 'aktif');
    }
}
