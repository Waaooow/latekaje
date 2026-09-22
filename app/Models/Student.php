<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['nis', 'nama', 'kelas', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function label(): string
    {
        return ($this->nis ? $this->nis.' — ' : '').$this->nama.' ('.$this->kelas.')';
    }
}
