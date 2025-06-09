<?php
namespace WSM\Includes\Industry_Configs;

class Industry_Config {
    private static $configs = null;

    private static function load_configs() {
        if (self::$configs !== null) {
            return;
        }

        self::$configs = [
            'fitness'    => Fitness_Config::get(),
            'education'  => Education_Config::get(),
            'creative'   => Creative_Config::get(),
            'sports'     => Sports_Config::get(),
            'wellness'   => Wellness_Config::get(),
            'gymnastics' => [
            'participant_label' => 'Athlete',
            'session_label'     => 'Practice',
            'instructor_label'  => 'Coach',
            'features'          => ['skill_tracking', 'meet_management', 'routine_videos'],
            'required_fields'   => ['usag_number', 'emergency_contact', 'medical_conditions'],
            'integrations'      => ['scoring_systems', 'competition_registration'],
            'custom_fields'     => [
                'participant' => [
                    'usag_number'       => ['label' => 'USAG Number', 'type' => 'text', 'required' => true],
                    'medical_conditions' => ['label' => 'Medical Conditions', 'type' => 'textarea'],
                ],
            ],
            ],
        ];
    }

    public static function get_config($industry = null) {
        self::load_configs();
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
