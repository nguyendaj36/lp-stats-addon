<?php
/*
Plugin Name: LearnPress Stats Dashboard
Plugin URI:  https://nguyendaj36.id.vn/
Description: Plugin hiển thị bảng thống kê tổng quan (khóa học, học viên, lượt hoàn thành) cho LearnPress.
Version:     1.0.0
Author:      Hậu
Text Domain: lp-stats-addon
*/

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * 1. Hàm truy vấn dữ liệu thống kê từ database
 */
function get_lp_custom_stats() {
    global $wpdb;

    // Lấy tổng số khóa học đã được xuất bản (Publish)
    $total_courses = wp_count_posts('lp_course')->publish;

    // Lấy tổng số học viên đã đăng ký (Đếm số user_id duy nhất trong bảng user_items)
    $enrolled_query = "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}learnpress_user_items WHERE item_type = 'lp_course'";
    $total_students = $wpdb->get_var($enrolled_query);

    // Lấy số lượng khóa học đã được hoàn thành
    $completed_query = "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_user_items WHERE item_type = 'lp_course' AND status = 'completed'";
    $total_completed = $wpdb->get_var($completed_query);

    return array(
        'courses'   => $total_courses ? $total_courses : 0,
        'students'  => $total_students ? $total_students : 0,
        'completed' => $total_completed ? $total_completed : 0,
    );
}

/**
 * 2. Tạo Dashboard Widget trong trang quản trị Admin
 */
function lp_stats_dashboard_widget_content() {
    $stats = get_lp_custom_stats();
    echo '<ul style="font-size: 15px; line-height: 1.8;">';
    echo '<li>📚 <strong>Tổng số khóa học:</strong> ' . esc_html($stats['courses']) . '</li>';
    echo '<li>👨‍🎓 <strong>Tổng số học viên:</strong> ' . esc_html($stats['students']) . '</li>';
    echo '<li>✅ <strong>Khóa học hoàn thành:</strong> ' . esc_html($stats['completed']) . '</li>';
    echo '</ul>';
}

function add_lp_stats_dashboard_widget() {
    wp_add_dashboard_widget(
        'lp_custom_stats_widget', 
        'LearnPress Stats Dashboard', 
        'lp_stats_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'add_lp_stats_dashboard_widget');

/**
 * 3. Tạo Shortcode hiển thị ngoài Frontend
 */
function lp_total_stats_shortcode() {
    $stats = get_lp_custom_stats();
    ob_start(); // Bắt đầu bộ đệm đầu ra
    ?>
    <div class="lp-stats-container" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; background: #f8fafc; max-width: 400px;">
        <h3 style="margin-top: 0; color: #333;">Thống kê hệ thống</h3>
        <p><strong>Số lượng khóa học:</strong> <?php echo esc_html($stats['courses']); ?></p>
        <p><strong>Học viên đã đăng ký:</strong> <?php echo esc_html($stats['students']); ?></p>
        <p><strong>Lượt hoàn thành:</strong> <?php echo esc_html($stats['completed']); ?></p>
    </div>
    <?php
    return ob_get_clean(); // Trả về nội dung và xóa bộ đệm
}
add_shortcode('lp_total_stats', 'lp_total_stats_shortcode');