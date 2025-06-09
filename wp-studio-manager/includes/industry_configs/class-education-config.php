<?php
namespace WSM\Includes\Industry_Configs;

class Education_Config {
    /**
     * Return the configuration array for educational centers.
     *
     * @return array
     */
    public static function get() {
        return [
            'participant_label' => 'Student',
            'session_label'     => 'Lesson',
            'instructor_label'  => 'Teacher',
            'features'          => [
                'relationship_management',
                'lesson_recurrence',
                'progress_reports',
                'skill_tracking',
                'assignment_management',
                'homework_management',
            ],
            'required_fields'   => ['skill_level', 'instrument', 'parent_contact'],
            'integrations'      => ['practice_tracking', 'sheet_music'],
            'custom_fields'     => [
                'participant' => [
                    'skill_level'    => ['label' => 'Skill Level', 'type' => 'text', 'required' => true],
                    'instrument'     => ['label' => 'Instrument', 'type' => 'text'],
                ],
                'session' => [
                    'room'   => ['label' => 'Room', 'type' => 'text'],
                ],
            ],
        ];
    }
}
