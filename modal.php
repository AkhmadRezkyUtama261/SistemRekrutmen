<?php
/*
 * @Module:      Modal Component
 * @Author:      FE-01 (UI Shell Lead)
 * @Date:        2026-05-24
 * @Description: Reusable modal dialog with glassmorphism styling.
 * @Ownership:   FE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

/**
 * Render modal opening tag.
 * @param string $id    Unique modal ID
 * @param string $title Modal title
 * @param string $size  sm, md, or lg
 */
function renderModalOpen(string $id, string $title, string $size = 'md'): void {
    $widthClass = match($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        default => 'max-w-lg',
    };
?>
<div id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-overlay" onclick="if(event.target===this)closeModal('<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>')">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative <?= $widthClass ?> w-full bg-slate-800/90 backdrop-blur-xl border border-white/[0.08] rounded-2xl shadow-[0_25px_50px_rgba(0,0,0,0.5)] modal-content">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
            <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
            <button onclick="closeModal('<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>')" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <!-- Body -->
        <div class="px-6 py-4">
<?php
}

/**
 * Render modal closing tag.
 */
function renderModalClose(): void {
?>
        </div>
    </div>
</div>
<?php
}
?>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => modal.classList.add('active'));
    }
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
    }
}
</script>
