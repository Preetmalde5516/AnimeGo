<?php
function generate_pagination($total_pages, $current_page, $base_url = '?') {
    if ($total_pages <= 1) {
        return '';
    }

    $pagination_html = '<nav class="pagination"><div class="page-numbers">';

    // Previous Button
    if ($current_page > 1) {
        $pagination_html .= '<a href="' . $base_url . 'page=' . ($current_page - 1) . '"><i class="fas fa-chevron-left"></i></a>';
    }

    // Page Numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $pagination_html .= '<a href="#" class="active">' . $i . '</a>';
        } else {
            $pagination_html .= '<a href="' . $base_url . 'page=' . $i . '">' . $i . '</a>';
        }
    }

    // Next Button
    if ($current_page < $total_pages) {
        $pagination_html .= '<a href="' . $base_url . 'page=' . ($current_page + 1) . '"><i class="fas fa-chevron-right"></i></a>';
    }

    $pagination_html .= '</div></nav>';
    return $pagination_html;
}
?>