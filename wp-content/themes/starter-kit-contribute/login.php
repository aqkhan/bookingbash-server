<?php
/* Template Name: Facebook Login */
session_start();
require_once __DIR__ . '/vendor/autoload.php';

$fb = new Facebook\Facebook([
'app_id' => 1674992816157003, // Replace {app-id} with your app id
'app_secret' => '0a7e33084a4a9f4cffc1c803bc211b8e',
'default_graph_version' => 'v2.2',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('http://bookingbash.nnj54dnmv6.us-west-2.elasticbeanstalk.com/server/wp-content/themes/starter-kit-contribute/fb-callback.php', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
