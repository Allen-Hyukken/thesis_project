<script>
    // ── File name display on dropzone select ─────────────────────────────
    function showFileName(input, dropzoneId) {
        const dz   = document.getElementById(dropzoneId);
        const name = input.files[0]?.name;
        if (!name) return;
        let existing = dz.querySelector('.tac-selected-display');
        if (!existing) {
            existing = document.createElement('div');
            existing.className = 'tac-selected-display mt-2';
            existing.style.cssText = 'font-size:12px;color:#435ebe;font-weight:600;';
            dz.appendChild(existing);
        }
        existing.innerHTML = `<i class="bi bi-paperclip me-1"></i>${name}`;
    }

    // ── Drag & drop on dropzones ─────────────────────────────────────────
    document.querySelectorAll('.tac-dropzone').forEach(dz => {
        dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragover'); });
        dz.addEventListener('dragleave', ()  => dz.classList.remove('dragover'));
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('dragover');
            const input = dz.querySelector('.tac-file-input');
            if (input && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFileName(input, dz.id);
            }
        });
    });

    // ── Submission file preview modal ─────────────────────────────────────
    function previewSubmissionFile(url, filename, mime, downloadUrl) {
        // Reset
        ['subPreviewPdf','subPreviewImgWrap','subPreviewVideoWrap','subPreviewAudioWrap'].forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
        document.getElementById('subPreviewLoader').style.display  = 'flex';
        document.getElementById('subPreviewTitle').textContent     = filename;
        document.getElementById('subPreviewDownload').href         = downloadUrl;

        const modal = new bootstrap.Modal(document.getElementById('submissionPreviewModal'));
        modal.show();

        const done = () => document.getElementById('subPreviewLoader').style.display = 'none';

        if (mime === 'application/pdf') {
            const iframe = document.getElementById('subPreviewPdf');
            iframe.onload = done;
            iframe.src = url;
            iframe.style.display = 'block';
        } else if (mime.startsWith('image/')) {
            const img = document.getElementById('subPreviewImg');
            img.onload = done;
            img.src = url;
            document.getElementById('subPreviewImgWrap').style.display = 'block';
        } else if (mime.startsWith('video/')) {
            const v = document.getElementById('subPreviewVideo');
            v.src = url; v.load(); done();
            document.getElementById('subPreviewVideoWrap').style.display = 'block';
        } else if (mime.startsWith('audio/')) {
            const a = document.getElementById('subPreviewAudio');
            a.src = url; a.load(); done();
            document.getElementById('subPreviewAudioWrap').style.display = 'block';
        }
    }

    // ── Clear modal on close ──────────────────────────────────────────────
    document.getElementById('submissionPreviewModal')?.addEventListener('hidden.bs.modal', function () {
        document.getElementById('subPreviewPdf').src   = '';
        document.getElementById('subPreviewImg').src   = '';
        document.getElementById('subPreviewVideo').src = '';
        document.getElementById('subPreviewAudio').src = '';
    });
</script>
