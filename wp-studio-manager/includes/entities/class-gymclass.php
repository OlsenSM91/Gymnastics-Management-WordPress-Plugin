<?php
namespace WSM\Includes\Entities;

class GymClass {
    public $id;
    public $title;
    public $level_id;
    public $available_slots;
    public $price;

    public function __construct($id = 0, array $data = []) {
        $this->id             = $id;
        $this->title          = $data['title'] ?? '';
        $this->level_id       = $data['level_id'] ?? 0;
        $this->available_slots = $data['available_slots'] ?? 0;
        $this->price          = $data['price'] ?? 0;
    }
}
