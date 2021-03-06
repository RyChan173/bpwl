<?php

session_start();
require 'autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
define('CONSUMER_KEY', 'p3EfDwp9NtKlA0sPbxi9p8Cd3'); 	// add your app consumer key between single quotes
define('CONSUMER_SECRET', 'H76fIXwy3PYkOWufkmYSjtcXSRjRtB4OaHTpbovjnADHpQUeW6'); // add your app consumer 																			secret key between single quotes
define('OAUTH_CALLBACK', 'http://localhost/apitwitter/index.php'); // your app callback URL i.e. page 																			you want to load after successful 																			  getting the data
//define('oauth_token', '842987337353052160-LL8z2AHxYRP7lHo8iDaq8cLNzeSu8OP');
//define('oauth_token_secret', '6eZZno5qC6d8E5Gtc9jakmhEgvP07F3MfxOBwJ5ysLm8x');


if ( isset( $_SESSION['twitter_access_token'] ) && $_SESSION['twitter_access_token'] ) { // we have an access token
        $isLoggedIn = true;
    } elseif ( isset( $_GET['oauth_verifier'] ) && isset( $_GET['oauth_token'] ) && isset( $_SESSION['oauth_token'] ) && $_GET['oauth_token'] == $_SESSION['oauth_token'] ) { // coming from twitter callback url
        // setup connection to twitter with request token
        $connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] );

        // get an access token
        $access_token = $connection->oauth( "oauth/access_token", array( "oauth_verifier" => $_GET['oauth_verifier'] ) );

        // save access token to the session
        $_SESSION['twitter_access_token'] = $access_token;

        // user is logged in
        $isLoggedIn = true;
    } else { // not authorized with our app, show login button
        // connect to twitter with our app creds
        $connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );

        // get a request token from twitter
        $request_token = $connection->oauth( 'oauth/request_token', array( 'oauth_callback' => OAUTH_CALLBACK ) );

        // save twitter token info to the session
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        // user is logged in
        $isLoggedIn = false;
    }

    if ( $isLoggedIn ) { // logged in
        // get token info from session
        $oauthToken = $_SESSION['twitter_access_token']['oauth_token'];
        $oauthTokenSecret = $_SESSION['twitter_access_token']['oauth_token_secret'];

        // setup connection
        $connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET, $oauthToken, $oauthTokenSecret );

        // user twitter connection to get user info
        $user = $connection->get( "account/verify_credentials", ['include_email' => 'true'] );

        if ( property_exists( $user, 'errors' ) ) { // errors, clear session so user has to re-authorize with our app
	        $_SESSION = array();
	        header( 'Refresh:0' );
        } else { // display user info in browser
	        ?>
	        <img src="<?php echo $user->profile_image_url; ?>" />
	        <br />
	        <b>Users:</b> <?php echo $user->name; ?>
	        <br />
	        <b>Location:</b> <?php echo $user->location; ?>
	        <br />
	        <b>Twitter Handle:</b> <?php echo $user->screen_name; ?>
	        <br />
	        <b>User Created:</b> <?php echo $user->created_at; ?>
	        <br />
	        <hr />
	        <br />
	        <h3>User Info</h3>
	        <textarea style="height:400px;width:100%"><?php echo print_r( $user, true ); ?></textarea>
	        <?php
        }
    } else {  // not logged in, get and display the login with twitter link
        $url = $connection->url( 'oauth/authorize', array( 'oauth_token' => $request_token['oauth_token'] ) );
        ?>
        <a href="<?php echo $url; ?>"><img src='twitter-login-blue.png' style='margin-left:4%; margin-top: 4%'>
</a>
        <?php
    }
