<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PcDevices;
use App\Models\User;
use Log;

class PcStatusController extends Controller
{
    public function status(Request $request){
        $request->validate([
            'macAddress' =>  'required'
        ]);

        $pc = PcDevices::where('mac_address', $request->macAddress)->first();

        if(!$pc->user_id){
            return response([
                'msg' => 'No user found on this PC'
            ], 404);
        }

        $user = User::findOrFail($pc->user_id);

        Log::info("User: {$user}");
        if ($user->remaining_session_time > 0) {
            $user->remaining_session_time = max(0, $user->remaining_session_time - 90); 
            $user->save();
        }

        return response()->json([
            'message' => 'Ping received',
            'remaining_time' => $user->remaining_session_time,
        ]);
    }
}
