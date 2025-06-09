<?php
namespace WSM\Includes\Entities;

/**
 * Simple resource entity used for rooms or equipment.
 */
class Resource {
    public $id;
    public $name;
    public $type; // room, equipment
    public $capacity;

    public function __construct($id = 0, array $data = []) {
        $this->id       = $id;
        $this->name     = $data['name'] ?? '';
        $this->type     = $data['type'] ?? 'room';
        $this->capacity = $data['capacity'] ?? 0;
    }
}
