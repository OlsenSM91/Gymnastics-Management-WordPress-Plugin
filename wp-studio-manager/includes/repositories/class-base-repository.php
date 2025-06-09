<?php
namespace WSM\Includes\Repositories;

abstract class Base_Repository {
    protected $post_type;

    public function get($id) {
        return get_post($id);
    }

    public function all(array $args = []) {
        $defaults = [
            'post_type'   => $this->post_type,
            'post_status' => 'publish',
            'numberposts' => -1,
        ];
        return get_posts(array_merge($defaults, $args));
    }

    public function create(array $data) {
        $data['post_type'] = $this->post_type;
        $data['post_status'] = 'publish';
        return wp_insert_post($data);
    }

    public function update($id, array $data) {
        $data['ID'] = $id;
        return wp_update_post($data);
    }

    public function delete($id) {
        return wp_delete_post($id, true);
    }
}
