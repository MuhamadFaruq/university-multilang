<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\Widgets;

use WP_Widget;
use UniversityMultilang\Frontend\LanguageSwitcher;

class LanguageSwitcherWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'uml_language_switcher_widget',
            'University Multilang - Switcher',
            ['description' => 'Display language switcher dropdown or list.']
        );
    }

    public function widget($args, $instance): void
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $type = $instance['type'] ?? 'dropdown';

        if (function_exists('uml_language_switcher')) {
            echo uml_language_switcher(['type' => $type]);
        }

        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = $instance['title'] ?? 'Languages';
        $type = $instance['type'] ?? 'dropdown';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('type')); ?>">Display Type:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
                <option value="dropdown" <?php selected($type, 'dropdown'); ?>>Dropdown</option>
                <option value="list" <?php selected($type, 'list'); ?>>List</option>
            </select>
        </p>
        <?php
    }

    public function update($newInstance, $oldInstance): array
    {
        $instance = [];
        $instance['title'] = (!empty($newInstance['title'])) ? sanitize_text_field($newInstance['title']) : '';
        $instance['type'] = (!empty($newInstance['type'])) ? sanitize_text_field($newInstance['type']) : 'dropdown';
        return $instance;
    }
}
