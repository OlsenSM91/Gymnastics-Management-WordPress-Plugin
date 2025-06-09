<?php
namespace WSM\Includes\Services;

/**
 * Handles waitlists for schedules when capacity is reached.
 */
class Waitlist_Service {

    public function add_to_waitlist($schedule_id, $participant_id) {
        $list = get_post_meta($schedule_id, '_gm_waitlist', true);
        if (!is_array($list)) {
            $list = [];
        }
        if (!in_array($participant_id, $list)) {
            $list[] = $participant_id;
            update_post_meta($schedule_id, '_gm_waitlist', $list);
            return true;
        }
        return false;
    }

    public function remove_from_waitlist($schedule_id, $participant_id) {
        $list = get_post_meta($schedule_id, '_gm_waitlist', true);
        if (is_array($list)) {
            $index = array_search($participant_id, $list);
            if ($index !== false) {
                unset($list[$index]);
                update_post_meta($schedule_id, '_gm_waitlist', array_values($list));
            }
        }
    }

    public function get_waitlist($schedule_id) {
        $list = get_post_meta($schedule_id, '_gm_waitlist', true);
        return is_array($list) ? $list : [];
    }
}
