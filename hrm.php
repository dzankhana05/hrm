<?php
/**
 * Plugin Name: WP human resource management
 * Plugin URI: http://mishubd.com/plugin/human-resource-management-hrm/
 * Description: Organization, Industries and Office management
 * Author: asaquzzaman
 * Version: 0.6
 * Author URI: http://mishubd.com
 * License: GPL2
 * TextDomain: hrm
 */

/**
 * Copyright (c) 2013 Asaquzzaman Mishu (email: joy.mishu@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * **********************************************************************
 */


function hrm_autoload( $class ) {
    $name = explode( '_', $class );

    if ( isset( $name[1] ) ) {
        $class_name = strtolower( $name[1] );
        $filename = dirname( __FILE__ ) . '/class/' . $class_name . '.php';
        if ( file_exists( $filename ) ) {
            require_once $filename;
        }
    }
}
spl_autoload_register( 'hrm_autoload' );

require_once dirname (__FILE__) . '/include/function.php';
require_once dirname (__FILE__) . '/include/urls.php';
require_once dirname (__FILE__) . '/include/page.php';

class Wp_Hrm {

    function __construct() {
        $this->version = '0.6';
        $this->db_version = '0.2';

        $this->plugin_dir = dirname(__FILE__);
        $this->instantiate();
        add_action( 'plugins_loaded', array($this, 'load_textdomain') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_notices', array($this, 'fornt_end') );
        register_activation_hook( __FILE__, array($this, 'install') );
        add_action( 'init', array( $this, 'init' ) );
    }

    function fornt_end() {
         $license_status = get_option( 'hrm_front_end_license' );
        if ( isset( $license_status->request_status ) ) {
            return;
        }
        ?>
         <div class="update-nag">
            <?php printf( __( 'If you want the <strong>front-end</strong> version of <strong>wp human resource management</strong> plugin,
            then please go & purchase it, <a href="http://mishubd.com/product/hrm-front-end/" target="_blank">HRM front-end</a>' )  ); ?>
        </div>
        <?php
    }

    /**
     * Load plugin textdomain
     *
     * @since 0.3
     */
    function load_textdomain() {
        load_plugin_textdomain( 'hrm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function init() {

        if ( !defined( 'DOING_AJAX' ) ) {
            global $hrm_is_admin;
            $hrm_is_admin = is_admin() ? 1 : 0;

        } else {
            global $hrm_is_admin;

            if ( isset( $_REQUEST['hrm_dataAttr']['is_admin'] ) ) {
                $hrm_is_admin = $_REQUEST['hrm_dataAttr']['is_admin'];
            } else if ( isset( $_REQUEST['hrm_attr']['is_admin'] ) ) {
                $hrm_is_admin = $_REQUEST['hrm_attr']['is_admin'];
            } else if ( isset( $_REQUEST['is_admin'] ) ) {
                $hrm_is_admin = $_REQUEST['is_admin'];
            }
        }


        Hrm_Init::getInstance()->register_post_type();
    }


    static function admin_scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-autocomplete');
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'hrm_chosen', plugins_url( '/asset/js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), false, true);
        wp_enqueue_script( 'hrm_datetimepicker', plugins_url( '/asset/js/jquery-ui-timepicker.js', __FILE__ ), array( 'jquery' ), false, true);
        wp_enqueue_script( 'hrm_admin', plugins_url( '/asset/js/hrm.js', __FILE__ ), array( 'jquery' ), false, true);

        wp_localize_script( 'hrm_admin', 'hrm_ajax_data', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            '_wpnonce'    => wp_create_nonce( 'hrm_nonce' ),
            'is_admin'    => is_admin() ? 1 : 0,
            'confirm_msg' => __( 'Are you sure!', 'hrm'),
            'success_msg' => __( 'Changed Successfully', 'hrm' )
        ));

        wp_enqueue_style( 'hrm-chosen', plugins_url( '/asset/css/chosen.min.css', __FILE__ ), false, false, 'all' );
        wp_enqueue_style( 'hrm-admin', plugins_url( '/asset/css/admin.css', __FILE__ ), false, false, 'all' );
        wp_enqueue_style( 'hrm-jquery-ui', plugins_url( '/asset/css/jquery-ui.css', __FILE__ ), false, false, 'all' );
        wp_enqueue_style( 'hrm-jquery-ui-timepicker', plugins_url( '/asset/css/jquery-ui-timepicker-addon.css', __FILE__ ), false, false, 'all' );

    }

    function instantiate() {

        Hrm_Ajax::getInstance();
        Hrm_Admin::getInstance();
        Hrm_Leave::getInstance();
        Hrm_Employee::getInstance();
    }

    function install() {
        $logged_in_user_id = get_current_user_id();

        new Hrm_Db();
        new Hrm_Update();

        update_option( 'hrm_admin', $logged_in_user_id );
        update_option( 'hrm_version', $this->version );
        update_option( 'hrm_db_version', $this->db_version );
    }

    function pim_scripts() {
        $this->admin_scripts();
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'plupload-handlers' );
    }

    function leave_scripts() {
        $this->admin_scripts();
    }

    function employer_scripts() {
        $this->admin_scripts();
    }

    function attendance_scripts() {
        $this->admin_scripts();
    }

    function evaluation_scripts() {
        $this->admin_scripts();
    }

    function author_scripts() {
        $this->admin_scripts();
    }

    static function file_scripts() {
        self::admin_scripts();
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'plupload-handlers' );
        wp_enqueue_script( 'hrm-file', plugins_url( '/asset/js/file.js', __FILE__ ) );
    }

    function admin_menu() {
        $capability = 'read'; //minimum level: subscriber
        if ( hrm_current_user_role() != 'hrm_employee' ) {
            $menu           = add_menu_page( __( 'HRM', 'hrm' ), __( 'HRM', 'hrm' ), $capability, 'hrm_management', array($this, 'admin_page_handler'), ''  );
            $admin_sub_menu = add_submenu_page( 'hrm_management', __( 'Admin', 'hrm' ), __( 'Admin', 'hrm' ), $capability, 'hrm_management', array($this, 'admin_page_handler') );
            $pim            = add_submenu_page( 'hrm_management', __( 'PIM', 'hrm' ), __( 'PIM', 'hrm' ), $capability, 'hrm_pim', array( $this, 'admin_page_handler' ) );
            $leave          = add_submenu_page( 'hrm_management', __( 'Leave', 'hrm' ), __( 'Leave', 'hrm' ), $capability, 'hrm_leave', array( $this, 'admin_page_handler' ) );
            $attendance     = add_submenu_page( 'hrm_management', __( 'Time', 'hrm' ), __( 'Time', 'hrm' ), $capability, 'hrm_time', array( $this, 'admin_page_handler' ) );
            $evaluation     = add_submenu_page( 'hrm_management', __( 'Evaluation', 'hrm' ), __( 'Evaluation', 'hrm' ), $capability, 'hrm_evaluation', array( $this, 'admin_page_handler' ) );
            $file           = add_submenu_page( 'hrm_management', __( 'File', 'hrm' ), __( 'File', 'hrm' ), $capability, 'hrm_file', array( $this, 'admin_page_handler' ) );
            $author         = add_submenu_page( 'hrm_management', __( 'Support', 'hrm' ), __( 'Support', 'hrm' ), $capability, 'hrm_author', array( $this, 'admin_page_handler' ) );

            add_action( 'admin_print_styles-' . $admin_sub_menu, array($this, 'admin_scripts') );
            add_action( 'admin_print_styles-' . $pim, array( $this, 'pim_scripts') );
            add_action( 'admin_print_styles-' . $leave, array( $this, 'leave_scripts' ) );
            add_action( 'admin_print_styles-' . $attendance, array($this, 'attendance_scripts') );
            add_action( 'admin_print_styles-' . $evaluation, array($this, 'evaluation_scripts') );
            add_action( 'admin_print_styles-' . $author, array($this, 'author_scripts') );
            add_action( 'admin_print_styles-' . $file, array($this, 'file_scripts') );

        } else {
            $user_id    = get_current_user_id();
            $menu       = add_menu_page( __( 'HRM', 'hrm' ), __( 'HRM', 'hrm' ), $capability, 'hrm_employee', array($this, 'admin_page_handler') );
            $menu       = add_submenu_page( 'hrm_employee', __( 'My Info', 'hrm' ), __( 'My Info', 'hrm' ), $capability, 'hrm_employee', array($this, 'admin_page_handler') );
            $attendance = add_submenu_page( 'hrm_employee', __( 'Time', 'hrm' ), __( 'Time', 'hrm' ), $capability, 'hrm_time', array( $this, 'admin_page_handler' ) );
            $file       = add_submenu_page( 'hrm_employee', __( 'File', 'hrm' ), __( 'File', 'hrm' ), $capability, 'hrm_file', array( $this, 'admin_page_handler' ) );
            add_action( 'admin_print_styles-' . $attendance, array($this, 'attendance_scripts') );
            add_action( 'admin_print_styles-' . $file, array($this, 'file_scripts') );
            $this->admin_scripts();
        }
    }


    function admin_page_handler() {
        if( !is_user_logged_in() ) {
            sprintf( 'Please <a href="%s">login</a>', wp_login_url() );
            return;
        }
        $current_user_id = get_current_user_id();
        $user_status = get_user_meta( $current_user_id, '_status', true );

        if ( $user_status == 'no' ) {
            _e( '<div class="hrm wrap"><h1>This account temporary disabled!</h1></div>', 'hrm' );
            return;
        }

        $menu       = hrm_page();
        $query_args = hrm_get_query_args();
        $page       = $query_args['page'];
        $tab        = $query_args['tab'];
        $subtab     = $query_args['subtab'];

        if ( $page == 'hrm_author' ) {
            require_once dirname (__FILE__) . '/templates/author/header.php';
            return;
        }

        echo '<div class="hrm wrap" id="hrm">';
        Hrm_Settings::getInstance()->show_tab_page( $page, $tab, $subtab );
        echo '</div>';
    }
}

new Wp_Hrm();

