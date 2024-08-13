<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['name','space_id', 'start_time', 'end_time', 'date_booking'];

    //hoặc dùng boot validate trong model

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
