<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leavetype extends Model
{
    use HasFactory;

    protected $table  = 'leaves_type';

    protected $fillable = ['name'];
}
