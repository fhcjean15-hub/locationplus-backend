<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bien_id',
        'user_id',
        'owner_id',
        'tracking_token',

        'client_name',
        'client_email',
        'client_phone',

        'category',
        'transaction_type',
        'reservation_type',
        'price',

        'start_date',
        'end_date',
        'visit_date',

        'message',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'visit_date' => 'date',
        'price'      => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            // UUID primaire
            $model->id = $model->id ?? (string) Str::uuid();

            // Tracking token auto pour invité
            if (empty($model->user_id) && empty($model->tracking_token)) {
                $model->tracking_token = (string) Str::uuid();
            }
        });
    }

    /* -------------------- RELATIONS -------------------- */

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /* -------------------- HELPERS -------------------- */

    public function isGuest(): bool
    {
        return is_null($this->user_id);
    }
}
