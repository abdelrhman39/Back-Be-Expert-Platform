@php
    $template = $render['template'];
    $elements = $render['elements'];
    $width = (int) ($template['canvas_width'] ?? 1123);
    $height = (int) ($template['canvas_height'] ?? 794);
    $settings = $template['settings'] ?? [];
    $backgroundColor = $settings['background_color'] ?? '#ffffff';
@endphp

<div
    class="certificate-render-shell {{ $forPdf ? 'is-pdf' : 'is-responsive' }}"
    data-certificate-width="{{ $width }}"
    data-certificate-height="{{ $height }}"
>
    <div
        class="certificate-render"
        style="
            width: {{ $width }}px;
            height: {{ $height }}px;
            background-color: {{ $backgroundColor }};
            @if ($render['background']) background-image: url('{{ $render['background'] }}'); @endif
        "
        role="img"
        aria-label="شهادة {{ $certificate->holder_name }}"
    >
        @foreach ($elements as $element)
            @php
                $type = $element['type'] ?? 'text';
                $elementStyle = sprintf(
                    'left:%spx;top:%spx;width:%spx;height:%spx;z-index:%s;transform:rotate(%sdeg);',
                    $element['x'] ?? 0,
                    $element['y'] ?? 0,
                    $element['width'] ?? 100,
                    $element['height'] ?? 40,
                    $element['z_index'] ?? 1,
                    $element['rotation'] ?? 0,
                );
            @endphp

            @if ($type === 'text')
                <div
                    class="certificate-render__element certificate-render__text"
                    style="{{ $elementStyle }}
                        font-family: {{ $element['font_family'] ?? 'DejaVu Sans' }};
                        font-size: {{ $element['font_size'] ?? 18 }}px;
                        font-weight: {{ $element['font_weight'] ?? 400 }};
                        color: {{ $element['color'] ?? '#111827' }};
                        background: {{ $element['background'] ?? 'transparent' }};
                        text-align: {{ $element['align'] ?? 'center' }};
                        direction: {{ $element['direction'] ?? 'rtl' }};
                        line-height: {{ $element['line_height'] ?? 1.4 }};
                        letter-spacing: {{ $element['letter_spacing'] ?? 0 }}px;
                    "
                >{!! nl2br(e($element['rendered_content'] ?? '')) !!}</div>
            @elseif ($type === 'qr')
                <div class="certificate-render__element certificate-render__qr" style="{{ $elementStyle }}">
                    <img src="{{ $element['data_uri'] ?? '' }}" alt="QR للتحقق من الشهادة">
                </div>
            @elseif ($type === 'line')
                <div
                    class="certificate-render__element certificate-render__line"
                    style="{{ $elementStyle }} background: {{ $element['color'] ?? '#111827' }};"
                ></div>
            @endif
        @endforeach
    </div>
</div>

<style>
    .certificate-render-shell{width:100%;padding:.25rem;box-sizing:border-box}
    .certificate-render-shell.is-responsive{position:relative;overflow:hidden}
    .certificate-render-shell.is-responsive .certificate-render{position:absolute;top:.25rem;left:50%;margin:0;transform:translateX(-50%) scale(var(--certificate-scale,1));transform-origin:top center}
    .certificate-render{position:relative;isolation:isolate;margin:0 auto;background-position:center;background-repeat:no-repeat;background-size:100% 100%;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,.16)}
    .certificate-render__element{position:absolute;box-sizing:border-box}
    .certificate-render__text{display:flex;align-items:center;justify-content:center;overflow:hidden;white-space:pre-wrap}
    .certificate-render__qr img{display:block;width:100%;height:100%;object-fit:contain}
    .certificate-render__line{min-height:1px}
    @media print{
        @page{margin:0}
        html,body{margin:0!important;padding:0!important}
        .certificate-render-shell{padding:0;overflow:visible}
        .certificate-render{margin:0;box-shadow:none;page-break-after:avoid}
    }
</style>

@unless ($forPdf)
    @once
        <script>
            (() => {
                const fitCertificates = () => {
                    document.querySelectorAll('.certificate-render-shell.is-responsive').forEach((shell) => {
                        const width = Number(shell.dataset.certificateWidth || 1123);
                        const height = Number(shell.dataset.certificateHeight || 794);
                        const available = Math.max(280, shell.clientWidth - 8);
                        const scale = Math.min(1, available / width);
                        shell.style.setProperty('--certificate-scale', scale);
                        shell.style.height = `${Math.ceil(height * scale) + 8}px`;
                    });
                };
                window.addEventListener('resize', fitCertificates);
                document.addEventListener('DOMContentLoaded', fitCertificates);
                document.addEventListener('livewire:navigated', fitCertificates);
                requestAnimationFrame(fitCertificates);
            })();
        </script>
    @endonce
@endunless
