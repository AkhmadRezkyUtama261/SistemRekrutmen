<?php
/*
 * @Module:      Table Component
 * @Author:      FE-05
 * @Date:        2026-05-24
 * @Description: Data table wrapper with dark theme and responsive design.
 * @Ownership:   FE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

/**
 * Render table opening tags.
 * @param array $headers Array of header labels
 */
function renderTableOpen(array $headers): void {
?>
<div class="bg-slate-800/60 backdrop-blur-xl border border-white/[0.08] rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($headers as $header): ?>
                    <th><?= htmlspecialchars($header, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
<?php
}

/**
 * Render table closing tags.
 */
function renderTableClose(): void {
?>
            </tbody>
        </table>
    </div>
</div>
<?php
}
