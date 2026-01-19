<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\QrScans;
use Illuminate\Support\Carbon;
use App\Models\PcDevices;
use App\Models\PcAssignments;
use DB;
use Log;
class ScanController extends Controller
{
    public function keepAlive(Request $request) {
        try {
            $macAddress = $request->input('macAddress');
            $idNumber   = $request->input('id_number');

            $pc = PcDevices::where('mac_address', $macAddress)->first();
            $user = User::where('id_number', $idNumber)->first();
            if (!$user || !$pc) {
                return response()->json([
                    'success' => false,
                    'msg' => 'There is no such record found.'
                ], 404);
            }

            $lastScan = QrScans::where('user_id', $user->id)
                ->whereDate('clockIn', Carbon::today())
                ->latest()
                ->first();

            if ($lastScan) {
                if ($lastScan->clockOut === null) {
                    DB::transaction(function() use ($lastScan, $user) {
                        $lastScan->clockOut = Carbon::now();
                        $lastScan->save();
    
                        $assignment = PcAssignments::where('user_id', $user->id)
                            ->whereNull('clockOut')
                            ->latest()
                            ->first();
    
                        if ($assignment) {
                            $assignment->clockOut = Carbon::now();
                            $assignment->save();

                            $device = PcDevices::where('pc_assignments_id', $assignment->id)->first();

                            if ($device) {
                                $endTime = now();
                                $startTime = Carbon::parse($endTime->toDateString() . ' ' . $device->start_time);
                                if ($startTime->gt($endTime)) {
                                    $startTime->subDay();
                                }

                                $usedSeconds = abs($endTime->diffInSeconds($startTime, false));

                                $user = $assignment->user;
                                $remainingSeconds = $user->remaining_session_time ?? 0;
                                $newRemainingSeconds = max(0, $remainingSeconds - $usedSeconds);

                                $maxSeconds = (23 * 3600) + (59 * 60) + 59;
                                $newRemainingSeconds = min($newRemainingSeconds, $maxSeconds);

                                Log::info("Used: {$usedSeconds}");
                                Log::info("End: {$endTime}");
                                Log::info("Remaining: {$newRemainingSeconds}");
                                $user->remaining_session_time = $newRemainingSeconds;
                                $user->save();

                                $device->status = 'vacant';
                                $device->pc_assignments_id = null;
                                $device->user_id = null;
                                $device->start_time = null;
                                $device->end_time = null;
                                $device->save();
                            }
                        }
                    });
    
                    return response()->json([
                        'success' => true,
                        'message' => 'Clocked out successfully.',
                    ]);
                }
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    public function scanQr(Request $request) {
        try {
            $request->validate([
                'id_number' => 'required|string'
            ]);
    
            $user = User::where('id_number', $request->id_number)->first();
    
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'msg' => 'User not registered'
                ], 404);
            }
    
            $lastScan = QrScans::where('user_id', $user->id)
                ->whereDate('clockIn', Carbon::today())
                ->latest()
                ->first();
    
            if ($lastScan) {
                if ($lastScan->updated_at->diffInSeconds(now()) < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please wait before scanning again to avoid duplicate logs.',
                    ]);
                }
    
                if ($lastScan->clockOut === null) {

                    DB::transaction(function() use ($lastScan, $user) {
                        $lastScan->clockOut = Carbon::now();
                        $lastScan->save();
    
                        $assignment = PcAssignments::where('user_id', $user->id)
                            ->whereNull('clockOut')
                            ->latest()
                            ->first();
    
                        if ($assignment) {
                            $assignment->clockOut = Carbon::now();
                            $assignment->save();

                            $device = PcDevices::where('pc_assignments_id', $assignment->id)->first();

                            if ($device) {
                                $endTime = now();
                                // $startTime = Carbon::createFromFormat('H:i:s', $device->start_time);
                                $startTime = Carbon::parse($endTime->toDateString() . ' ' . $device->start_time);
                                if ($startTime->gt($endTime)) {
                                    $startTime->subDay(); // handle overnight sessions
                                }

                                // calculate used seconds between start and end
                                // $usedSeconds = $endTime->diffInSeconds($startTime);
                                $usedSeconds = abs($endTime->diffInSeconds($startTime, false));

                                $user = $assignment->user;

                                $remainingSeconds = $user->remaining_session_time ?? 0;

                                $newRemainingSeconds = max(0, $remainingSeconds - $usedSeconds);

                                $maxSeconds = (23 * 3600) + (59 * 60) + 59;
                                $newRemainingSeconds = min($newRemainingSeconds, $maxSeconds);

                                Log::info("Used: {$usedSeconds}");
                                Log::info("End: {$endTime}");
                                Log::info("Remaining: {$newRemainingSeconds}");
                                $user->remaining_session_time = $newRemainingSeconds;
                                $user->save();

                                $device->status = 'vacant';
                                $device->pc_assignments_id = null;
                                $device->user_id = null;
                                $device->start_time = null;
                                $device->end_time = null;
                                $device->save();
                            }


                            // PcDevices::where('pc_assignments_id', $assignment->id)
                            //     ->update([
                            //         'status' => 'not used',
                            //         'pc_assignments_id' => null
                            //     ]);
                        }
                    });
    
                    return response()->json([
                        'success' => true,
                        'message' => 'Clocked out successfully.',
                    ]);
                } else {
                    $availablePc = PcDevices::where('status', 'vacant')->first();
    
                    if (!$availablePc) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No available PC right now. Please wait.'
                        ]);
                    }
    
                    $pcAssigned = DB::transaction(function() use ($user, $availablePc) {
                        $newScan = QrScans::create([
                            'user_id'  => $user->id,
                            'clockIn'  => Carbon::now(),
                            'clockOut' => null,
                            'pc_device_id' => $availablePc->id
                        ]);
    
                        $assignment = PcAssignments::create([
                            'user_id' => $user->id,
                            'code'    => uniqid('assign_'),
                            'clockIn' => Carbon::now(),
                            'clockOut' => null
                        ]);
    
                        $availablePc->update([
                            'status' => 'pre-occupied',
                            'pc_assignments_id' => $assignment->id,
                            'user_id' => $user->id
                        ]);

                        return PcDevices::where('user_id', $user->id)->first();

                    });
                    Log::info($pcAssigned);
                    return response()->json([
                        'success' => true,
                        'message' => 'Clocked in successfully and PC assigned.',
                        'assignedNumber' => $pcAssigned->device_name
                    ]);
                }
            } else {
                $availablePc = PcDevices::where('status', 'vacant')->first();
    
                if (!$availablePc) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No available PC right now. Please wait.'
                    ]);
                }

                $pcAssigned = DB::transaction(function() use ($user, $availablePc) {
                    $newScan = QrScans::create([
                        'user_id'  => $user->id,
                        'clockIn'  => Carbon::now(),
                        'clockOut' => null,
                        'pc_device_id' => $availablePc->id
                    ]);

                    $assignment = PcAssignments::create([
                        'user_id' => $user->id,
                        'code'    => uniqid('assign_'),
                        'clockIn' => Carbon::now(),
                        'clockOut' => null
                    ]);

                    $availablePc->update([
                        'status' => 'pre-occupied',
                        'pc_assignments_id' => $assignment->id,
                        'user_id' => $user->id
                    ]);

                    return PcDevices::where('user_id', $user->id)->first();

                });
                Log::info($pcAssigned);
                return response()->json([
                    'success' => true,
                    'message' => 'Clocked in successfully and PC assigned.',
                    'assignedNumber' => $pcAssigned->device_name
                ]);
                // $availablePc = PcDevices::where('status', 'vacant')->first();
    
                // if (!$availablePc) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'No available PC right now. Please wait.'
                //     ]);
                // }
    
                // DB::transaction(function() use ($user, $availablePc) {
                //     $newScan = QrScans::create([
                //         'user_id'  => $user->id,
                //         'clockIn'  => Carbon::now(),
                //         'clockOut' => null
                //     ]);
    
                //     $assignment = PcAssignments::create([
                //         'user_id' => $user->id,
                //         'code'    => uniqid('assign_'),
                //         'clockIn' => Carbon::now(),
                //         'clockOut' => null
                //     ]);
    
                //     $availablePc->update([
                //         'status' => 'occupied',
                //         'pc_assignments_id' => $assignment->id
                //     ]);
                // });
    
                // return response()->json([
                //     'success' => true,
                //     'message' => 'Clocked in successfully and PC assigned.'
                // ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    public function fetchAllHistoryScans($id = null){
        try {
            
            if($id === null){
                $history = QrScans::with(['user', 'pcDevice'])->get();
            }else{
                $history = QrScans::with(['user', 'pcDevice'])->where('user_id', $id)->get();
            }

            if($history->isEmpty()){
                return response([
                    'success' => false,
                    'message' => 'No data available'
                ], 404);
            };

            return response([
                'success' => true,
                'history' => $history,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => $th->getMessage(),
            ], 200);
        }
    }
}
