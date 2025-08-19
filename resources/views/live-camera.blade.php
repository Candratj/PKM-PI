@extends('layouts.app')

@section('title', 'Live Camera Feed')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-dark">
                        <i class="fas fa-video me-2"></i>
                        Live Camera Feed
                    </h1>
                    <p class="text-muted">Monitor all devices in real-time</p>
                </div>
                <div>
                    <button class="btn btn-success me-2" onclick="refreshAllStreams()">
                        <i class="fas fa-sync me-1"></i>
                        Refresh All
                    </button>
                    <button class="btn btn-primary" onclick="toggleFullscreen()">
                        <i class="fas fa-expand me-1"></i>
                        Fullscreen
                    </button>
                </div>
            </div>
        </div>

        @if ($devices->count() > 0)
            <!-- Camera Grid -->
            <div class="row">
                @foreach ($devices as $device)
                    <div class="col-lg-6 col-xl-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-video me-2 text-primary"></i>
                                        {{ $device->device_name }}
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $device->location }} • {{ $device->ip_address }}
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-circle"></i> Online
                                    </span>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-success"
                                            onclick="startStream({{ $device->device_id }})" title="Start Stream">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="stopStream({{ $device->device_id }})" title="Stop Stream">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="capturePhoto({{ $device->device_id }})" title="Capture Photo">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                        @if ($device->web_interface)
                                            <a href="{{ $device->web_interface }}" target="_blank"
                                                class="btn btn-sm btn-outline-info" title="Open Web Interface">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="camera-feed-container"
                                    style="position: relative; height: 400px; background: #f8f9fa;">
                                    <!-- Live Stream Image -->
                                    <img id="stream-{{ $device->device_id }}" src="" alt="Camera Stream"
                                        style="display: none; width: 100%; height: 100%; object-fit: contain;"
                                        onerror="handleStreamError({{ $device->device_id }})"
                                        onload="handleStreamLoad({{ $device->device_id }})">

                                    <!-- Placeholder/Status -->
                                    <div id="placeholder-{{ $device->device_id }}"
                                        class="d-flex align-items-center justify-content-center h-100">
                                        <div class="text-center">
                                            <i class="fas fa-video fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">Live Camera Stream</h5>
                                            <p class="text-muted mb-3">Click "Start Stream" to begin monitoring</p>
                                            @if ($device->stream_url)
                                                <button class="btn btn-primary"
                                                    onclick="startStream({{ $device->device_id }})">
                                                    <i class="fas fa-play me-2"></i>Start Stream
                                                </button>
                                            @else
                                                <p class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Stream URL not available
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Stream Status Overlay -->
                                    <div id="status-{{ $device->device_id }}" class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-secondary">Disconnected</span>
                                    </div>

                                    <!-- Loading Overlay -->
                                    <div id="loading-{{ $device->device_id }}"
                                        class="position-absolute top-50 start-50 translate-middle" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-white">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Device ID</small>
                                        <strong>{{ $device->device_id }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Last Seen</small>
                                        <strong>{{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Unknown' }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Stream Status</small>
                                        <span id="stream-status-{{ $device->device_id }}" class="badge bg-secondary">
                                            Stopped
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Devices Available -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-video-slash fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">No Online Devices</h4>
                            <p class="text-muted mb-4">
                                No security devices are currently online for live monitoring.
                                <br>
                                Please ensure your ESP32 devices are powered on and connected to the network.
                            </p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync me-2"></i>Refresh Page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Fullscreen Modal -->
    <div class="modal fade" id="fullscreenModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Live Camera - Full Screen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-2">
                        @foreach ($devices as $device)
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <small>{{ $device->device_name }} - {{ $device->location }}</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <img id="fullscreen-stream-{{ $device->device_id }}" src=""
                                            alt="Full Screen Stream"
                                            style="width: 100%; height: 300px; object-fit: contain; background: #000;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const activeStreams = new Map();
            const streamIntervals = new Map();
            const streamUrls = new Map();

            // Initialize stream URLs
            @foreach ($devices as $device)
                @if ($device->stream_url)
                    streamUrls.set({{ $device->device_id }}, '{{ $device->stream_url }}');
                @endif
            @endforeach

            function startStream(deviceId) {
                console.log('Starting stream for device:', deviceId);

                if (activeStreams.has(deviceId)) {
                    console.log('Stream already active for device:', deviceId);
                    return;
                }

                if (!streamUrls.has(deviceId)) {
                    showAlert('danger', `Stream URL not available for Device ${deviceId}`);
                    return;
                }

                const streamUrl = streamUrls.get(deviceId);
                const imageElement = document.getElementById(`stream-${deviceId}`);
                const placeholderElement = document.getElementById(`placeholder-${deviceId}`);
                const statusElement = document.getElementById(`status-${deviceId}`);
                const streamStatusElement = document.getElementById(`stream-status-${deviceId}`);
                const loadingElement = document.getElementById(`loading-${deviceId}`);

                // Show loading
                loadingElement.style.display = 'block';
                updateStreamStatus(deviceId, 'Connecting...', 'bg-warning');

                // Hide placeholder and show stream
                placeholderElement.style.display = 'none';
                imageElement.style.display = 'block';

                // Start streaming with timestamp to prevent caching
                const interval = setInterval(() => {
                    if (activeStreams.has(deviceId)) {
                        const timestamp = new Date().getTime();
                        imageElement.src = `${streamUrl}?t=${timestamp}`;
                    } else {
                        clearInterval(interval);
                    }
                }, 1000); // Refresh every second

                activeStreams.set(deviceId, true);
                streamIntervals.set(deviceId, interval);

                console.log('Stream started for device:', deviceId);
            }

            function stopStream(deviceId) {
                console.log('Stopping stream for device:', deviceId);

                const imageElement = document.getElementById(`stream-${deviceId}`);
                const placeholderElement = document.getElementById(`placeholder-${deviceId}`);
                const statusElement = document.getElementById(`status-${deviceId}`);
                const loadingElement = document.getElementById(`loading-${deviceId}`);

                // Clear interval
                if (streamIntervals.has(deviceId)) {
                    clearInterval(streamIntervals.get(deviceId));
                    streamIntervals.delete(deviceId);
                }

                // Remove from active streams
                activeStreams.delete(deviceId);

                // Reset UI
                imageElement.style.display = 'none';
                imageElement.src = '';
                placeholderElement.style.display = 'flex';
                loadingElement.style.display = 'none';

                updateStreamStatus(deviceId, 'Stopped', 'bg-secondary');

                console.log('Stream stopped for device:', deviceId);
            }

            function handleStreamLoad(deviceId) {
                console.log('Stream loaded for device:', deviceId);

                const loadingElement = document.getElementById(`loading-${deviceId}`);
                loadingElement.style.display = 'none';

                updateStreamStatus(deviceId, 'Live', 'bg-success');
            }

            function handleStreamError(deviceId) {
                console.error('Stream error for device:', deviceId);

                const loadingElement = document.getElementById(`loading-${deviceId}`);
                loadingElement.style.display = 'none';

                updateStreamStatus(deviceId, 'Connection Error', 'bg-danger');

                // Try to reconnect after 5 seconds
                setTimeout(() => {
                    if (activeStreams.has(deviceId)) {
                        console.log('Attempting to reconnect stream for device:', deviceId);
                        const imageElement = document.getElementById(`stream-${deviceId}`);
                        const timestamp = new Date().getTime();
                        const streamUrl = streamUrls.get(deviceId);
                        if (streamUrl) {
                            imageElement.src = `${streamUrl}?t=${timestamp}`;
                        }
                    }
                }, 5000);
            }

            function updateStreamStatus(deviceId, status, badgeClass) {
                const statusElement = document.getElementById(`status-${deviceId}`);
                const streamStatusElement = document.getElementById(`stream-status-${deviceId}`);

                if (statusElement) {
                    statusElement.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
                }

                if (streamStatusElement) {
                    streamStatusElement.className = `badge ${badgeClass}`;
                    streamStatusElement.textContent = status;
                }
            }

            function capturePhoto(deviceId) {
                console.log('Capturing photo for device:', deviceId);

                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;

                button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
                button.disabled = true;

                // Send capture command to ESP32
                if (streamUrls.has(deviceId)) {
                    const baseUrl = streamUrls.get(deviceId).replace('/stream', '');
                    fetch(`${baseUrl}/capture`)
                        .then(response => response.text())
                        .then(data => {
                            showAlert('success', `Photo captured from Device ${deviceId}!`);
                            console.log('Capture response:', data);
                        })
                        .catch(error => {
                            console.error('Capture error:', error);
                            showAlert('danger', `Failed to capture photo from Device ${deviceId}`);
                        })
                        .finally(() => {
                            button.innerHTML = originalHTML;
                            button.disabled = false;
                        });
                } else {
                    showAlert('danger', `Cannot capture photo - Device ${deviceId} not available`);
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }
            }

            function refreshAllStreams() {
                console.log('Refreshing all streams...');

                // Stop all active streams
                activeStreams.forEach((value, deviceId) => {
                    stopStream(deviceId);
                });

                // Wait a moment, then restart
                setTimeout(() => {
                    streamUrls.forEach((url, deviceId) => {
                        startStream(deviceId);
                    });
                }, 1000);

                showAlert('info', 'All streams refreshed!');
            }

            function toggleFullscreen() {
                const modal = new bootstrap.Modal(document.getElementById('fullscreenModal'));

                // Copy current streams to fullscreen modal
                activeStreams.forEach((value, deviceId) => {
                    const currentSrc = document.getElementById(`stream-${deviceId}`).src;
                    const fullscreenImg = document.getElementById(`fullscreen-stream-${deviceId}`);
                    if (fullscreenImg && currentSrc) {
                        fullscreenImg.src = currentSrc;
                    }
                });

                modal.show();
            }

            function showAlert(type, message) {
                const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                document.body.insertAdjacentHTML('beforeend', alertHTML);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        if (alert.textContent.includes(message.substring(0, 20))) {
                            alert.remove();
                        }
                    });
                }, 5000);
            }

            // Auto-refresh stream URLs every 30 seconds
            setInterval(() => {
                fetch('/api/camera-streams')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            data.data.forEach(device => {
                                if (device.stream_url) {
                                    streamUrls.set(device.device_id, device.stream_url);
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Failed to refresh stream URLs:', error));
            }, 30000);

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                activeStreams.forEach((value, deviceId) => {
                    stopStream(deviceId);
                });
            });

            // Auto-start streams for debugging (remove in production)
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Page loaded, available stream URLs:', Array.from(streamUrls.entries()));
            });
        </script>
    @endpush
@endsection
