{{--
    Shared file list with instant preview modal.
    Required vars: $class, $materials, $role ('teacher'|'student')
    Office preview: mammoth.js (docx), SheetJS (xlsx), download fallback for others
--}}

{{-- ── Preview Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0" style="border-radius:0; overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header py-2 px-4" style="background:#25396f; border:none; flex-shrink:0;">
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
            <div class="modal-body p-0 flex-grow-1" id="previewBody"
                 style="position:relative; background:#1e1e2e; overflow:hidden; display:flex; flex-direction:column;">

                {{-- PDF --}}
                <iframe id="previewPdf" src="" style="display:none; width:100%; height:100%; border:none; flex:1;"></iframe>

                {{-- Image --}}
                <div id="previewImageWrap" style="display:none; flex:1; overflow:auto; text-align:center; padding:20px; background:#1a1a2e;">
                    <img id="previewImage" src="" alt=""
                         style="max-width:100%; max-height:100%; border-radius:8px; object-fit:contain; box-shadow:0 4px 24px rgba(0,0,0,.5);">
                </div>

                {{-- Video --}}
                <div id="previewVideoWrap" style="display:none; flex:1; background:#000; display:flex; align-items:center; justify-content:center;">
                    <video id="previewVideo" controls style="width:100%; height:100%; display:block; object-fit:contain;">
                        Your browser does not support video.
                    </video>
                </div>

                {{-- Audio --}}
                <div id="previewAudioWrap" style="display:none; flex:1; padding:60px 40px; text-align:center; background:#1a1a2e; align-items:center; justify-content:center; flex-direction:column;">
                    <i class="bi bi-music-note-beamed text-white" style="font-size:4rem; opacity:.6;"></i>
                    <p class="text-white mt-3 mb-4" id="previewAudioName" style="opacity:.8;"></p>
                    <audio id="previewAudio" controls style="width:100%; max-width:500px;">
                        Your browser does not support audio.
                    </audio>
                </div>

                {{-- DOCX rendered by mammoth.js --}}
                <div id="previewDocxWrap" style="display:none; flex:1; background:#f5f5f5; overflow-y:auto;">
                    <div id="previewDocxContent"
                         style="max-width:860px; margin:0 auto; padding:48px 56px; background:#fff;
                                min-height:100%; font-family:'Segoe UI',sans-serif;
                                font-size:14.5px; line-height:1.85; color:#1a1a1a;
                                box-shadow:0 0 0 1px #e5e7eb;">
                    </div>
                </div>

                {{-- XLSX rendered by SheetJS --}}
                <div id="previewXlsxWrap" style="display:none; flex:1; background:#fff; overflow:auto;">
                    <div id="previewXlsxContent" style="padding:20px; min-width:max-content;"></div>
                </div>

                {{-- Cannot preview --}}
                <div id="previewUnsupported" style="display:none; flex:1; padding:80px 40px; text-align:center; align-items:center; justify-content:center; flex-direction:column;">
                    <i class="bi bi-file-earmark-arrow-down text-white" style="font-size:3.5rem; opacity:.5;"></i>
                    <p class="text-white mt-3 mb-1" style="opacity:.85; font-weight:600; font-size:16px;"
                       id="previewUnsupportedLabel">This file cannot be previewed in the browser.</p>
                    <p class="mb-4" style="color:rgba(255,255,255,.55); font-size:13px;">
                        Download it to open with the appropriate app on your device.
                    </p>
                    <a id="previewUnsupportedDownload" href="#" class="btn btn-primary px-4">
                        <i class="bi bi-download me-1"></i> Download File
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

        $icon = match(true) {
            str_starts_with($mime, 'image/')   => 'bi-file-earmark-image text-success',
            str_starts_with($mime, 'video/')   => 'bi-file-earmark-play text-danger',
            str_starts_with($mime, 'audio/')   => 'bi-file-earmark-music text-warning',
            $mime === 'application/pdf'        => 'bi-file-earmark-pdf text-danger',
            in_array($ext, ['doc','docx'])     => 'bi-file-earmark-word text-primary',
            in_array($ext, ['xls','xlsx'])     => 'bi-file-earmark-excel text-success',
            in_array($ext, ['ppt','pptx'])     => 'bi-file-earmark-ppt text-warning',
            in_array($ext, ['zip','rar','7z']) => 'bi-file-earmark-zip text-secondary',
            default                            => 'bi-file-earmark-text text-primary',
        };
    @endphp

    <div class="file-row d-flex align-items-center gap-3 p-3 rounded mb-2"
         style="border:1px solid #dfe3e7; background:#fff; transition: box-shadow .15s;">

        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded"
             style="width:44px; height:44px; background:#ebf3ff;">
            <i class="bi {{ $icon }} fs-4"></i>
        </div>

        <div class="flex-grow-1 overflow-hidden">
            <p class="fw-semibold mb-0 text-truncate" style="color:#25396f;">{{ $material->title }}</p>
            <p class="text-muted mb-0 text-truncate" style="font-size:11.5px;">
                {{ $material->original_filename }}
                &bull; {{ $material->humanSize() }}
                &bull; {{ $material->created_at ? \Illuminate\Support\Carbon::parse($material->created_at)->format('M d, Y') : '—' }}
            </p>
        </div>

        <div class="d-flex gap-2 flex-shrink-0">
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

            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary" title="Download">
                <i class="bi bi-download"></i>
            </a>

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
    {{-- mammoth.js: Word (.docx) → HTML in-browser --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    {{-- SheetJS: Excel (.xlsx) → table in-browser --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        /* ── DOCX rendered output ─────────────────────────────────────── */
        #previewDocxContent h1 { font-size:22px; font-weight:700; margin:0 0 .6em; color:#111; }
        #previewDocxContent h2 { font-size:18px; font-weight:700; margin:1.2em 0 .5em; color:#25396f; border-bottom:1px solid #e5e7eb; padding-bottom:4px; }
        #previewDocxContent h3 { font-size:15px; font-weight:700; margin:1em 0 .4em; color:#333; }
        #previewDocxContent p  { margin:0 0 .85em; }
        #previewDocxContent ul,
        #previewDocxContent ol { padding-left:1.6em; margin:0 0 .85em; }
        #previewDocxContent li { margin-bottom:.3em; }
        #previewDocxContent table { width:100%; border-collapse:collapse; margin-bottom:1em; font-size:13.5px; }
        #previewDocxContent th { background:#f0f4ff; padding:8px 12px; border:1px solid #dde3f0; font-weight:700; text-align:left; color:#25396f; }
        #previewDocxContent td { padding:7px 12px; border:1px solid #e5e7eb; }
        #previewDocxContent tr:nth-child(even) td { background:#f9fafb; }
        #previewDocxContent strong { font-weight:700; }
        #previewDocxContent em { font-style:italic; }
        #previewDocxContent img { max-width:100%; border-radius:6px; margin:8px 0; }

        /* ── XLSX rendered table ──────────────────────────────────────── */
        #previewXlsxContent table { border-collapse:collapse; font-size:13px; }
        #previewXlsxContent th { background:#25396f; color:#fff; padding:7px 14px; border:1px solid #1c2d5a; font-weight:600; white-space:nowrap; }
        #previewXlsxContent td { padding:6px 14px; border:1px solid #e5e7eb; white-space:nowrap; }
        #previewXlsxContent tr:nth-child(even) td { background:#f5f7ff; }
        #previewXlsxContent tr:hover td { background:#ebf3ff; }
    </style>

    <script>
        (function () {
            const PREVIEW_IDS = ['previewPdf','previewImageWrap','previewVideoWrap',
                'previewAudioWrap','previewDocxWrap','previewXlsxWrap','previewUnsupported'];

            function hideAll() {
                PREVIEW_IDS.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = 'none';
                });
            }

            function showUnsupported(label) {
                const el  = document.getElementById('previewUnsupported');
                const lbl = document.getElementById('previewUnsupportedLabel');
                if (lbl && label) lbl.textContent = label;
                if (el) el.style.display = 'flex';
            }

            // ── Fetch file as ArrayBuffer ──────────────────────────────────
            async function fetchBuffer(url) {
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Fetch failed: ' + res.status);
                return res.arrayBuffer();
            }

            // ── DOCX preview via mammoth.js ────────────────────────────────
            async function previewDocx(url) {
                const wrap    = document.getElementById('previewDocxWrap');
                const content = document.getElementById('previewDocxContent');
                content.innerHTML = '<p style="color:#aaa; text-align:center; padding:40px 0;">Rendering document...</p>';
                wrap.style.display = 'flex';
                try {
                    const buf    = await fetchBuffer(url);
                    const result = await mammoth.convertToHtml({ arrayBuffer: buf });
                    content.innerHTML = result.value || '<p style="color:#888;">Document appears to be empty.</p>';
                } catch (err) {
                    wrap.style.display = 'none';
                    showUnsupported('Could not render this Word document.');
                    console.error('mammoth error:', err);
                }
            }

            // ── XLSX preview via SheetJS ───────────────────────────────────
            async function previewXlsx(url) {
                const wrap    = document.getElementById('previewXlsxWrap');
                const content = document.getElementById('previewXlsxContent');
                content.innerHTML = '<p style="color:#aaa; padding:40px;">Rendering spreadsheet...</p>';
                wrap.style.display = 'flex';
                try {
                    const buf  = await fetchBuffer(url);
                    const wb   = XLSX.read(new Uint8Array(buf), { type: 'array' });
                    const name = wb.SheetNames[0];
                    const ws   = wb.Sheets[name];
                    const html = XLSX.utils.sheet_to_html(ws, { editable: false });
                    content.innerHTML = html;
                    const tbl = content.querySelector('table');
                    if (tbl) tbl.style.cssText = 'border-collapse:collapse;font-size:13px;';
                } catch (err) {
                    wrap.style.display = 'none';
                    showUnsupported('Could not render this Excel file.');
                    console.error('SheetJS error:', err);
                }
            }

            // ── Preview button click ───────────────────────────────────────
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const url      = this.dataset.previewUrl;
                    const dlUrl    = this.dataset.downloadUrl;
                    const title    = this.dataset.title;
                    const filename = this.dataset.filename;
                    const mime     = this.dataset.mime;
                    const ext      = this.dataset.ext;

                    document.getElementById('previewTitle').textContent        = title;
                    document.getElementById('previewDownloadBtn').href         = dlUrl;
                    document.getElementById('previewUnsupportedDownload').href = dlUrl;
                    document.getElementById('previewAudioName').textContent    = filename;
                    document.getElementById('previewUnsupportedLabel').textContent =
                        'This file cannot be previewed in the browser.';

                    hideAll();

                    const modal = bootstrap.Modal.getOrCreate
                        ? bootstrap.Modal.getOrCreate(document.getElementById('filePreviewModal'))
                        : new bootstrap.Modal(document.getElementById('filePreviewModal'));
                    modal.show();

                    const isImage        = mime.startsWith('image/');
                    const isVideo        = mime.startsWith('video/');
                    const isAudio        = mime.startsWith('audio/');
                    const isPdf          = mime === 'application/pdf';
                    const isDocx         = ext === 'docx';
                    const isXlsx         = ext === 'xlsx';
                    const isDownloadOnly = ['doc','xls','ppt','pptx'].includes(ext);

                    if (isPdf) {
                        const iframe = document.getElementById('previewPdf');
                        iframe.src = url;
                        iframe.style.display = 'block';

                    } else if (isImage) {
                        const img = document.getElementById('previewImage');
                        img.onerror = () => showUnsupported();
                        img.src = url;
                        document.getElementById('previewImageWrap').style.display = 'flex';

                    } else if (isVideo) {
                        const video = document.getElementById('previewVideo');
                        video.src = url;
                        video.load();
                        document.getElementById('previewVideoWrap').style.display = 'flex';

                    } else if (isAudio) {
                        const audio = document.getElementById('previewAudio');
                        audio.src = url;
                        audio.load();
                        document.getElementById('previewAudioWrap').style.display = 'flex';

                    } else if (isDocx) {
                        previewDocx(url);

                    } else if (isXlsx) {
                        previewXlsx(url);

                    } else if (isDownloadOnly) {
                        const labels = {
                            doc:  'Old Word (.doc) files cannot be previewed - please download.',
                            xls:  'Old Excel (.xls) files cannot be previewed - please download.',
                            ppt:  'PowerPoint (.ppt) files cannot be previewed - please download.',
                            pptx: 'PowerPoint (.pptx) files cannot be previewed - please download.',
                        };
                        showUnsupported(labels[ext] || 'This file type cannot be previewed in the browser.');

                    } else {
                        showUnsupported();
                    }
                });
            });

            // ── Clear on modal close ───────────────────────────────────────
            document.getElementById('filePreviewModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('previewPdf').src   = '';
                document.getElementById('previewImage').src = '';
                document.getElementById('previewVideo').src = '';
                document.getElementById('previewAudio').src = '';
                document.getElementById('previewDocxContent').innerHTML = '';
                document.getElementById('previewXlsxContent').innerHTML = '';
                hideAll();
            });

            // ── Delete confirm ─────────────────────────────────────────────
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

            // ── Hover effect ───────────────────────────────────────────────
            document.querySelectorAll('.file-row').forEach(row => {
                row.addEventListener('mouseenter', () => row.style.boxShadow = '0 2px 10px rgba(67,94,190,.12)');
                row.addEventListener('mouseleave', () => row.style.boxShadow = 'none');
            });
        })();
    </script>
@endpush
