<?php
namespace WSM\Includes\Industry_Configs;

class Creative_Config {
    /**
     * Return the configuration array for creative studios.
     *
     * @return array
     */
    public static function get() {
        return [
            'participant_label' => 'Artist',
            'session_label'     => 'Workshop',
            'instructor_label'  => 'Instructor',
            'features'          => [
                'workshop_scheduling',
                'supply_lists',
                'project_portfolios',
                'certificate_tracking',
            ],
            'required_fields'   => ['experience_level', 'materials_owned'],
            'integrations'      => ['portfolio_sites', 'social_sharing'],
            'custom_fields'     => [
                'participant' => [
                    'experience_level' => ['label' => 'Experience Level', 'type' => 'text'],
                    'materials_owned'  => ['label' => 'Materials Owned', 'type' => 'textarea'],
                ],
                'session' => [
                    'supplies_needed' => ['label' => 'Supplies Needed', 'type' => 'textarea'],
                ],
            ],
        ];
    }
}
