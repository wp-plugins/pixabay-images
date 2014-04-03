<?php

#~~~~~~~~~~~~ imported from pluggable.php ~~~~~~~~~~~

if ( !function_exists('wp_get_current_user') ) :
/**
 * Retrieve the current user object.
 *
 * @since 2.0.3
 *
 * @return WP_User Current user WP_User object
 */
 
function wp_get_current_user() {
	global $current_user;

	get_currentuserinfo();

	return $current_user;
}
endif;

if ( !function_exists('get_currentuserinfo') ) :
/**
 * Populate global variables with information about the currently logged in user.
 *
 * Will set the current user, if the current user is not set. The current user
 * will be set to the logged in person. If no user is logged in, then it will
 * set the current user to 0, which is invalid and won't have any permissions.
 *
 * @since 0.71
 * @uses $current_user Checks if the current user is set
 * @uses wp_validate_auth_cookie() Retrieves current logged in user.
 *
 * @return bool|null False on XMLRPC Request and invalid auth cookie. Null when current user set
 */
function get_currentuserinfo() {
	global $current_user;

	if ( ! empty( $current_user ) ) {
		if ( $current_user instanceof WP_User )
			return;

		// Upgrade stdClass to WP_User
		if ( is_object( $current_user ) && isset( $current_user->ID ) ) {
			$cur_id = $current_user->ID;
			$current_user = null;
			wp_set_current_user( $cur_id );
			return;
		}

		// $current_user has a junk value. Force to WP_User with ID 0.
		$current_user = null;
		wp_set_current_user( 0 );
		return false;
	}

	if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
		wp_set_current_user( 0 );
		return false;
	}

	if ( ! $user = wp_validate_auth_cookie() ) {
		 if ( is_blog_admin() || is_network_admin() || empty( $_COOKIE[LOGGED_IN_COOKIE] ) || !$user = wp_validate_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' ) ) {
		 	wp_set_current_user( 0 );
		 	return false;
		 }
	}

	wp_set_current_user( $user );
}
endif;

if ( !function_exists('wp_validate_auth_cookie') ) :
/**
 * Validates authentication cookie.
 *
 * The checks include making sure that the authentication cookie is set and
 * pulling in the contents (if $cookie is not used).
 *
 * Makes sure the cookie is not expired. Verifies the hash in cookie is what is
 * should be and compares the two.
 *
 * @since 2.5
 *
 * @param string $cookie Optional. If used, will validate contents instead of cookie's
 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
 * @return bool|int False if invalid cookie, User ID if valid.
 */
function wp_validate_auth_cookie($cookie = '', $scheme = '') {
	if ( ! $cookie_elements = wp_parse_auth_cookie($cookie, $scheme) ) {
		do_action('auth_cookie_malformed', $cookie, $scheme);
		return false;
	}

	extract($cookie_elements, EXTR_OVERWRITE);

	$expired = $expiration;

	// Allow a grace period for POST and AJAX requests
	if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] )
		$expired += HOUR_IN_SECONDS;

	// Quick check to see if an honest cookie has expired
	if ( $expired < time() ) {
		do_action('auth_cookie_expired', $cookie_elements);
		return false;
	}

	$user = get_user_by('login', $username);
	if ( ! $user ) {
		do_action('auth_cookie_bad_username', $cookie_elements);
		return false;
	}

	$pass_frag = substr($user->user_pass, 8, 4);

	$key = wp_hash($username . $pass_frag . '|' . $expiration, $scheme);
	$hash = hash_hmac('md5', $username . '|' . $expiration, $key);

	if ( $hmac != $hash ) {
		do_action('auth_cookie_bad_hash', $cookie_elements);
		return false;
	}

	if ( $expiration < time() ) // AJAX/POST grace period set above
		$GLOBALS['login_grace_period'] = 1;

	do_action('auth_cookie_valid', $cookie_elements, $user);

	return $user->ID;
}
endif;

if ( !function_exists('wp_parse_auth_cookie') ) :
/**
 * Parse a cookie into its components
 *
 * @since 2.7
 *
 * @param string $cookie
 * @param string $scheme Optional. The cookie scheme to use: auth, secure_auth, or logged_in
 * @return array Authentication cookie components
 */
function wp_parse_auth_cookie($cookie = '', $scheme = '') {
	if ( empty($cookie) ) {
		switch ($scheme){
			case 'auth':
				$cookie_name = AUTH_COOKIE;
				break;
			case 'secure_auth':
				$cookie_name = SECURE_AUTH_COOKIE;
				break;
			case "logged_in":
				$cookie_name = LOGGED_IN_COOKIE;
				break;
			default:
				if ( is_ssl() ) {
					$cookie_name = SECURE_AUTH_COOKIE;
					$scheme = 'secure_auth';
				} else {
					$cookie_name = AUTH_COOKIE;
					$scheme = 'auth';
				}
	    }

		if ( empty($_COOKIE[$cookie_name]) )
			return false;
		$cookie = $_COOKIE[$cookie_name];
	}

	$cookie_elements = explode('|', $cookie);
	if ( count($cookie_elements) != 3 )
		return false;

	list($username, $expiration, $hmac) = $cookie_elements;

	return compact('username', 'expiration', 'hmac', 'scheme');
}
endif;

if ( !function_exists('get_userdata') ) :
/**
 * Retrieve user info by user ID.
 *
 * @since 0.71
 *
 * @param int $user_id User ID
 * @return bool|object False on failure, WP_User object on success
 */
function get_userdata( $user_id ) {
	return get_user_by( 'id', $user_id );
}
endif;

if ( !function_exists('get_user_by') ) :
/**
 * Retrieve user info by a given field
 *
 * @since 2.8.0
 *
 * @param string $field The field to retrieve the user with. id | slug | email | login
 * @param int|string $value A value for $field. A user ID, slug, email address, or login name.
 * @return bool|object False on failure, WP_User object on success
 */
function get_user_by( $field, $value ) {
	$userdata = WP_User::get_data_by( $field, $value );

	if ( !$userdata )
		return false;

	$user = new WP_User;
	$user->init( $userdata );

	return $user;
}
endif;

if ( !function_exists('wp_hash') ) :
/**
 * Get hash of given string.
 *
 * @since 2.0.3
 * @uses wp_salt() Get WordPress salt
 *
 * @param string $data Plain text to hash
 * @return string Hash of $data
 */
function wp_hash($data, $scheme = 'auth') {
	$salt = wp_salt($scheme);

	return hash_hmac('md5', $data, $salt);
}
endif;

if ( !function_exists('wp_salt') ) :
/**
 * Get salt to add to hashes.
 *
 * Salts are created using secret keys. Secret keys are located in two places:
 * in the database and in the wp-config.php file. The secret key in the database
 * is randomly generated and will be appended to the secret keys in wp-config.php.
 *
 * The secret keys in wp-config.php should be updated to strong, random keys to maximize
 * security. Below is an example of how the secret key constants are defined.
 * Do not paste this example directly into wp-config.php. Instead, have a
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ secret key created} just
 * for you.
 *
 * <code>
 * define('AUTH_KEY',         ' Xakm<o xQy rw4EMsLKM-?!T+,PFF})H4lzcW57AF0U@N@< >M%G4Yt>f`z]MON');
 * define('SECURE_AUTH_KEY',  'LzJ}op]mr|6+![P}Ak:uNdJCJZd>(Hx.-Mh#Tz)pCIU#uGEnfFz|f ;;eU%/U^O~');
 * define('LOGGED_IN_KEY',    '|i|Ux`9<p-h$aFf(qnT:sDO:D1P^wZ$$/Ra@miTJi9G;ddp_<q}6H1)o|a +&JCM');
 * define('NONCE_KEY',        '%:R{[P|,s.KuMltH5}cI;/k<Gx~j!f0I)m_sIyu+&NJZ)-iO>z7X>QYR0Z_XnZ@|');
 * define('AUTH_SALT',        'eZyT)-Naw]F8CwA*VaW#q*|.)g@o}||wf~@C-YSt}(dh_r6EbI#A,y|nU2{B#JBW');
 * define('SECURE_AUTH_SALT', '!=oLUTXh,QW=H `}`L|9/^4-3 STz},T(w}W<I`.JjPi)<Bmf1v,HpGe}T1:Xt7n');
 * define('LOGGED_IN_SALT',   '+XSqHc;@Q*K_b|Z?NC[3H!!EONbh.n<+=uKR:>*c(u`g~EJBf#8u#R{mUEZrozmm');
 * define('NONCE_SALT',       'h`GXHhD>SLWVfg1(1(N{;.V!MoE(SfbA_ksP@&`+AycHcAV$+?@3q+rxV{%^VyKT');
 * </code>
 *
 * Salting passwords helps against tools which has stored hashed values of
 * common dictionary strings. The added values makes it harder to crack.
 *
 * @since 2.5
 *
 * @link https://api.wordpress.org/secret-key/1.1/salt/ Create secrets for wp-config.php
 *
 * @param string $scheme Authentication scheme (auth, secure_auth, logged_in, nonce)
 * @return string Salt value
 */
function wp_salt( $scheme = 'auth' ) {
	static $cached_salts = array();
	if ( isset( $cached_salts[ $scheme ] ) )
		return apply_filters( 'salt', $cached_salts[ $scheme ], $scheme );

	static $duplicated_keys;
	if ( null === $duplicated_keys ) {
		$duplicated_keys = array( 'put your unique phrase here' => true );
		foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE', 'SECRET' ) as $first ) {
			foreach ( array( 'KEY', 'SALT' ) as $second ) {
				if ( ! defined( "{$first}_{$second}" ) )
					continue;
				$value = constant( "{$first}_{$second}" );
				$duplicated_keys[ $value ] = isset( $duplicated_keys[ $value ] );
			}
		}
	}

	$key = $salt = '';
	if ( defined( 'SECRET_KEY' ) && SECRET_KEY && empty( $duplicated_keys[ SECRET_KEY ] ) )
		$key = SECRET_KEY;
	if ( 'auth' == $scheme && defined( 'SECRET_SALT' ) && SECRET_SALT && empty( $duplicated_keys[ SECRET_SALT ] ) )
		$salt = SECRET_SALT;

	if ( in_array( $scheme, array( 'auth', 'secure_auth', 'logged_in', 'nonce' ) ) ) {
		foreach ( array( 'key', 'salt' ) as $type ) {
			$const = strtoupper( "{$scheme}_{$type}" );
			if ( defined( $const ) && constant( $const ) && empty( $duplicated_keys[ constant( $const ) ] ) ) {
				$$type = constant( $const );
			} elseif ( ! $$type ) {
				$$type = get_site_option( "{$scheme}_{$type}" );
				if ( ! $$type ) {
					$$type = wp_generate_password( 64, true, true );
					update_site_option( "{$scheme}_{$type}", $$type );
				}
			}
		}
	} else {
		if ( ! $key ) {
			$key = get_site_option( 'secret_key' );
			if ( ! $key ) {
				$key = wp_generate_password( 64, true, true );
				update_site_option( 'secret_key', $key );
			}
		}
		$salt = hash_hmac( 'md5', $scheme, $key );
	}

	$cached_salts[ $scheme ] = $key . $salt;
	return apply_filters( 'salt', $cached_salts[ $scheme ], $scheme );
}
endif;

if ( !function_exists('wp_set_current_user') ) :
/**
 * Changes the current user by ID or name.
 *
 * Set $id to null and specify a name if you do not know a user's ID.
 *
 * Some WordPress functionality is based on the current user and not based on
 * the signed in user. Therefore, it opens the ability to edit and perform
 * actions on users who aren't signed in.
 *
 * @since 2.0.3
 * @global object $current_user The current user object which holds the user data.
 * @uses do_action() Calls 'set_current_user' hook after setting the current user.
 *
 * @param int $id User ID
 * @param string $name User's username
 * @return WP_User Current user User object
 */
function wp_set_current_user($id, $name = '') {
	global $current_user;

	if ( isset( $current_user ) && ( $current_user instanceof WP_User ) && ( $id == $current_user->ID ) )
		return $current_user;

	$current_user = new WP_User( $id, $name );

	setup_userdata( $current_user->ID );

	do_action('set_current_user');

	return $current_user;
}
endif;
#~~~~~~~~~~~~~~~~~~~~~~~~

?>