{{-- Include once in the page that uses activity cards --}}
<style>
    /* ═══════════════════════════════════════════════════════════
       MS Teams-style Activity Card
    ═══════════════════════════════════════════════════════════ */
    .teams-activity-card {
        border: 1px solid #dfe3e7;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow .15s;
    }
    .teams-activity-card:hover {
        box-shadow: 0 3px 14px rgba(67,94,190,.12);
    }

    /* Header */
    .tac-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f0f2f5;
        background: #fff;
    }
    .tac-type-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .tac-title {
        font-weight: 700;
        font-size: 15px;
        color: #25396f;
        line-height: 1.3;
    }
    .tac-meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    .tac-badge {
        padding: 1px 8px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 11px;
    }
    .tac-dot { opacity: .4; }

    /* Status chips */
    .tac-status-chip {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .tac-chip-graded   { background: #e8f8f0; color: #1a8a4a; }
    .tac-chip-submitted { background: #ebf3ff; color: #435ebe; }
    .tac-chip-pending  { background: #f5f7fb; color: #6c757d; border: 1px solid #dfe3e7; }

    /* Body */
    .tac-body {
        padding: 16px 18px;
    }
    .tac-instructions {
        font-size: 14px;
        color: #444;
        white-space: pre-wrap;
        line-height: 1.7;
        margin-bottom: 14px;
    }

    /* Feedback box */
    .tac-feedback-box {
        background: #f0faf5;
        border: 1px solid #b7e4cc;
        border-radius: 8px;
        padding: 14px 16px;
    }
    .tac-feedback-score {
        font-size: 15px;
        font-weight: 700;
        color: #1a8a4a;
        margin-bottom: 8px;
    }
    .tac-feedback-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .tac-feedback-text {
        font-size: 13.5px;
        color: #2d3748;
        white-space: pre-wrap;
        line-height: 1.6;
        padding-top: 8px;
        border-top: 1px solid #cdeedd;
        margin-top: 8px;
    }

    /* Submitted banner */
    .tac-submitted-banner {
        background: #ebf3ff;
        border: 1px solid #c5d9f8;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13.5px;
        color: #435ebe;
    }
    .tac-submitted-preview,
    .tac-submitted-text { margin-top: 12px; }
    .tac-preview-label { @extend .tac-feedback-label; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6c757d; margin-bottom:5px; }

    /* Text bubble (submitted answer) */
    .tac-text-bubble {
        background: #f5f7fb;
        border: 1px solid #dfe3e7;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13.5px;
        color: #444;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    /* Form section */
    .tac-form-section { margin-top: 4px; }
    .tac-form-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #25396f;
        margin-bottom: 5px;
        display: block;
    }
    .tac-textarea {
        font-size: 14px;
        border: 1.5px solid #dfe3e7;
        border-radius: 8px;
        resize: vertical;
        transition: border-color .2s;
    }
    .tac-textarea:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 3px rgba(67,94,190,.1);
    }

    /* Dropzone */
    .tac-dropzone {
        border: 2px dashed #c5d9f8;
        border-radius: 8px;
        padding: 20px 16px;
        text-align: center;
        background: #f8faff;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .tac-dropzone:hover,
    .tac-dropzone.dragover { border-color: #435ebe; background: #ebf3ff; }
    .tac-dropzone-text { font-size: 13px; color: #6c757d; }
    .tac-browse-link { color: #435ebe; font-weight: 600; cursor: pointer; text-decoration: underline; }
    .tac-file-input {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer;
        width: 100%; height: 100%;
    }
    .tac-current-file { font-size: 12px; color: #6c757d; }

    /* File chip */
    .tac-file-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: #f5f7fb;
        border: 1px solid #dfe3e7;
        border-radius: 8px;
        font-size: 13.5px;
    }
    .tac-file-chip-name {
        flex-grow: 1;
        color: #25396f;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .tac-file-chip-actions { display: flex; gap: 6px; flex-shrink: 0; }
</style>

{{-- Submission file preview modal (shared, rendered once per page) --}}
<div class="modal fade" id="submissionPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header py-2 px-4" style="background:#25396f; border:none;">
                <span class="text-white fw-semibold" id="subPreviewTitle"></span>
                <div class="d-flex gap-2">
                    <a id="subPreviewDownload" href="#" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark" style="min-height:500px; position:relative;">
                <div id="subPreviewLoader" class="d-flex align-items-center justify-content-center" style="height:500px;">
                    <div class="text-center text-white opacity-75">
                        <div class="spinner-border mb-2"></div>
                        <div style="font-size:13px;">Loading…</div>
                    </div>
                </div>
                <iframe id="subPreviewPdf"    src="" style="display:none; width:100%; height:80vh; border:none;"></iframe>
                <div   id="subPreviewImgWrap" style="display:none; text-align:center; padding:20px; background:#1a1a2e;">
                    <img id="subPreviewImg" src="" style="max-width:100%; max-height:78vh; border-radius:8px; object-fit:contain;">
                </div>
                <div   id="subPreviewVideoWrap" style="display:none; background:#000;">
                    <video id="subPreviewVideo" controls style="width:100%; max-height:80vh; display:block;"></video>
                </div>
                <div   id="subPreviewAudioWrap" style="display:none; padding:60px 40px; text-align:center; background:#1a1a2e;">
                    <i class="bi bi-music-note-beamed text-white" style="font-size:4rem; opacity:.6;"></i>
                    <audio id="subPreviewAudio" controls style="width:100%; max-width:500px; display:block; margin:24px auto 0;"></audio>
                </div>
            </div>
        </div>
    </div>
</div>
