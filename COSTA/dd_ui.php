<?php
// Iconos locales: el diseño no depende de servicios externos.
function ddIcono(string $nombre): string {
    $trazos = [
        'inicio' => '<path d="m3 10 9-7 9 7v11h-7v-7h-4v7H3z"/>',
        'factura' => '<path d="M6 3h12v19l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h4"/>',
        'pago' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v5c0 4 16 4 16 0V5M4 10v5c0 4 16 4 16 0v-5M4 15v4c0 4 16 4 16 0v-4"/>',
        'usuarios' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/>',
        'auditoria' => '<path d="m12 3 9 4v5c0 6-9 10-9 10S3 18 3 12V7zM8 12l3 3 5-6"/>',
        'cartera' => '<path d="M3 7h17v14H3zM3 7V4l13-2v5M16 12h6v5h-6z"/>',
        'ver' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'editar' => '<path d="m15 3 6 6-12 12H3v-6zM12 6l6 6"/>',
        'buscar' => '<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/>',
        'salir' => '<path d="M9 3H3v18h6M9 12h13m-5-5 5 5-5 5"/>',
        'menu' => '<path d="M3 5h18M3 12h18M3 19h18"/>',
        'liquidacion' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M8 6h8M8 11h2m4 0h2M8 15h2m4 0h2M8 19h2m4 0h2"/>',
    ];
    return '<svg class="dd-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($trazos[$nombre] ?? $trazos['factura']).'</svg>';
}
function ddEsc($valor): string { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }
function ddDinero($valor): string { return '$ ' . number_format((float)$valor, 2, ',', '.'); }
