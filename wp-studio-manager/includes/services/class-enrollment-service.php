<?php
namespace WSM\Includes\Services;

use WSM\Includes\Repositories\GymClass_Repository;

class Enrollment_Service {
    protected $classes;

    public function __construct() {
        $this->classes = new GymClass_Repository();
    }

    public function assign_athlete($class_id, $athlete_id) {
        $athletes = get_post_meta($class_id, '_gm_class_athletes', true);
        if (!is_array($athletes)) {
            $athletes = [];
        }
        if (!in_array($athlete_id, $athletes)) {
            $athletes[] = $athlete_id;
            update_post_meta($class_id, '_gm_class_athletes', $athletes);
        }
    }
}
