<?php
use WSM\Includes\Industry_Configs\Industry_Config;
$industry = Industry_Config::get_config();
?>
<h2><?php esc_html_e('Industry Overview', 'wsm'); ?></h2>
<ul class="wsm-industry-features">
    <?php if (!empty($industry['features'])) : ?>
        <?php foreach ($industry['features'] as $feature) : ?>
            <li><?php echo esc_html(ucwords(str_replace('_', ' ', $feature))); ?></li>
        <?php endforeach; ?>
    <?php else : ?>
        <li><?php esc_html_e('No industry features configured.', 'wsm'); ?></li>
    <?php endif; ?>
</ul>
