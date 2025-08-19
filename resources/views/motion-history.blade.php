@extends('layouts.app')

@section('title', 'Motion History')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-dark">
                        <i class="fas fa-running me-2"></i>
                        Motion Detection History
                    </h1>
                    <p class="text-muted">View all motion detection events and alerts</p>
                </div>
                <div>
                    <button class="btn btn-success me-2" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-1"></i>
                        Mark All Read
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?filter=today">Today</a></li>
                            <li><a class="dropdown-item" href="?filter=week">This Week</a></li>
                            <li><a class="dropdown-item" href="?filter=unread">Unread Only</a></li>
                            <li><a class="dropdown-item" href="{{ route('motion.history') }}">All</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motion History Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($motions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Device</th>
                                    <th>Alert Type</th>
                                    <th>Message</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($motions as $motion)
                                    <tr class="{{ !$motion->is_read ? 'table-warning' : '' }}"
                                        data-motion-id="{{ $motion->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-video text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ $motion->device->device_name ?? 'Unknown Device' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $motion->device->location ?? 'Unknown Location' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $motion->alert_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $motion->message ?: 'Motion detected' }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $motion->detected_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $motion->detected_at->format('H:i:s') }}</small>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $motion->detected_at->diffForHumans() }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($motion->is_read)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    Read
                                                </span>
                                            @else
                                                <span class="badge bg-danger unread-badge">
                                                    <i class="fas fa-circle me-1"></i>
                                                    Unread
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if (!$motion->is_read)
                                                    <button class="btn btn-sm btn-outline-success"
                                                        onclick="markAsRead({{ $motion->id }})" title="Mark as Read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete('motion', {{ $motion->id }}, '{{ $motion->device->device_name ?? 'motion record' }}')"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white">
                        {{ $motions->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No Motion History</h4>
                        <p class="text-muted">No motion detection events found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function markAllAsRead() {
                if (confirm('Mark all motion alerts as read?')) {
                    axios.post('/motion/mark-all-read')
                        .then(response => {
                            location.reload();
                        })
                        .catch(error => {
                            showAlert('danger', 'Failed to mark all as read');
                        });
                }
            }
        </script>
    @endpush
@endsection
