<?php

/*
Plugin Name: Word Count
Description: This is my first plugin
Version: 1.0
Author Nirzar
Author URI: https://nirzarbhatt.netlify.app/
Text Domain: wcpdomain
Domain Path: /languages
*/

class WordCountAndTimePlugin
{
    function __construct()
    {
        add_action(
            "admin_menu",
            array(
                $this,
                "adminPage"
            )
        );

        add_action(
            "admin_init",
            array(
                $this,
                "settings"
            )
        );

        add_filter(
            "the_content",
            array(
                $this,
                "if_wrap"
            )
        );

        add_action(
            'init',
            array(
                $this,
                'languages'
            )
        );
    }

    function languages()
    {
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) .'/languages');
    }

    function if_wrap($content)
    {
        if (is_main_query() and is_single() and (get_option('wcp_word_count', 1) or get_option('wcp_char_count', 1) or get_option('wcp_read_time', 1))) 
        {
            return $this->create_html($content);
        }
        return $content;
    }

    function settings()
    {
        add_settings_section(
            'wcp_first_section',
            null,
            null,
            'word-count-settings-page'
        );

        // location
        add_settings_field(
            'wcp_location',
            'Display Location',
            array(
                $this,
                'location_html'
            ),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'wordcountplugin',
            'wcp_location',
            array(
                'sanitize_callback' => array(
                    $this,
                    'sanitize_location'
                ),
                'default' => '0'
            )
        );

        // headline
        add_settings_field(
            'wcp_headline',
            'Headline Text',
            array(
                $this,
                'headline_html'
            ),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'wordcountplugin',
            'wcp_headline',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'Post Statistics'
            )
        );

        // word count
        add_settings_field(
            'wcp_word_count',
            'Word Count',
            array(
                $this,
                'word_count_html'
            ),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'wordcountplugin',
            'wcp_word_count',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        // character count
        add_settings_field(
            'char_count',
            'Character Count',
            array(
                $this,
                'char_count_html'
            ),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'wordcountplugin',
            'wcp_char_count',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        // read time
        add_settings_field(
            'read_time',
            'Read Time',
            array(
                $this,
                'read_time_html'
            ),
            'word-count-settings-page',
            'wcp_first_section'
        );
        register_setting(
            'wordcountplugin',
            'wcp_read_time',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );
    }

    function sanitize_location($input)
    {
        if ($input != '0' and $input != '1') 
        {
            add_settings_error('wcp_location', 'wcp_location_err', 'Display location MUST BE either Beginning or End of the post.');
            return get_option('wcp_location');
        }
        return $input;
    }

    function location_html()
    { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0'); ?>>Beginning of the post</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1'); ?>>End of the post</option>
        </select>
    <?php }

    function headline_html()
    { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')); ?>">
    <?php }

    function word_count_html()
    { ?>
        <input type="checkbox" name="wcp_word_count" value="1" <?php checked(get_option('wcp_word_count'), '1'); ?>>
    <?php }

    function char_count_html()
    { ?>
        <input type="checkbox" name="wcp_char_count" value="1" <?php checked(get_option('wcp_char_count'), '1'); ?>>
    <?php }

    function read_time_html()
    { ?>
        <input type="checkbox" name="wcp_read_time" value="1" <?php checked(get_option('wcp_read_time'), '1'); ?>>
    <?php }

    function adminPage()
    {
        add_options_page(
            'Word Count Settings',
            esc_html__('Word Count', 'wcpdomain'),
            'manage_options',
            'word-count-settings-page',
            array(
                $this,
                "pageHTML"
            )
        );
    }

    function create_html($content)
    {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '<h1><p>';

        // get word count once as word count and read time will both need it
        if (get_option('wcp_word_count', '1') or get_option('wcp_read_time', '1'))
        {
            $word_count = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_word_count', '1') == '1')
        {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $word_count . ' ' . __('words', 'wcpdomain') . '<br>';
        }

        if (get_option('wcp_char_count', '1') == '1')
        {
            $html .= 'This post has ' . strlen(strip_tags($content)) . '  characters.<br>';
        }

        if (get_option('wcp_read_time', '1') == '1')
        {
            $html .= 'This post will take about ' . ceil($word_count / 225) . '  minute(s) to read.<br>';
        }

        $html .= '</p>';
        
        if (get_option('wcp_location', '0') == '0')
        {
            return $html . $content;
        }
        return $content . $html;
    }

    function pageHTML()
    { ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>

            <form action="options.php" method="POST">
                <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
                ?>
            </form>
        </div>
    <?php }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();