{{-- submission-file-chip.blade.php — shows submitted file with preview/download --}}
@php
    $previewUrl  = route('student.activities.submissions.preview',  $sub->submission_id);
    $downloadUrl = route('student.activities.submissions.download', $sub->submission_id);
    $mime        = $sub->file_mime_type ?? '';
    $ext         = strtolower(pathinfo($sub->file_original_name, PATHINFO_EXTENSION));

    $canInlinePreview = str_starts_with($mime, 'image/')
        || str_starts_with($mime, 'video/')
        || str_starts_with($mime, 'audio/')
        || $mime === 'application/pdf';

    $icon = match(true) {
        str_starts_with($mime, 'image/')     => 'bi-file-earmark-image text-success',
        str_starts_with($mime, 'video/')     => 'bi-file-earmark-play text-danger',
        str_starts_with($mime, 'audio/')     => 'bi-file-earmark-music text-warning',
        $mime === 'application/pdf'          => 'bi-file-earmark-pdf text-danger',
        in_array($ext, ['doc','docx'])       => 'bi-file-earmark-word text-primary',
        in_array($ext, ['xls','xlsx'])       => 'bi-file-earmark-excel text-success',
        in_array($ext, ['ppt','pptx'])       => 'bi-file-earmark-ppt text-warning',
        default                              => 'bi-file-earmark text-secondary',
    };
@endphp

<div class="tac-file-chip">
    <i class="bi {{ $icon }} fs-5"></i>
    <span class="tac-file-chip-name">{{ $sub->file_original_name }}</span>
    <div class="tac-file-chip-actions">
        @if ($canInlinePreview)
            <button type="button" class="btn btn-sm btn-outline-primary"
                    onclick="previewSubmissionFile('{{ $previewUrl }}', '{{ $sub->file_original_name }}', '{{ $mime }}', '{{ $downloadUrl }}')">
                <i class="bi bi-eye me-1"></i> View
            </button>
        @endif
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download"></i>
        </a>
    </div>
</div>
