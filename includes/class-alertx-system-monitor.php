<?php
/**
 * System Monitor Class
 *
 * Monitors system health including memory, disk space, and database
 *
 * @package AlertX
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlertX_System_Monitor {

    /**
     * Get memory usage
     *
     * @return array Memory usage data
     */
    public static function get_memory_usage() {
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = self::convert_to_bytes($memory_limit);
        $current_memory = memory_get_usage(true);
        $percentage = ($current_memory / $memory_limit_bytes) * 100;

        return array(
            'limit' => $memory_limit,
            'limit_bytes' => $memory_limit_bytes,
            'current' => $current_memory,
            'current_formatted' => size_format($current_memory),
            'percentage' => round($percentage, 2),
            'status' => self::get_status($percentage, 80)
        );
    }

    /**
     * Get disk space usage
     *
     * @return array Disk usage data
     */
    public static function get_disk_usage() {
        $upload_dir = wp_upload_dir();
        $disk_free = @disk_free_space($upload_dir['basedir']);
        $disk_total = @disk_total_space($upload_dir['basedir']);

        if ($disk_free === false || $disk_total === false) {
            return array(
                'status' => 'unknown',
                'message' => __('Unable to determine disk space', 'alertx')
            );
        }

        $disk_used = $disk_total - $disk_free;
        $percentage = ($disk_used / $disk_total) * 100;
        $settings = get_option('alertx_settings');
        $threshold = isset($settings['storage_threshold']) ? (int)$settings['storage_threshold'] : 75;

        return array(
            'total' => $disk_total,
            'total_formatted' => size_format($disk_total),
            'used' => $disk_used,
            'used_formatted' => size_format($disk_used),
            'free' => $disk_free,
            'free_formatted' => size_format($disk_free),
            'percentage' => round($percentage, 2),
            'status' => self::get_status($percentage, $threshold)
        );
    }

    /**
     * Get database size
     *
     * @return array Database size data
     */
    public static function get_database_size() {
        global $wpdb;

        $database_size = 0;
        $tables = array();

        $results = $wpdb->get_results("
            SELECT
                table_name AS 'table',
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size'
            FROM information_schema.TABLES
            WHERE table_schema = '" . DB_NAME . "'
            ORDER BY (data_length + index_length) DESC
        ");

        if ($results) {
            foreach ($results as $row) {
                $database_size += $row->size;
                $tables[] = array(
                    'name' => $row->table,
                    'size' => $row->size
                );
            }
        }

        // Get top 5 largest tables
        $top_tables = array_slice($tables, 0, 5);

        return array(
            'total_size' => round($database_size, 2),
            'total_size_formatted' => size_format($database_size * 1024 * 1024),
            'table_count' => count($tables),
            'top_tables' => $top_tables,
            'status' => $database_size > 100 ? 'warning' : 'good'
        );
    }

    /**
     * Convert size string to bytes
     *
     * @param string $size_str Size string (e.g., '256M')
     * @return int Size in bytes
     */
    private static function convert_to_bytes($size_str) {
        $size_str = trim($size_str);
        $last = strtolower($size_str[strlen($size_str)-1]);
        $size_str = (int)$size_str;

        switch($last) {
            case 'g':
                $size_str *= 1024;
            case 'm':
                $size_str *= 1024;
            case 'k':
                $size_str *= 1024;
        }

        return $size_str;
    }

    /**
     * Get status based on percentage
     *
     * @param float $percentage Current percentage
     * @param int $threshold Warning threshold
     * @return string Status (good, warning, critical)
     */
    private static function get_status($percentage, $threshold) {
        if ($percentage >= $threshold) {
            return 'critical';
        } elseif ($percentage >= ($threshold - 10)) {
            return 'warning';
        } else {
            return 'good';
        }
    }

    /**
     * Get all system data
     *
     * @return array All system monitoring data
     */
    public static function get_all_data() {
        // Check cache
        $cached = get_transient('alertx_system_data');
        if ($cached !== false) {
            return $cached;
        }

        $data = array(
            'memory' => self::get_memory_usage(),
            'disk' => self::get_disk_usage(),
            'database' => self::get_database_size()
        );

        // Cache for 5 minutes
        set_transient('alertx_system_data', $data, 5 * MINUTE_IN_SECONDS);

        return $data;
    }
}
