@extends('layouts.app')

@section('title', 'Security System Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 text-dark">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Security Dashboard
                </h1>
                <p class="text-muted">Real-time monitoring of your security devices</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Devices</h6>
                            <h3 class="mb-0 text-success" id="activeDevicesCount">
                                {{ $devices->where('status', 'online')->count() }}
                            </h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-wifi fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Alerts</h6>
                            <h3 class="mb-0 text-warning" id="todayAlertsCount">
                                {{ $recentMotions->where('detected_at', '>=', today())->count() }}
                            </h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Unread Alerts</h6>
                            <h3 class="mb-0 text-danger" id="unreadAlertsCount">
                                {{ $unreadAlerts }}
                            </h3>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Images Captured</h6>
                            <h3 class="mb-0 text-info" id="imagesCapturedCount">
                                {{ $recentImages->where('captured_at', '>=', today())->count() }}
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-camera fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Status Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">Device Status</h4>
            </div>

            @foreach ($devices as $device)
                <div class="col-xl-3 col-lg-6 mb-3">
                    <div class="device-card card" data-device-id="{{ $device->device_id }}">
                        <div
                            class="card-header {{ $device->status === 'online' ? 'status-online' : 'status-offline' }} text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-video me-2"></i>
                                    {{ $device->device_name }}
                                </h6>
                                <span
                                    class="badge {{ $device->status === 'online' ? 'bg-light text-success' : 'bg-light text-danger' }} device-status">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $device->location }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-network-wired me-1"></i>
                                {{ $device->ip_address ?: 'Unknown IP' }}
                            </p>
                            <p class="text-muted mb-3">
                                <i class="fas fa-clock me-1"></i>
                                Last seen: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}
                            </p>

                            <div class="d-flex justify-content-between">
                                <small class="text-muted">
                                    Alerts today:
                                    {{ $device->motionDetections->where('detected_at', '>=', today())->count() }}
                                </small>
                                @if ($device->status === 'online')
                                    <span class="text-success pulse">
                                        <i class="fas fa-circle"></i> Live
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <!-- Recent Motion Detections -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-running text-warning me-2"></i>
                            Recent Motion Detections
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentMotions->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($recentMotions->take(5) as $motion)
                                    <div class="list-group-item {{ !$motion->is_read ? 'table-warning' : '' }}"
                                        data-motion-id="{{ $motion->id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    {{ $motion->device->device_name }}
                                                    @if (!$motion->is_read)
                                                        <span class="badge bg-danger unread-badge">New</span>
                                                    @endif
                                                </h6>
                                                <p class="mb-1 text-muted">{{ $motion->message }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $motion->detected_at->format('M d, Y H:i:s') }}
                                                </small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if (!$motion->is_read)
                                                        <li>
                                                            <a class="dropdown-item" href="#"
                                                                onclick="markAsRead({{ $motion->id }})">
                                                                <i class="fas fa-check me-2"></i>Mark as Read
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#"
                                                            onclick="confirmDelete('motion', {{ $motion->id }}, '{{ $motion->device->device_name }}')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent motion detections</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white">
                        <a href="{{ route('motion.history') }}" class="btn btn-outline-primary btn-sm">
                            View All Motion History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Images -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-camera text-info me-2"></i>
                            Recent Captured Images
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentImages->count() > 0)
                            <div class="row g-2 p-3">
                                @foreach ($recentImages->take(6) as $image)
                                    <div class="col-4">
                                        <div class="gallery-item">
                                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Captured Image"
                                                class="img-fluid" style="height: 120px; width: 100%; object-fit: cover;">
                                            <div class="p-2">
                                                <small
                                                    class="text-muted d-block">{{ $image->device->device_name }}</small>
                                                <small
                                                    class="text-muted">{{ $image->captured_at->format('M d H:i') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent images</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white">
                        <a href="{{ route('image.gallery') }}" class="btn btn-outline-info btn-sm">
                            View Image Gallery
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateDashboardCounters() {
                // Refresh dashboard counters via AJAX
                axios.get('/api/dashboard-stats')
                    .then(response => {
                        const data = response.data;
                        document.getElementById('activeDevicesCount').textContent = data.active_devices;
                        document.getElementById('todayAlertsCount').textContent = data.today_alerts;
                        document.getElementById('unreadAlertsCount').textContent = data.unread_alerts;
                        document.getElementById('imagesCapturedCount').textContent = data.images_captured;
                    })
                    .catch(error => console.error('Error updating counters:', error));
            }

            // Auto-refresh counters every 30 seconds
            setInterval(updateDashboardCounters, 30000);
        </script>
    @endpush
@endsection
