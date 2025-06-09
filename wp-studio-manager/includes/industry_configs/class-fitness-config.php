<?php
namespace WSM\Includes\Industry_Configs;

class Fitness_Config {
    /**
     * Return the configuration array for fitness studios.
     *
     * @return array
     */
    public static function get() {
        return [
            'participant_label' => 'Member',
            'session_label'     => 'Class',
            'instructor_label'  => 'Trainer',
            'features'          => [
                'health_tracking',
                'capacity_limits',
                'membership_packages',
                'drop_ins',
                'progress_tracking',
                'body_composition',
            ],
            'required_fields'   => ['emergency_contact', 'health_conditions'],
            'integrations'      => ['heart_rate_monitors', 'nutrition_tracking'],
            'custom_fields'     => [
                'participant' => [
                    'emergency_contact'  => ['label' => 'Emergency Contact', 'type' => 'text', 'required' => true],
                    'health_conditions'  => ['label' => 'Health Conditions', 'type' => 'textarea'],
                ],
                'session' => [
                    'difficulty' => ['label' => 'Difficulty', 'type' => 'select', 'options' => ['Beginner','Intermediate','Advanced']],
                ],
                'instructor' => [
                    'certifications' => ['label' => 'Certifications', 'type' => 'textarea'],
                ],
            ],
        ];
    }
}
