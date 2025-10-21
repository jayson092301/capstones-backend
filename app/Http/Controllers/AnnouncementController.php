<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function createAnnouncement(Request $request) {
        $validator = Validator::make($request->all(),[
            'announcement' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement = Announcement::create([
            'announcement' => $request->announcement
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Announcement posted'
        ]);
    }

    public function fetchAnnouncement() {
        $latest = Announcement::latest('created_at')->first();
    
        if (!$latest) {
            return response()->json([
                'success' => false,
                'message' => 'No announcements found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'announcement' => $latest
        ]);
    }
}
