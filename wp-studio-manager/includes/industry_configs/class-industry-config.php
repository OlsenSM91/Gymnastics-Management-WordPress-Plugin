<?php
namespace WSM\Includes\Industry_Configs;

class Industry_Config {
    private static $configs = [
        'fitness' => [
            'participant_label' => 'Member',
            'session_label'     => 'Class',
            'instructor_label'  => 'Trainer',
            'features'          => ['body_composition', 'progress_photos', 'workout_plans'],
            'required_fields'   => ['emergency_contact', 'health_conditions'],
            'integrations'      => ['heart_rate_monitors', 'nutrition_tracking'],
        ],
        'education' => [
            'participant_label' => 'Student',
            'session_label'     => 'Lesson',
            'instructor_label'  => 'Teacher',
            'features'          => ['progress_tracking', 'assignments', 'recitals'],
            'required_fields'   => ['skill_level', 'instrument', 'parent_contact'],
            'integrations'      => ['practice_tracking', 'sheet_music'],
        ],
        'creative' => [
            'participant_label' => 'Artist',
            'session_label'     => 'Workshop',
            'instructor_label'  => 'Instructor',
            'features'          => ['portfolio', 'supply_lists', 'project_gallery'],
            'required_fields'   => ['experience_level', 'materials_owned'],
            'integrations'      => ['portfolio_sites', 'social_sharing'],
        ],
    ];

    public static function get_config($industry = null) {
        if (!$industry) {
            $industry = get_option('wsm_industry', 'fitness');
        }
        return self::$configs[$industry] ?? self::$configs['fitness'];
    }

    public static function get_label($key, $plural = false) {
        $config = self::get_config();
        if (!isset($config[$key])) {
            return '';
        }
        $label = $config[$key];
        if ($plural) {
            return $label . 's';
        }
        return $label;
    }
}
