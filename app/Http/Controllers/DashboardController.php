<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MotionDetection;
use App\Models\AlarmLog;
use App\Models\CapturedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $devices = Device::with(['motionDetections' => function ($query) {
            $query->latest()->limit(5);
        }])->get();

        $recentMotions = MotionDetection::with('device')
            ->latest('detected_at')
            ->limit(10)
            ->get();

        $recentAlarms = AlarmLog::with('device')
            ->latest('activated_at')
            ->limit(10)
            ->get();

        $recentImages = CapturedImage::with('device')
            ->latest('captured_at')
            ->limit(12)
            ->get();

        $unreadAlerts = MotionDetection::where('is_read', false)->count();

        return view('dashboard', compact(
            'devices',
            'recentMotions',
            'recentAlarms',
            'recentImages',
            'unreadAlerts'
        ));
    }

    public function motionHistory()
    {
        $motions = MotionDetection::with('device')
            ->orderBy('detected_at', 'desc')
            ->paginate(20);

        return view('motion-history', compact('motions'));
    }

    public function alarmHistory()
    {
        $alarms = AlarmLog::with('device')
            ->orderBy('activated_at', 'desc')
            ->paginate(20);

        return view('alarm-history', compact('alarms'));
    }

    public function imageGallery()
    {
        $images = CapturedImage::with('device')
            ->orderBy('captured_at', 'desc')
            ->paginate(24);

        return view('image-gallery', compact('images'));
    }

    public function liveCamera()
    {
        $devices = Device::where('status', 'online')->get();

        // Add stream URLs to devices
        $devices = $devices->map(function ($device) {
            $device->stream_url = $device->ip_address ? "http://{$device->ip_address}/stream" : null;
            $device->web_interface = $device->ip_address ? "http://{$device->ip_address}/" : null;
            return $device;
        });

        return view('live-camera', compact('devices'));
    }

    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'Web route is working!',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'server_ip' => request()->ip(),
            'url' => request()->fullUrl()
        ]);
    }

    public function markAsRead($id)
    {
        try {
            $motion = MotionDetection::findOrFail($id);
            $motion->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Motion marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMotion($id)
    {
        try {
            $motion = MotionDetection::findOrFail($id);
            $motion->delete();

            return redirect()->back()->with('success', 'Motion record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete motion record');
        }
    }

    public function deleteAlarm($id)
    {
        try {
            $alarm = AlarmLog::findOrFail($id);
            $alarm->delete();

            return redirect()->back()->with('success', 'Alarm record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete alarm record');
        }
    }

    public function deleteImage($id)
    {
        try {
            $image = CapturedImage::findOrFail($id);

            // Delete physical file
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();

            return redirect()->back()->with('success', 'Image deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete image');
        }
    }
}
