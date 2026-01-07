<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Bien;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\AccountCategory;
use App\Models\Annonce;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'company_name',
        'email',
        'phone',
        'avatar_url',
        'ifu',           // ajouté
        'adresse',       // ajouté
        'ville',
        'password',
        'account_type',
        'account_category_id',
        'documents_urls',
        'verified_documents',
        'activated',
        'payment_status',
        'payment_valid_until', // ajouté
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'documents_urls' => 'array',
            'verified_documents' => 'boolean',
            'activated' => 'boolean',
            'payment_valid_until' => 'datetime', // ajouté
        ];
    }

    public function accountCategory()
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'owner_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function biens()
    {
        return $this->hasMany(Bien::class);
    }

    public function signalements()
    {
        return $this->hasMany(Signalement::class, 'reporter_user_id');
    }
}
