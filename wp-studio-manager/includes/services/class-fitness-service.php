<?php
namespace WSM\Includes\Services;

/**
 * Fitness specific utilities like body composition tracking.
 */
class Fitness_Service {

    public function add_body_composition($athlete_id, array $metrics) {
        $entries = get_user_meta($athlete_id, '_gm_body_comp', true);
        if (!is_array($entries)) {
            $entries = [];
        }
        $metrics['date'] = $metrics['date'] ?? current_time('mysql');
        $entries[] = $metrics;
        update_user_meta($athlete_id, '_gm_body_comp', $entries);
    }

    public function assign_workout_plan($athlete_id, $plan_id) {
        update_user_meta($athlete_id, '_gm_workout_plan', $plan_id);
    }

    public function add_progress_photo($athlete_id, $attachment_id) {
        $photos = get_user_meta($athlete_id, '_gm_progress_photos', true);
        if (!is_array($photos)) {
            $photos = [];
        }
        $photos[] = $attachment_id;
        update_user_meta($athlete_id, '_gm_progress_photos', $photos);
    }

    public function freeze_membership($athlete_id, $start, $end) {
        $freezes = get_user_meta($athlete_id, '_gm_membership_freezes', true);
        if (!is_array($freezes)) {
            $freezes = [];
        }
        $freezes[] = ['start' => $start, 'end' => $end];
        update_user_meta($athlete_id, '_gm_membership_freezes', $freezes);
    }
}
