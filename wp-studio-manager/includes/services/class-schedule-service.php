<?php
namespace WSM\Includes\Services;

use WSM\Includes\Entities\Schedule;

/**
 * Service handling creation of schedules and resource assignments.
 */
class Schedule_Service {

    /**
     * Create a new schedule instance and store as custom post.
     */
    public function create_schedule(array $data) {
        $post_id = wp_insert_post([
            'post_type'   => 'gm_schedule',
            'post_title'  => $data['title'] ?? 'Schedule',
            'post_status' => 'publish'
        ]);
        if ($post_id && !is_wp_error($post_id)) {
            foreach (['session_id','resource_id','instructor_id','start_time','end_time','capacity','is_drop_in','recurrence','exceptions','cancelled'] as $key) {
                if (isset($data[$key])) {
                    update_post_meta($post_id, '_gm_'.$key, $data[$key]);
                }
            }
        }
        return $post_id;
    }

    /**
     * Check whether a resource is free for a time range.
     */
    public function resource_available($resource_id, $start, $end) {
        $args = [
            'post_type'  => 'gm_schedule',
            'meta_query' => [
                ['key' => '_gm_resource_id', 'value' => $resource_id],
                ['key' => '_gm_start_time',  'value' => $end,   'compare' => '<'],
                ['key' => '_gm_end_time',    'value' => $start, 'compare' => '>']
            ]
        ];
        $conflicts = get_posts($args);
        return empty($conflicts);
    }

    /**
     * Assign a resource if available.
     */
    public function book_resource($schedule_id, $resource_id, $start, $end) {
        if ($this->resource_available($resource_id, $start, $end)) {
            update_post_meta($schedule_id, '_gm_resource_id', $resource_id);
            update_post_meta($schedule_id, '_gm_start_time', $start);
            update_post_meta($schedule_id, '_gm_end_time', $end);
            return true;
        }
        return false;
    }

    /**
     * Mark schedule as cancelled or provide substitute instructor.
     */
    public function cancel_schedule($schedule_id, $reason = '') {
        update_post_meta($schedule_id, '_gm_cancelled', 1);
        update_post_meta($schedule_id, '_gm_cancel_reason', $reason);
    }

    public function substitute_instructor($schedule_id, $instructor_id) {
        update_post_meta($schedule_id, '_gm_instructor_id', $instructor_id);
    }
}
