<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\MotionDetection;
use App\Models\AlarmLog;
use App\Models\CapturedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SecurityController extends Controller
{
    public function testConnection(Request $request)
    {
        try {
            $data = [
                'success' => true,
                'message' => 'API connection successful!',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'server_ip' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'received_data' => $request->all(),
                'database_status' => $this->testDatabase()
            ];

            // Log untuk debug
            Log::info('ESP32 Test Connection', $data);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Test Connection Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function testDatabase()
    {
        try {
            $deviceCount = Device::count();
            return [
                'connected' => true,
                'device_count' => $deviceCount,
                'tables_exist' => true
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function pirDetections(Request $request)
    {
        try {
            Log::info('PIR Detection Request', [
                'method' => $request->method(),
                'data' => $request->all(),
                'ip' => $request->ip()
            ]);

            if ($request->method() === 'GET') {
                // Return recent detections
                $detections = MotionDetection::with('device')
                    ->orderBy('detected_at', 'desc')
                    ->limit(50)
                    ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'PIR detections retrieved successfully',
                    'count' => $detections->count(),
                    'data' => $detections
                ]);
            }

            if ($request->method() === 'POST') {
                // Handle PIR detection data from ESP32
                $validated = $request->validate([
                    'device_id' => 'required|integer',
                    'timestamp' => 'required|string',
                    'alert_type' => 'nullable|string',
                    'message' => 'nullable|string'
                ]);

                // Update or create device
                $device = Device::updateOrCreate(
                    ['device_id' => $validated['device_id']],
                    [
                        'device_name' => 'Security Device ' . $validated['device_id'],
                        'location' => 'Location ' . $validated['device_id'],
                        'status' => 'online',
                        'ip_address' => $request->ip(),
                        'last_seen' => now()
                    ]
                );

                // Create motion detection record
                $motion = MotionDetection::create([
                    'device_id' => $validated['device_id'],
                    'detected_at' => Carbon::parse($validated['timestamp']),
                    'alert_type' => $validated['alert_type'] ?? 'motion_detected',
                    'message' => $validated['message'] ?? 'Motion detected'
                ]);

                Log::info('Motion detection created', ['motion_id' => $motion->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'PIR detection recorded successfully',
                    'data' => $motion,
                    'device' => $device
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PIR Detection Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing PIR detection: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function motionAlert(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'timestamp' => 'required|string',
                'alert_type' => 'nullable|string',
                'message' => 'nullable|string'
            ]);

            // Update device status
            $device = Device::updateOrCreate(
                ['device_id' => $validated['device_id']],
                [
                    'device_name' => 'Security Device ' . $validated['device_id'],
                    'location' => 'Location ' . $validated['device_id'],
                    'status' => 'online',
                    'last_seen' => now(),
                    'ip_address' => $request->ip()
                ]
            );

            // Create motion detection record
            $motion = MotionDetection::create([
                'device_id' => $validated['device_id'],
                'detected_at' => Carbon::parse($validated['timestamp']),
                'alert_type' => $validated['alert_type'] ?? 'motion_detected',
                'message' => $validated['message'] ?? 'Motion detected'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Motion alert recorded',
                'data' => $motion
            ]);
        } catch (\Exception $e) {
            Log::error('Motion Alert Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error recording motion alert: ' . $e->getMessage()
            ], 500);
        }
    }

    public function alarmStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'timestamp' => 'required|string',
                'status' => 'required|in:activated,deactivated',
                'message' => 'nullable|string'
            ]);

            $alarmLog = AlarmLog::create([
                'device_id' => $validated['device_id'],
                'activated_at' => Carbon::parse($validated['timestamp']),
                'status' => $validated['status'],
                'message' => $validated['message'] ?? ''
            ]);

            if ($validated['status'] === 'deactivated') {
                // Update the last activated alarm log
                AlarmLog::where('device_id', $validated['device_id'])
                    ->where('status', 'activated')
                    ->whereNull('deactivated_at')
                    ->update([
                        'deactivated_at' => Carbon::parse($validated['timestamp']),
                        'status' => 'deactivated'
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Alarm status recorded',
                'data' => $alarmLog
            ]);
        } catch (\Exception $e) {
            Log::error('Alarm Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error recording alarm status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'timestamp' => 'required|string',
                'image' => 'required|file|mimes:jpeg,jpg,png|max:5120' // 5MB max
            ]);

            // Store image
            $imagePath = $request->file('image')->store('captures', 'public');

            // Create image record
            $capturedImage = CapturedImage::create([
                'device_id' => $validated['device_id'],
                'image_path' => $imagePath,
                'captured_at' => Carbon::parse($validated['timestamp']),
                'description' => 'Auto captured on motion detection'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'data' => $capturedImage,
                'image_url' => asset('storage/' . $imagePath)
            ]);
        } catch (\Exception $e) {
            Log::error('Photo Upload Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error uploading photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deviceStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'timestamp' => 'required|string',
                'status' => 'required|in:online,offline',
                'ip_address' => 'nullable|string',
                'camera_available' => 'nullable|boolean',
                'free_heap' => 'nullable|integer'
            ]);

            $device = Device::updateOrCreate(
                ['device_id' => $validated['device_id']],
                [
                    'device_name' => 'Security Device ' . $validated['device_id'],
                    'location' => 'Location ' . $validated['device_id'],
                    'status' => $validated['status'],
                    'ip_address' => $validated['ip_address'] ?? $request->ip(),
                    'last_seen' => Carbon::parse($validated['timestamp'])
                ]
            );

            // Log device info untuk debug
            Log::info('Device Status Update', [
                'device_id' => $validated['device_id'],
                'status' => $validated['status'],
                'ip_address' => $validated['ip_address'],
                'camera_available' => $validated['camera_available'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device status updated',
                'data' => $device
            ]);
        } catch (\Exception $e) {
            Log::error('Device Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating device status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDashboardStats()
    {
        try {
            $stats = [
                'active_devices' => Device::where('status', 'online')->count(),
                'today_alerts' => MotionDetection::whereDate('detected_at', today())->count(),
                'unread_alerts' => MotionDetection::where('is_read', false)->count(),
                'images_captured' => CapturedImage::whereDate('captured_at', today())->count(),
                'total_devices' => Device::count(),
                'total_detections' => MotionDetection::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting dashboard stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCameraStreams()
    {
        try {
            $devices = Device::where('status', 'online')->get();

            $streams = $devices->map(function ($device) {
                return [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'location' => $device->location,
                    'stream_url' => $device->ip_address ? "http://{$device->ip_address}/stream" : null,
                    'web_interface' => $device->ip_address ? "http://{$device->ip_address}/" : null,
                    'status' => $device->status,
                    'last_seen' => $device->last_seen
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $streams
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting camera streams: ' . $e->getMessage()
            ], 500);
        }
    }
}
