<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/icons.php';

/**
 * Header judul halaman desktop (sembunyi di mobile karena sudah di mobile-header) + pill tanggal opsional.
 */
function render_page_header(string $title, string $subtitle, ?string $dateText = null): void
{
    ?>
    <div class="page-head">
      <div class="page-head-copy">
        <h1 class="page-title"><?= esc($title) ?></h1>
        <p class="page-subtitle"><?= esc($subtitle) ?></p>
      </div>
      <?php if ($dateText !== null): ?>
        <div class="date-pill" aria-label="Tanggal aktif">
          <span class="date-pill-icon"><?= icon('calendar', 18) ?></span>
          <span><?= esc($dateText) ?></span>
        </div>
      <?php endif; ?>
    </div>
    <?php
}

/**
 * Badge status seragam.
 */
function render_badge(string $label, string $variantClass): string
{
    return '<span class="badge ' . esc($variantClass) . '">' . esc($label) . '</span>';
}

/**
 * Empty state card seragam dengan ikon lingkaran + judul + deskripsi.
 */
function render_empty_state(string $iconName, string $title, string $desc, string $extraClass = ''): string
{
    return '<div class="empty-state ' . esc($extraClass) . '">'
        . '<div class="empty-state-icon" aria-hidden="true">' . icon($iconName, 30, '', 1.8) . '</div>'
        . '<h3 class="empty-state-title">' . esc($title) . '</h3>'
        . '<p class="empty-state-desc">' . esc($desc) . '</p>'
        . '</div>';
}

/**
 * Paginasi standar: "Menampilkan X–Y dari N" + tombol ‹ 1..k ›.
 */
function render_pagination(int $total, int $page, int $perPage, string $targetId = ''): string
{
    if ($total <= 0) {
        return '';
    }
    $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
    $current = max(1, min($page, $totalPages));
    $from = ($current - 1) * $perPage + 1;
    $to = min($current * $perPage, $total);

    $maxBtns = 5;
    $start = max(1, $current - 2);
    $end = min($totalPages, $start + $maxBtns - 1);
    if ($end - $start + 1 < $maxBtns) {
        $start = max(1, $end - $maxBtns + 1);
    }

    $attr = $targetId !== '' ? ' data-pager="' . esc($targetId) . '"' : '';
    $html = '<div class="pager-bar"' . $attr . ' data-page="' . $current . '">';
    $html .= '<span class="pager-info">Menampilkan ' . $from . '–' . $to . ' dari ' . $total . '</span>';
    $html .= '<div class="pager-controls" role="navigation" aria-label="Paginasi">';

    $prevDisabled = $current <= 1 ? ' disabled aria-disabled="true"' : '';
    $html .= '<button type="button" class="pg-btn" data-page-target="' . ($current - 1) . '" aria-label="Halaman sebelumnya"' . $prevDisabled . '>‹</button>';

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current ? ' is-active" aria-current="page' : '';
        $html .= '<button type="button" class="pg-btn' . $active . '" data-page-target="' . $i . '">' . $i . '</button>';
    }

    $nextDisabled = $current >= $totalPages ? ' disabled aria-disabled="true"' : '';
    $html .= '<button type="button" class="pg-btn" data-page-target="' . ($current + 1) . '" aria-label="Halaman berikutnya"' . $nextDisabled . '>›</button>';
    $html .= '</div></div>';
    return $html;
}
