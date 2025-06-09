<?php
namespace WSM\Includes\Entities;

class Athlete {
    public $id;
    public $first_name;
    public $last_name;
    public $parent_id;

    public function __construct($id = 0, array $data = []) {
        $this->id = $id;
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name  = $data['last_name'] ?? '';
        $this->parent_id  = $data['parent_id'] ?? 0;
    }
}
