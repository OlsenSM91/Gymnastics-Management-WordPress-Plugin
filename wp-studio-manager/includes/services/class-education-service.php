<?php
namespace WSM\Includes\Services;

/**
 * Education center specific utilities.
 */
class Education_Service {

    public function add_skill_assessment($participant_id, array $data) {
        $assessments = get_user_meta($participant_id, '_gm_skill_assessments', true);
        if (!is_array($assessments)) {
            $assessments = [];
        }
        $data['date'] = $data['date'] ?? current_time('mysql');
        $assessments[] = $data;
        update_user_meta($participant_id, '_gm_skill_assessments', $assessments);
    }

    public function log_practice_time($participant_id, $minutes) {
        $logs = get_user_meta($participant_id, '_gm_practice_log', true);
        if (!is_array($logs)) {
            $logs = [];
        }
        $logs[] = ['date' => current_time('mysql'), 'minutes' => $minutes];
        update_user_meta($participant_id, '_gm_practice_log', $logs);
    }

    public function create_recital($title, $date) {
        return wp_insert_post([
            'post_type'   => 'gm_recital',
            'post_title'  => $title,
            'post_status' => 'publish',
            'meta_input'  => ['_gm_recital_date' => $date]
        ]);
    }

    public function send_parent_message($parent_id, $subject, $content) {
        return wp_insert_post([
            'post_type'   => 'gm_parent_message',
            'post_title'  => $subject,
            'post_content'=> $content,
            'post_status' => 'publish',
            'post_author' => $parent_id
        ]);
    }
}
