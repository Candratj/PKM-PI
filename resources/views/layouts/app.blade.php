<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Security System Dashboard')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark-color: #2c3e50;
        }

        body {
            background-color: #ecf0f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .device-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .status-online {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
        }

        .status-offline {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
        }

        .alert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
        }

        .live-camera-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .camera-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .camera-feed {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--secondary-color);
        }

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .notification-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            position: absolute;
            top: -8px;
            right: -8px;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-3 text-center border-bottom border-light">
            <h4 class="text-white mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                Security System
            </h4>
        </div>

        <ul class="nav nav-pills flex-column p-3">
            <li class="nav-item mb-2">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('live.camera') }}"
                    class="nav-link {{ request()->routeIs('live.camera') ? 'active' : '' }}">
                    <i class="fas fa-video me-2"></i>
                    Live Camera
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('motion.history') }}"
                    class="nav-link {{ request()->routeIs('motion.history') ? 'active' : '' }}">
                    <i class="fas fa-running me-2"></i>
                    Motion History
                    @if ($unreadAlerts ?? 0 > 0)
                        <span class="notification-badge">{{ $unreadAlerts }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('alarm.history') }}"
                    class="nav-link {{ request()->routeIs('alarm.history') ? 'active' : '' }}">
                    <i class="fas fa-bell me-2"></i>
                    Alarm History
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('image.gallery') }}"
                    class="nav-link {{ request()->routeIs('image.gallery') ? 'active' : '' }}">
                    <i class="fas fa-images me-2"></i>
                    Image Gallery
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Alert Notifications -->
    <div id="alertContainer" class="alert-notification"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        // CSRF Token Setup
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute(
            'content');

        // Pusher Setup untuk Real-time Notifications
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const channel = pusher.subscribe('security-system');

        // Motion Detection Alert
        channel.bind('motion.detected', function(data) {
            showAlert('danger', `
                <strong>Motion Detected!</strong><br>
                Device: ${data.device_name}<br>
                Time: ${data.detected_at}<br>
                ${data.message}
            `);

            // Update counters if on dashboard
            if (typeof updateDashboardCounters !== 'undefined') {
                updateDashboardCounters();
            }

            // Play alert sound
            playAlertSound();
        });

        // Alarm Triggered Alert
        channel.bind('alarm.triggered', function(data) {
            const alertType = data.status === 'activated' ? 'warning' : 'info';
            const message = data.status === 'activated' ?
                'Alarm Activated!' : 'Alarm Deactivated';

            showAlert(alertType, `
                <strong>${message}</strong><br>
                Device: ${data.device_name}<br>
                Time: ${data.activated_at}
            `);
        });

        // Device Status Change
        channel.bind('device.status.changed', function(data) {
            updateDeviceStatus(data.device_id, data.status);
        });

        function showAlert(type, message) {
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            const container = document.getElementById('alertContainer');
            container.innerHTML = alertHTML;

            // Auto-hide after 10 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 10000);
        }

        function playAlertSound() {
            // Create audio element and play alert sound
            const audio = new Audio(
                'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+L7w2QYBz2GjOXsklIIDFqm+uyxazwJOWLb/sp5OgMY'
                );
            audio.play().catch(e => console.log('Audio play failed'));
        }

        function updateDeviceStatus(deviceId, status) {
            const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (deviceCard) {
                const statusBadge = deviceCard.querySelector('.device-status');
                if (statusBadge) {
                    statusBadge.className = `badge ${status === 'online' ? 'bg-success' : 'bg-danger'} device-status`;
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                }
            }
        }

        // Mark notification as read
        function markAsRead(motionId) {
            axios.post(`/motion/${motionId}/read`)
                .then(response => {
                    const element = document.querySelector(`[data-motion-id="${motionId}"]`);
                    if (element) {
                        element.classList.remove('table-warning');
                        const badge = element.querySelector('.unread-badge');
                        if (badge) badge.remove();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Delete confirmation
        function confirmDelete(type, id, name) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/${type}/${id}`;
                form.innerHTML = `
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
