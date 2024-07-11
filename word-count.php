<?php

/*
Plugin Name: Word Count
Description: This is my first plugin
Version: 1.0
Author Nirzar
Author URI: https://nirzarbhatt.netlify.app/
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
    }

    function settings()
    {
        register_setting(
            'wordcountplugin',
            'wcp_location',
            array(
                'sanitize_callback' => 'sanatize_text_field',
                'default' => '0'
            )
        );
    }

    function adminPage()
    {
        add_options_page(
            'Word Count Settings',
            'Word Count',
            'manage_options',
            'word-count-settings-page',
            array(
                $this,
                "pageHTML"
            )
        );
    }

    function pageHTML()
    { ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>
        </div>
    <?php }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();