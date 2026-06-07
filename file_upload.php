<?php
/*
 * @Module:      File Upload Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Drag-and-drop PDF upload zone with visual feedback.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

$uploadFieldName = $fieldName ?? 'cv';
$uploadAccept    = '.pdf';
$uploadMaxLabel  = '2MB';
?>

<div class="upload-zone" id="upload-zone"
     ondragover="event.preventDefault(); this.classList.add('dragover')"
     ondragleave="this.classList.remove('dragover')"
     ondrop="handleDrop(event)"
     onclick="document.getElementById('file-input-<?= $uploadFieldName ?>').click()">

    <input type="file" name="<?= htmlspecialchars($uploadFieldName, ENT_QUOTES, 'UTF-8') ?>"
           id="file-input-<?= htmlspecialchars($uploadFieldName, ENT_QUOTES, 'UTF-8') ?>"
           accept="<?= $uploadAccept ?>"
           class="hidden"
           onchange="handleFileSelect(this)">

    <div id="upload-placeholder">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
            <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
        </div>
        <p class="text-sm text-slate-300 font-medium mb-1">Klik atau drag & drop file di sini</p>
        <p class="text-xs text-slate-500">Hanya file PDF, maksimal <?= $uploadMaxLabel ?></p>
    </div>

    <div id="upload-preview" class="hidden">
        <div class="flex items-center gap-4 p-4 bg-slate-900/50 rounded-xl border border-white/[0.06]">
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate" id="file-name">document.pdf</p>
                <p class="text-xs text-slate-500" id="file-size">0 KB</p>
            </div>
            <button type="button" onclick="event.stopPropagation(); removeFile()" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.type !== 'application/pdf') {
        alert('Hanya file PDF yang diperbolehkan.');
        input.value = '';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB.');
        input.value = '';
        return;
    }
    showPreview(file);
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    if (file.type !== 'application/pdf') {
        alert('Hanya file PDF yang diperbolehkan.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB.');
        return;
    }
    const input = document.getElementById('file-input-<?= $uploadFieldName ?>');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    showPreview(file);
}

function showPreview(file) {
    document.getElementById('upload-placeholder').classList.add('hidden');
    document.getElementById('upload-preview').classList.remove('hidden');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
}

function removeFile() {
    const input = document.getElementById('file-input-<?= $uploadFieldName ?>');
    input.value = '';
    document.getElementById('upload-placeholder').classList.remove('hidden');
    document.getElementById('upload-preview').classList.add('hidden');
}
</script>
