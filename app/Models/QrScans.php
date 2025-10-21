<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrScans extends Model
{
    protected $fillable = [
        'user_id',
        'clockIn',
        'clockOut',
        'pc_device_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pcDevice(){
        return $this->belongsTo(PcDevices::class, 'pc_device_id');
    }
}
