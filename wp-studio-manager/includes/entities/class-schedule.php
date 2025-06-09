<?php
namespace WSM\Includes\Entities;

/**
 * Represents a single scheduled session instance.
 */
class Schedule {
    public $id;
    public $session_id;
    public $resource_id;
    public $instructor_id;
    public $start_time;
    public $end_time;
    public $capacity;
    public $is_drop_in;
    public $recurrence; // string e.g. rrule
    public $exceptions = [];
    public $cancelled = false;

    public function __construct($id = 0, array $data = []) {
        $this->id           = $id;
        $this->session_id   = $data['session_id'] ?? 0;
        $this->resource_id  = $data['resource_id'] ?? 0;
        $this->instructor_id= $data['instructor_id'] ?? 0;
        $this->start_time   = $data['start_time'] ?? '';
        $this->end_time     = $data['end_time'] ?? '';
        $this->capacity     = $data['capacity'] ?? 0;
        $this->is_drop_in   = $data['is_drop_in'] ?? false;
        $this->recurrence   = $data['recurrence'] ?? '';
        $this->exceptions   = $data['exceptions'] ?? [];
        $this->cancelled    = $data['cancelled'] ?? false;
    }
}
