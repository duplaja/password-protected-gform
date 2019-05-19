<?php
/*
Plugin Name: Password Protected GForm
Plugin URI: https://dandulaney.com
Description: Require a value for a field to match in order for form to submit
Version: 1.1
Author: Dan Dulaney
Author URI: https://dandulaney.com
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2019 by Dan Dulaney <dan.dulaney07@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'gf_password_activate_check' ) ) {
	/**
	 * Makes sure that Gravity Forms is installed before activating
	 *
	 * @param
	 * @return
	 */

    register_activation_hook(__FILE__, 'gf_password_activate_check');
    function gf_password_activate_check() {
    
        if( !class_exists( 'GFCommon' ) ) {
        
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'Please activate Gravity Forms first.', 'password-protected-gform' ), 'Plugin dependency check', array( 'back_link' => true ) );
            
        }
    }
}
   
	
if ( ! function_exists( 'gf_password_protect_validation' ) ) {
	/**
	 * Validation Function. Checks form settings, and field ID, and then validates with stored password
	 *
	 * @param
	 * @return
	 */

    add_filter( 'gform_field_validation', 'gf_password_protect_validation', 10, 4 );	
	function gf_password_protect_validation( $result, $value, $form, $field ) {

	//Converts password saved string to array
        if(!empty($form['form_password_to_submit']) && $field->id == $form['form_password_field_id']) {
            $passwords_array = explode(',',$form['form_password_to_submit']);
        }

        if($result['is_valid'] && !empty($form['form_password_to_submit']) && !empty($form['form_password_field_id']) && $field->id == $form['form_password_field_id'] && !in_array($value,$passwords_array)) {

            $result['is_valid'] = false;

            if(!empty($form['form_password_custom_message'])) {
                $message = $form['form_password_custom_message'];
            } else {
                $message = 'Password check does not match';
            }
            $result['message'] = $message;

        } 

        return $result;
        
    }
}

if ( ! function_exists( 'gf_password_to_submit' ) ) {
	/**
	 * Adds boxes for form specific password, password field ID, and password validation failure message
	 *
	 * @param
	 * @return
	 */

    add_filter( 'gform_form_settings', 'gf_password_to_submit', 10, 2 );
    function gf_password_to_submit( $settings, $form ) {
        $settings[ __( 'Form Options', 'gravityforms' ) ]['form_password_to_submit'] = '
            <tr>
                <th><label for="form_password_to_submit">Required Password(s) To Submit<br>(blank for none)<br>Comma seperated for multiple, spaces will be stripped</label></th>
                <td><textarea name="form_password_to_submit" class="fieldwidth-3 fieldheight-2">'.rgar($form, 'form_password_to_submit').'</textarea></td>
            </tr>
            <tr>
                <th><label for="form_password_field_id">Field ID for Password Check<br>(blank for none)</label></th>
                <td><input type ="number" min="1" value="' . rgar($form, 'form_password_field_id') . '" name="form_password_field_id"></td>
            </tr>
            <tr>
                <th><label for="form_password_custom_message">Custom Failure Message (optional)</label></th>
                <td><input value="' . rgar($form, 'form_password_custom_message') . '" name="form_password_custom_message"></td>
            </tr>';
    
        return $settings;
    }
}


if ( ! function_exists( 'gf_save_password_to_submit' ) ) {
	/**
	 * Saves values for form specific password, password field ID, and password validation failure message
	 *
	 * @param
	 * @return
	 */ 

    add_filter( 'gform_pre_form_settings_save', 'gf_save_password_to_submit' );
    function gf_save_password_to_submit($form) {

        $pw = str_replace(' ','',rgpost( 'form_password_to_submit' ));

        $form['form_password_to_submit'] = $pw;
        $form['form_password_field_id'] = rgpost( 'form_password_field_id' );
        $form['form_password_custom_message'] = rgpost( 'form_password_custom_message' );

        return $form;
    }
}
