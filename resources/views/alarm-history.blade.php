@extends('layouts.app')

@section('title', 'Alarm History')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 text-dark">
                    <i class="fas fa-bell me-2"></i>
                    Alarm History
                </h1>
                <p class="text-muted">View all alarm activation and deactivation events</p>
            </div>
        </div>

        <!-- Alarm History Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($alarms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Device</th>
                                    <th>Status</th>
                                    <th>Activated</th>
                                    <th>Deactivated</th>
                                    <th>Duration</th>
                                    <th>Message</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alarms as $alarm)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-video text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ $alarm->device->device_name ?? 'Unknown Device' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $alarm->device->location ?? 'Unknown Location' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($alarm->status === 'activated')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    Activated
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Deactivated
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $alarm->activated_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $alarm->activated_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($alarm->deactivated_at)
                                                <div>
                                                    <strong>{{ $alarm->deactivated_at->format('M d, Y') }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $alarm->deactivated_at->format('H:i:s') }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($alarm->deactivated_at)
                                                <span class="badge bg-info">
                                                    {{ $alarm->activated_at->diffInSeconds($alarm->deactivated_at) }}s
                                                </span>
                                            @else
                                                <span class="badge bg-warning">Active</span>
                                            @endif
                                        </td>
                                        <td>{{ $alarm->message ?: 'Alarm triggered' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete('alarm', {{ $alarm->id }}, '{{ $alarm->device->device_name ?? 'alarm record' }}')"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white">
                        {{ $alarms->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No Alarm History</h4>
                        <p class="text-muted">No alarm events found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
