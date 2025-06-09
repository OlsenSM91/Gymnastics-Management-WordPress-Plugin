<?php
namespace WSM\Includes\Industry_Configs;

class Wellness_Config {
    public static function get() {
        return [
            'participant_label' => 'Client',
            'session_label'     => 'Session',
            'instructor_label'  => 'Practitioner',
            'features'          => [],
            'required_fields'   => ['health_conditions'],
            'integrations'      => [],
            'custom_fields'     => [
                'participant' => [
                    'health_conditions' => ['label' => 'Health Conditions', 'type' => 'textarea'],
                ],
            ],
        ];
    }
}
