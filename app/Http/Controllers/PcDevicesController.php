<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PcDevices;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PcDevicesController extends Controller
{
    public function fetchDevices($id = null){
        try {
            if($id === null){
                $devices = PcDevices::all();
            }else{
                $devices = PcDevices::findOrFail($id);
            }

            return response()->json([
                'success' => true,
                'data'    => $devices
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function resetDevice($id) {
        try {
            $device = PcDevices::findOrFail($id);

            $device->update([
                'status'            => 'vacant',
                'pc_assignments_id' => null,
                'user_id'           => null,
                'start_time'        => null,
                'end_time'          => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$device->device_name} ID#{$id} has been reset.",
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function resetAllDevices() {
        try {
            PcDevices::query()->update([
                'status'            => 'vacant',
                'pc_assignments_id' => null,
                'user_id'           => null,
                'start_time'        => null,
                'end_time'          => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All devices have been reset.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function editMacAddress(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'macAddress' => 'required'
            ]);

            if($validator->fails()){
                return response([
                    'msg' => 'Mac address is required'
                ], 400);
            }

            $macAddress = $request->macAddress;
            return DB::transaction(function () use ($macAddress) {
                $existingDevice = PcDevices::where('mac_address', $macAddress)->first();
                if ($existingDevice) {
                    return response([
                        'msg' => 'Already registered',
                    ], 201);
                }
                $device = PcDevices::whereNull('mac_address')
                    ->lockForUpdate()
                    ->first();

                if(!$device){
                    return response([
                        'msg' => 'No Available PC to be edit'
                    ], 404);
                }

                $device->mac_address = $macAddress;
                $device->save();

                return response([
                    'msg' => 'Updated Successfully'
                ], 200);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return response([
                'msg' => 'Duplicate MAC detected'
            ], 409);
        }
    }

    public function checkDevice(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'macAddress' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response([
                    'msg' => 'MAC address is required'
                ], 400);
            }

            $macAddress = $request->macAddress;
            $device = PcDevices::where('mac_address', $macAddress)->first();

            if ($device) {
                return response([
                    'exists' => true,
                    'msg' => 'Device exists',
                ], 200);
            }

            return response([
                'exists' => false,
                'msg' => 'Device not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
