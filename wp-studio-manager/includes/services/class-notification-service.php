<?php
namespace WSM\Includes\Services;

class Notification_Service {
    public function send_email($to, $subject, $message) {
        wp_mail($to, $subject, $message);
    }
}
