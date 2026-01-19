<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\PcDevices;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;

class UserController extends Controller
{
    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name'     => 'required|string|max:255',
                'course_year'  => 'string|max:50',
                'id_number'    => 'required|string|max:20|unique:users,id_number',
                'email'        => 'required|string|email|max:255|unique:users',
                'password'     => 'required|string|min:6',
                'account_type'      => 'required'
            ]);

            $roleMap = [
                'Student' => 1,
                'Faculty' => 2,
                'Staff'   => 3,
            ];

            $role_id = $roleMap[$request->account_type] ?? null;

            if (!$role_id) {
                return response()->json(['message' => 'Invalid account type.'], 400);
            }

            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $remainingSessionTime = null;
            if($role_id == 1){
                $remainingSessionTime = 86400;
            }
            $user = User::create([
                'fullName'     => $request->name,
                'course_year'  => $request->course_year,
                'id_number'    => $request->id_number,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'role_id'      => $role_id,
                'remaining_session_time' => $remainingSessionTime
            ]);
        
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->isDesktopApp) {

                $user = $this->loginFormApi($request->email, $request->password);

                if (!$user) {
                    return response()->json(['message' => 'Invalid credentials.'], 401);
                }

                if ($user->remaining_session_time == 0) {
                    return response()->json(['message' => 'You already consume all your 24hours.'], 403);
                }

                $token = $user->createToken('api-token')->plainTextToken;

                $pcDevice = PcDevices::where('user_id', $user->id)->first();

                if(!$pcDevice){
                    return response() -> json([
                        'message' => 'PC not currently assigned. Please clock in at the entrance.'], 403);
                }

                $isPcAssignedCorrectly = $pcDevice->user_id === $user->id && $pcDevice->mac_address === $request->macAddress;
                if($pcDevice->status === "pre-occupied" && $isPcAssignedCorrectly){
                    
                    $pcDevice->status = 'occupied';
                    $pcDevice->start_time = now()->format('H:i:s');
                    $pcDevice->save();

                    return response()->json([
                        'message' => 'Login successful on desktop',
                        'token' => $token,
                        'user' => $user,
                    ]);
                } else {
                    return response() -> json([
                        'message' => 'PC not assigned or incorrect device.'], 403);
                }
            
            } else {

                $user = $this->loginFormApi($request->email, $request->password);

                if (!$user) {
                    return response()->json(['message' => 'Invalid credentials.'], 401);
                }
                
                $token = $user->createToken('api-token')->plainTextToken;
            
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user,
                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchAccountDetails($id = null){
        try {
            if($id === null){
                $user = User::with('latestPcAssignment.pcDevices')->get();
                $isEmpty = $user->isEmpty();
            }else{
                $user = User::with('latestPcAssignment.pcDevices')->find($id);
                $isEmpty = !$user;
            }

            if($isEmpty){
                return response([
                    'success' => false,
                    'message' => 'No data available'
                ], 404);
            };

            return response([
                'success' => true,
                'user' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'success' => false,
                'message' => $th->getMessage(),
            ], 200);
        }
    }

    private function loginFormApi($email, $password){
        $user = User::where('email', $email)->first();
        
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
