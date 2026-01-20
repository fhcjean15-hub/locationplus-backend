<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    use HasFactory;

    protected $table = 'account_categories';
    protected $fillable = ['name','kind','max_annonces','description','price'];
}
