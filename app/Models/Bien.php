<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Bien extends Model
{
    use HasFactory;

    // Utilisation d'IDs auto-incrémentés
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'category',
        'transaction_type',
        'title',
        'description',
        'price',
        'city',
        'district',
        'images',
        'attributes',
        'status',
        'actif',
    ];

    protected $casts = [
        'images'     => 'array',
        'attributes' => 'array',
        'price'      => 'float',
    ];

    // 🔗 Relation propriétaire
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accès facile à un attribut dynamique
     * $bien->attr('surface')
     */
    public function attr(string $key, $default = null)
    {
        return $this->attributes['attributes'][$key] ?? $default;
    }
}
