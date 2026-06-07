<?php
/*
 * @Module:      Alert/Flash Messages
 * @Author:      FE-01 (UI Shell Lead)
 * @Date:        2026-05-24
 * @Description: Flash message display component with auto-dismiss.
 * @Ownership:   FE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

$flash = getFlash();
if ($flash):
    $typeConfig = [
        'success' => ['bg' => 'bg-emerald-500/10 border-emerald-500/20', 'text' => 'text-emerald-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'error'   => ['bg' => 'bg-rose-500/10 border-rose-500/20', 'text' => 'text-rose-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>'],
        'warning' => ['bg' => 'bg-amber-500/10 border-amber-500/20', 'text' => 'text-amber-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>'],
        'info'    => ['bg' => 'bg-sky-500/10 border-sky-500/20', 'text' => 'text-sky-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>'],
    ];
    $cfg = $typeConfig[$flash['type']] ?? $typeConfig['info'];
?>
<div class="fixed top-28 right-6 z-50 max-w-md animate-fade-in-up" id="flash-alert">
    <div class="<?= $cfg['bg'] ?> border rounded-xl p-4 backdrop-blur-xl shadow-lg flex items-start gap-3">
        <svg class="w-5 h-5 <?= $cfg['text'] ?> flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <?= $cfg['icon'] ?>
        </svg>
        <p class="text-sm <?= $cfg['text'] ?> font-medium"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></p>
        <button onclick="document.getElementById('flash-alert').remove()" class="ml-auto <?= $cfg['text'] ?> hover:opacity-70 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
<script>
setTimeout(function() {
    const el = document.getElementById('flash-alert');
    if (el) { el.style.opacity = '0'; el.style.transform = 'translateY(-10px)'; el.style.transition = 'all 0.3s'; setTimeout(() => el.remove(), 300); }
}, 5000);
</script>
<?php endif; ?>
