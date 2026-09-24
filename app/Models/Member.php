<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = ['code', 'name', 'group', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id');
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id')->where('status', 'aktif');
    }

    public function label(): string
    {
        return ($this->code ? $this->code.' — ' : '').$this->name.' ('.$this->group.')';
    }
}
