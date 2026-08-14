@php
    $data = $block['data'] ?? [];
    $fileUrl = $data['file_url'] ?? '';
    $href = str_starts_with($fileUrl, 'http') ? $fileUrl : static_asset($fileUrl);
@endphp

<div class="container">
    <div class="row">
        <div class="col-lg-12 profile-file">
            <div class="call-to-sction bg_image shape-move d-flex flex-column justify-content-center align-items-center text-center">
                <div>
                    @if ($data['title'] ?? null)
                        <h2 class="mb-3">{{ $data['title'] }}</h2>
                    @endif
                    @if ($data['description'] ?? null)
                        <p class="mb-3">{{ $data['description'] }}</p>
                    @endif
                </div>
                @if ($data['button_label'] ?? null)
                    <a href="{{ $href }}" target="_blank" rel="noopener" class="rts-btn btn-primary-white with-arrow" style="color: white; border: 1px solid #197A86; background-color:#197A86; border-radius:8px; padding:10px;">
                        {{ $data['button_label'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
