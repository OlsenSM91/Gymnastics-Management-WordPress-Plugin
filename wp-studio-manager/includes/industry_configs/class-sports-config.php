<?php
namespace WSM\Includes\Industry_Configs;

class Sports_Config {
    public static function get() {
        return [
            'participant_label' => 'Athlete',
            'session_label'     => 'Practice',
            'instructor_label'  => 'Coach',
            'features'          => ['skill_tracking'],
            'required_fields'   => ['emergency_contact'],
            'integrations'      => [],
            'custom_fields'     => [
                'participant' => [
                    'emergency_contact' => ['label' => 'Emergency Contact', 'type' => 'text', 'required' => true],
                ],
            ],
        ];
    }
}
