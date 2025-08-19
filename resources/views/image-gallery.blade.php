@extends('layouts.app')

@section('title', 'Image Gallery')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-dark">
                        <i class="fas fa-images me-2"></i>
                        Captured Images Gallery
                    </h1>
                    <p class="text-muted">View all images captured by security devices</p>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i>
                        Filter by Device
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('image.gallery') }}">All Devices</a></li>
                        @foreach (\App\Models\Device::all() as $device)
                            <li><a class="dropdown-item"
                                    href="?device={{ $device->device_id }}">{{ $device->device_name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Image Gallery -->
        @if ($images->count() > 0)
            <div class="image-gallery">
                @foreach ($images as $image)
                    <div class="gallery-item">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="Captured Image"
                            onclick="showImageModal('{{ asset('storage/' . $image->image_path) }}', '{{ $image->device->device_name }}', '{{ $image->captured_at->format('M d, Y H:i:s') }}')">

                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $image->device->device_name ?? 'Unknown Device' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $image->device->location ?? 'Unknown Location' }}
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ asset('storage/' . $image->image_path) }}"
                                                download>
                                                <i class="fas fa-download me-2"></i>Download
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#"
                                                onclick="confirmDelete('image', {{ $image->id }}, '{{ $image->device->device_name }}')">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $image->captured_at->format('M d, Y') }}
                                </small>
                                <small class="text-muted">
                                    {{ $image->captured_at->format('H:i:s') }}
                                </small>
                            </div>

                            @if ($image->description)
                                <p class="text-muted mb-0 mt-2" style="font-size: 0.85em;">
                                    {{ $image->description }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $images->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">No Images Found</h4>
                <p class="text-muted">No captured images available</p>
            </div>
        @endif
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalTitle">Captured Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Captured Image" class="img-fluid rounded">
                    <p id="imageDetails" class="mt-3 text-muted"></p>
                </div>
                <div class="modal-footer">
                    <a id="downloadLink" href="" download class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showImageModal(imageSrc, deviceName, capturedAt) {
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                document.getElementById('modalImage').src = imageSrc;
                document.getElementById('imageModalTitle').textContent = `${deviceName} - Captured Image`;
                document.getElementById('imageDetails').textContent = `Captured on ${capturedAt}`;
                document.getElementById('downloadLink').href = imageSrc;
                modal.show();
            }
        </script>
    @endpush
@endsection
