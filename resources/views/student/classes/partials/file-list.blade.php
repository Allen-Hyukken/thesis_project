{{--
    Shared file list with instant preview modal.
    Required vars: $class, $materials, $role ('teacher'|'student')
--}}

{{-- ── Preview Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:12px; overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header py-2 px-4" style="background:#25396f; border:none;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark text-white fs-5" id="previewIcon"></i>
                    <span class="text-white fw-semibold" id="previewTitle" style="font-size:15px;"></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="previewDownloadBtn" href="#" class="btn btn-sm btn-outline-light" title="Download">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0 bg-dark" id="previewBody" style="min-height:520px; position:relative;">

                {{-- Loading spinner --}}
                <div id="previewLoader" class="d-flex align-items-center justify-content-center"
                     style="height:520px;">
                    <div class="text-center text-white opacity-75">
                        <div class="spinner-border mb-3" role="status"></div>
                        <div style="font-size:13px;">Loading preview…</div>
                    </div>
                </div>

                {{-- PDF --}}
                <iframe id="previewPdf" src="" style="display:none; width:100%; height:80vh; border:none;"></iframe>

                {{-- Image --}}
                <div id="previewImageWrap" style="display:none; text-align:center; padding:20px; background:#1a1a2e;">
                    <img id="previewImage" src="" alt=""
                         style="max-width:100%; max-height:78vh; border-radius:8px; object-fit:contain; box-shadow:0 4px 24px rgba(0,0,0,.5);">
                </div>

                {{-- Video --}}
                <div id="previewVideoWrap" style="display:none; background:#000;">
                    <video id="previewVideo" controls
                           style="width:100%; max-height:80vh; display:block;">
                        Your browser does not support video.
                    </video>
                </div>

                {{-- Audio --}}
                <div id="previewAudioWrap" style="display:none; padding:60px 40px; text-align:center; background:#1a1a2e;">
                    <i class="bi bi-music-note-beamed text-white" style="font-size:4rem; opacity:.6;"></i>
                    <p class="text-white mt-3 mb-4" id="previewAudioName" style="opacity:.8;"></p>
                    <audio id="previewAudio" controls style="width:100%; max-width:500px;">
                        Your browser does not support audio.
                    </audio>
                </div>

                {{-- Office / unsupported (Google Docs Viewer) --}}
                <iframe id="previewOffice" src="" style="display:none; width:100%; height:80vh; border:none;"></iframe>

                {{-- Cannot preview --}}
                <div id="previewUnsupported" style="display:none; padding:80px 40px; text-align:center;">
                    <i class="bi bi-file-earmark-x text-white" style="font-size:3.5rem; opacity:.5;"></i>
                    <p class="text-white mt-3 mb-4" style="opacity:.7;">This file type cannot be previewed in the browser.</p>
                    <a id="previewUnsupportedDownload" href="#" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Download instead
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── File List ───────────────────────────────────────────────────── --}}
@forelse ($class->materials as $material)
    @php
        $previewUrl  = ($role === 'teacher')
            ? route('teacher.classes.materials.preview',  [$class->class_id, $material->material_id])
            : route('student.classes.materials.preview',  [$class->class_id, $material->material_id]);
        $downloadUrl = ($role === 'teacher')
            ? route('teacher.classes.materials.download', [$class->class_id, $material->material_id])
            : route('student.classes.materials.download', [$class->class_id, $material->material_id]);

        $mime = $material->mime_type ?? '';
        $ext  = strtolower(pathinfo($material->original_filename, PATHINFO_EXTENSION));

        // Choose icon by mime / extension
        $icon = match(true) {
            str_starts_with($mime, 'image/')                        => 'bi-file-earmark-image text-success',
            str_starts_with($mime, 'video/')                        => 'bi-file-earmark-play text-danger',
            str_starts_with($mime, 'audio/')                        => 'bi-file-earmark-music text-warning',
            $mime === 'application/pdf'                             => 'bi-file-earmark-pdf text-danger',
            in_array($ext, ['doc','docx'])                          => 'bi-file-earmark-word text-primary',
            in_array($ext, ['xls','xlsx'])                          => 'bi-file-earmark-excel text-success',
            in_array($ext, ['ppt','pptx'])                          => 'bi-file-earmark-ppt text-warning',
            in_array($ext, ['zip','rar','7z'])                      => 'bi-file-earmark-zip text-secondary',
            default                                                  => 'bi-file-earmark-text text-primary',
        };
    @endphp

    <div class="file-row d-flex align-items-center gap-3 p-3 rounded mb-2"
         style="border:1px solid #dfe3e7; background:#fff; transition: box-shadow .15s;">

        {{-- Icon --}}
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded"
             style="width:44px; height:44px; background:#ebf3ff;">
            <i class="bi {{ $icon }} fs-4"></i>
        </div>

        {{-- Info --}}
        <div class="flex-grow-1 overflow-hidden">
            <p class="fw-semibold mb-0 text-truncate" style="color:#25396f;">{{ $material->title }}</p>
            <p class="text-muted mb-0 text-truncate" style="font-size:11.5px;">
                {{ $material->original_filename }}
                &bull; {{ $material->humanSize() }}
                &bull; {{ $material->created_at ? \Illuminate\Support\Carbon::parse($material->created_at)->format('M d, Y') : '—' }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 flex-shrink-0">
            {{-- Preview button --}}
            <button type="button"
                    class="btn btn-sm btn-primary preview-btn"
                    data-preview-url="{{ $previewUrl }}"
                    data-download-url="{{ $downloadUrl }}"
                    data-title="{{ $material->title }}"
                    data-filename="{{ $material->original_filename }}"
                    data-mime="{{ $mime }}"
                    data-ext="{{ $ext }}"
                    title="Preview">
                <i class="bi bi-eye me-1"></i> View
            </button>

            {{-- Download --}}
            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary" title="Download">
                <i class="bi bi-download"></i>
            </a>

            {{-- Delete (teacher only) --}}
            @if ($role === 'teacher')
                <form action="{{ route('teacher.classes.materials.destroy', [$class->class_id, $material->material_id]) }}"
                      method="POST" class="delete-material-form">
                    @csrf @method('DELETE')
                    <input type="hidden" name="material_title" value="{{ $material->title }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>
        <p class="mb-0">No files {{ $role === 'teacher' ? 'uploaded' : 'shared' }} yet.</p>
    </div>
@endforelse

@push('scripts')
    <script>
        (function () {
            // ── helpers ──────────────────────────────────────────────────────
            function hideAll() {
                ['previewPdf','previewImageWrap','previewVideoWrap',
                    'previewAudioWrap','previewOffice','previewUnsupported'].forEach(id => {
                    document.getElementById(id).style.display = 'none';
                });
            }
            function showLoader(show) {
                const loader = document.getElementById('previewLoader');
                loader.classList.toggle('d-flex', show);
                loader.classList.toggle('d-none', !show);
            }

            // ── Preview button click ─────────────────────────────────────────
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const url      = this.dataset.previewUrl;
                    const dlUrl    = this.dataset.downloadUrl;
                    const title    = this.dataset.title;
                    const filename = this.dataset.filename;
                    const mime     = this.dataset.mime;
                    const ext      = this.dataset.ext;

                    // Set header
                    document.getElementById('previewTitle').textContent = title;
                    document.getElementById('previewDownloadBtn').href  = dlUrl;
                    document.getElementById('previewUnsupportedDownload').href = dlUrl;
                    document.getElementById('previewAudioName').textContent = filename;

                    hideAll();
                    showLoader(true);

                    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
                    modal.show();

                    // ── Route by type ──────────────────────────────────────
                    const isImage  = mime.startsWith('image/');
                    const isVideo  = mime.startsWith('video/');
                    const isAudio  = mime.startsWith('audio/');
                    const isPdf    = mime === 'application/pdf';
                    const isOffice = ['doc','docx','xls','xlsx','ppt','pptx'].includes(ext);

                    if (isPdf) {
                        const iframe = document.getElementById('previewPdf');
                        iframe.onload = () => showLoader(false);
                        iframe.src = url;
                        iframe.style.display = 'block';

                    } else if (isImage) {
                        const img = document.getElementById('previewImage');
                        img.onload = () => showLoader(false);
                        img.onerror = () => { showLoader(false); showUnsupported(); };
                        img.src = url;
                        document.getElementById('previewImageWrap').style.display = 'block';

                    } else if (isVideo) {
                        const video = document.getElementById('previewVideo');
                        video.src = url;
                        video.load();
                        showLoader(false);
                        document.getElementById('previewVideoWrap').style.display = 'block';

                    } else if (isAudio) {
                        const audio = document.getElementById('previewAudio');
                        audio.src = url;
                        audio.load();
                        showLoader(false);
                        document.getElementById('previewAudioWrap').style.display = 'block';

                    } else if (isOffice) {
                        // Use Google Docs Viewer for Office files
                        const iframe = document.getElementById('previewOffice');
                        const encoded = encodeURIComponent(window.location.origin + url);
                        iframe.onload = () => showLoader(false);
                        iframe.src = `https://docs.google.com/gviewer?url=${encoded}&embedded=true`;
                        iframe.style.display = 'block';

                    } else {
                        showLoader(false);
                        document.getElementById('previewUnsupported').style.display = 'block';
                    }
                });
            });

            // ── Clear sources when modal closes ─────────────────────────────
            document.getElementById('filePreviewModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('previewPdf').src    = '';
                document.getElementById('previewImage').src  = '';
                document.getElementById('previewVideo').src  = '';
                document.getElementById('previewAudio').src  = '';
                document.getElementById('previewOffice').src = '';
                hideAll();
                showLoader(true);
            });

            // ── Delete confirm ───────────────────────────────────────────────
            document.querySelectorAll('.delete-material-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const title = this.querySelector('[name="material_title"]').value;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Delete File?',
                            text: `"${title}" will be permanently removed.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete',
                        }).then(r => { if (r.isConfirmed) this.submit(); });
                    } else {
                        if (confirm(`Delete "${title}"?`)) this.submit();
                    }
                });
            });

            // ── Hover effect on file rows ────────────────────────────────────
            document.querySelectorAll('.file-row').forEach(row => {
                row.addEventListener('mouseenter', () => row.style.boxShadow = '0 2px 10px rgba(67,94,190,.12)');
                row.addEventListener('mouseleave', () => row.style.boxShadow = 'none');
            });
        })();
    </script>
@endpush
