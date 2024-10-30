<?php
/*
Plugin Name: Block-Spam-By-Math
Plugin URI: http://www.grauonline.de
Description: This plugin protects your registration, login and comment forms against spambots with a simple math question.
Author: Alexander Grau
Version: 1.0
Author URI: http://www.grauonline.de
*/

/*  Copyright 2009  Alexander Grau (email : alex [make-an-at] grauonline [make-a-dot] de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( !class_exists( 'BlockSpamByMath' ) ) {
	class BlockSpamByMath {
		// Constructor
		function BlockSpamByMath() {
			
			add_action( 'init', array( &$this, 'init' ) );
			
			add_action( 'register_form', array( &$this, 'add_hidden_fields' ) );
			add_action( 'login_form', array( &$this, 'add_hidden_fields' ) );
			add_action( 'comment_form', array( &$this, 'add_hidden_fields' ) );
			
			add_action( 'register_post', array( &$this, 'register_post' ), 10, 2 );
			add_action( 'wp_authenticate', array( &$this, 'wp_authenticate' ), 10, 2 );
			
			add_filter( 'preprocess_comment', array( &$this, 'preprocess_comment' ) );
		}
		
		// Initialize plugin
		function init() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'block-spam-by-math', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) );
			}
		}
		
		// Add hidden fields to the form
		function add_hidden_fields() {
			$mathvalue0 = rand(2, 15);
			$mathvalue1 = rand(2, 15);
      echo '<div><b>IMPORTANT!</b> To be able to proceed, you need to solve the following simple math (so we know that you are a human) :-) <br/><br/>';
			echo "What is $mathvalue0 + $mathvalue1 ?<br/>";			
			echo '<input type="text" name="mathvalue2" value="" />';
			echo '</div>';
			echo '<div style="display:none">Please leave these two fields as-is: ';
			echo "<input type='text' name='mathvalue0' value='$mathvalue0' />";
			echo "<input type='text' name='mathvalue1' value='$mathvalue1' />";
			echo '</div>';
		}
		
		//  Protection function for submitted register form
		function register_post( $user_login, $user_email ) {
			if ( ( $user_login != '' ) && ( $user_email != '' ) ) {
				$this->check_hidden_fields();
			}
		}
		
		// Protection function for submitted login form
		function wp_authenticate( $user_login, $user_password ) {
			if ( ( $user_login != '' ) && ( $user_password != '' ) ) {
				$this->check_hidden_fields();
			}
		}
		
		// Protection function for submitted comment form
		function preprocess_comment( $commentdata ) {
			$this->check_hidden_fields();
			return $commentdata;
		}
		
		// Check for hidden fields and wp_die() in case of error
		function check_hidden_fields() {
			// Get values from POST data
			$val0 = '';
			$val1 = '';
			$val2 = '';
			if ( isset( $_POST['mathvalue0'] ) ) {
				$val0 = $_POST['mathvalue0'];
			}
			if ( isset( $_POST['mathvalue1'] ) ) {
				$val1 = $_POST['mathvalue1'];
			}
			if ( isset( $_POST['mathvalue2'] ) ) {
				$val2 = $_POST['mathvalue2'];
			}
			
			// Check values
			if ( ( $val0 == '' ) || ( $val1 == '' ) || ( intval($val2) != (intval($val0) + intval($val1)) ) ) {
				// Die and return error 403 Forbidden
				wp_die( 'Bye Bye, SPAMBOT!', '403 Forbidden', array( 'response' => 403 ) );
			}
		}
	}
	
	// Ban heavy spammer's IPs
	$ip = @ip2long( $_SERVER['REMOTE_ADDR'] );
	if ( ( $ip !== -1 ) && ( $ip !== false )) {
		// Banned address spaces
		$banned_ranges = array(
			// Dragonara Alliance Ltd (194.8.74.0 - 194.8.75.255)
			array( '194.8.74.0', '194.8.75.255' ),
		);
		foreach( $banned_ranges as $range ) {
			$block = false;
			if ( is_array( $range ) ) {
				if ( ( $ip >= ip2long( $range[0] ) ) && ( $ip <= ip2long( $range[1] ) ) ) {
					$block = true;
				}
			} else {
				if ( $ip == ip2long( $range ) ) {
					$block = true;
				}
			}
			
			if ( $block ) {
				wp_die( 'Bye Bye, SPAMBOT!', '403 Forbidden', array( 'response' => 403 ) );
			}
		}
	}
	
	$wp_block_spam_by_math = new BlockSpamByMath();
}

?>