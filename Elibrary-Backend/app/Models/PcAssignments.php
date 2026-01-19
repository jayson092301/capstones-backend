<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcAssignments extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'clockIn',
        'clockOut'
    ];

    // public function user(){
    //     return $this->hasMany(User::class);
    // }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pcDevices()
    {
        return $this->hasMany(PcDevices::class, 'pc_assignments_id');
    }
}
