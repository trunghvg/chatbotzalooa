<?php

/*
 * App constants
 */
defined('APPPATH') || exit('No direct script access allowed.');

// App version
define('APP_VERSION', '1.0.0');

// Zalo event types
define('ZALO_EVENT_TEXT',    'user_send_text');
define('ZALO_EVENT_IMAGE',   'user_send_image');
define('ZALO_EVENT_STICKER', 'user_send_sticker');
define('ZALO_EVENT_AUDIO',   'user_send_audio');
define('ZALO_EVENT_VIDEO',   'user_send_video');
define('ZALO_EVENT_FILE',    'user_send_file');
define('ZALO_EVENT_FOLLOW',  'follow');
define('ZALO_EVENT_UNFOLLOW','unfollow');
