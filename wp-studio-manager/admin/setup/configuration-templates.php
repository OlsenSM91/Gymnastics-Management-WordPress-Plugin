<?php
use WSM\Includes\Industry_Configs\Fitness_Config;
use WSM\Includes\Industry_Configs\Education_Config;
use WSM\Includes\Industry_Configs\Creative_Config;
use WSM\Includes\Industry_Configs\Sports_Config;
use WSM\Includes\Industry_Configs\Wellness_Config;

if (!function_exists('wsm_get_industry_templates')) {
    /**
     * Return the default configuration templates for all industries.
     *
     * @return array
     */
    function wsm_get_industry_templates() {
        return [
            'fitness'    => Fitness_Config::get(),
            'education'  => Education_Config::get(),
            'creative'   => Creative_Config::get(),
            'sports'     => Sports_Config::get(),
            'wellness'   => Wellness_Config::get(),
        ];
    }
}
