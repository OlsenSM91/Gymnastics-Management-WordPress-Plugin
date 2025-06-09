<?php
namespace WSM\Includes\Entities;

class Coach {
    public $id;
    public $first_name;
    public $last_name;
    public $phone;
    public $email;

    public function __construct($id = 0, array $data = []) {
        $this->id = $id;
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name  = $data['last_name'] ?? '';
        $this->phone      = $data['phone'] ?? '';
        $this->email      = $data['email'] ?? '';
    }
}
