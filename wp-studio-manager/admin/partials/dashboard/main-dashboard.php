<?php
use WSM\Includes\Industry_Configs\Industry_Config;

$participant_label = Industry_Config::get_label('participant_label', true);
$session_label     = Industry_Config::get_label('session_label', true);
$instructor_label  = Industry_Config::get_label('instructor_label', true);

$participant_count = wp_count_posts('gm_parent')->publish;
$session_count     = wp_count_posts('gm_class')->publish;
$instructor_count  = wp_count_posts('gm_coach')->publish;

$quick_actions = [
    [
        'label' => sprintf(__('Add %s', 'wsm'), $participant_label),
        'url'   => admin_url('admin.php?page=gym-parents'),
    ],
    [
        'label' => sprintf(__('Add %s', 'wsm'), $session_label),
        'url'   => admin_url('admin.php?page=gym-classes'),
    ],
    [
        'label' => sprintf(__('Add %s', 'wsm'), $instructor_label),
        'url'   => admin_url('admin.php?page=gym-coaches'),
    ],
];
?>
<div class="wsm-metrics">
    <div class="wsm-metric">
        <strong><?php echo esc_html($participant_count); ?></strong>
        <span><?php echo esc_html($participant_label); ?></span>
    </div>
    <div class="wsm-metric">
        <strong><?php echo esc_html($session_count); ?></strong>
        <span><?php echo esc_html($session_label); ?></span>
    </div>
    <div class="wsm-metric">
        <strong><?php echo esc_html($instructor_count); ?></strong>
        <span><?php echo esc_html($instructor_label); ?></span>
    </div>
</div>

<h2><?php esc_html_e('Quick Actions', 'wsm'); ?></h2>
<div class="wsm-actions">
    <?php foreach ($quick_actions as $action) : ?>
        <a href="<?php echo esc_url($action['url']); ?>" class="button button-primary wsm-action"><?php echo esc_html($action['label']); ?></a>
    <?php endforeach; ?>
</div>
