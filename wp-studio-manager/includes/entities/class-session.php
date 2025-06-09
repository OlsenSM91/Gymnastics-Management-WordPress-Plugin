<?php
namespace WSM\Includes\Entities;

/**
 * Represents a session template which can have many schedules.
 */
class Session {
    public $id;
    public $title;
    public $description;

    public function __construct($id = 0, array $data = []) {
        $this->id          = $id;
        $this->title       = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
    }
}
