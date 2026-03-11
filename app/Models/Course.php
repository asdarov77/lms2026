<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use Filterable, HasFactory, LogsActivity;

    protected $guarded = [];

    protected $fillable = ['title', 'short_description', 'long_description', 'path', 'hash', 'visible', 'aircraft_id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function aukstructures()
    {
        return $this->hasMany(Aukstructure::class);
    }

    public function group2learnings()
    {
        return $this->hasMany(Group2learning::class);
    }

    public static function generateHash(string $aircraftPath, string $coursePath): string
    {
        return hash('crc32b', $aircraftPath.$coursePath.time());
    }
}
