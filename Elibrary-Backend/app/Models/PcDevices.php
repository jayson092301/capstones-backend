<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcDevices extends Model
{
    protected $fillable = [
        'status',
        'pc_assignments_id',
        'device_name',
        'mac_address',
        'user_id',
        'start_time',
        'end_time'
    ];

    public function pcAssignment()
    {
        return $this->belongsTo(PcAssignments::class, 'pc_assignments_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
