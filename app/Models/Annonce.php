<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Annonce extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['owner_id','category_id','title','description','price','location_text','lat','lng','data_json','status'];
    protected $casts = ['data_json' => 'array'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class);
    }

    public function signalements()
    {
        return $this->hasMany(Signalement::class);
    }
}
