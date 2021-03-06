<?php
/**
 * Created by PhpStorm.
 * User: rel
 * Date: 2/9/2017
 * Time: 9:13 AM
 */

/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class RelLinkChecker {
    private $rel_link_checker_options;
    private $check_path;
    private $stats;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'rel_link_checker_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'rel_link_checker_page_init' ) );
    }

    public function rel_link_checker_add_plugin_page() {
        add_menu_page(
            'Link Checker', // page_title
            'Link Checker', // menu_title
            'manage_options', // capability
            'rel-link-checker', // menu_slug
            array( $this, 'rel_link_checker_create_admin_page' ), // function
            'dashicons-admin-generic', // icon_url
            81 // position
        );
    }

    public function rel_link_checker_create_admin_page() {
        $this->rel_link_checker_options = get_option( 'rel_link_checker_option_name' );
        var_dump($this->rel_link_checker_options);
        ?>
        
        <div class="wrap">
            <h2>Link Checker</h2>
            <p>Link checker for streaming/download hosts</p>

            <?php

            if( isset($_GET['reset_check']) ){
                $this->reset_check();
            }

            $status = $this->get_test_status();

            if ( $status == 'completed' ) {
                ?>
                <span class='label label-info'>A test has been completed</span>
                <form method="get" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="page" value="rel-link-checker">
                    <input type="submit" name="reset_check" value="Reset Check">
                </form>
                <?php
            } elseif ( $status == "running" ) {
                echo "<span class='label label-info'>A test is running</span>";
                ?>
                <form method="get" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="page" value="rel-link-checker">
                    <input type="submit" name="reset_check" value="Abort Check">
                </form>
                <?php
            }

            settings_errors();

            ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'rel_link_checker_option_group' );
                do_settings_sections( 'rel-link-checker-admin' );
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function rel_link_checker_page_init() {
        register_setting(
            'rel_link_checker_option_group', // option_group
            'rel_link_checker_option_name', // option_name
            array( $this, 'rel_link_checker_sanitize' ) // sanitize_callback
        );

        add_settings_section(
            'rel_link_checker_setting_section', // id
            'Settings', // title
            array( $this, 'rel_link_checker_section_info' ), // callback
            'rel-link-checker-admin' // page
        );

        add_settings_field(
            'db_name', // id
            'DB name', // title
            array( $this, 'db_name_callback' ), // callback
            'rel-link-checker-admin', // page
            'rel_link_checker_setting_section' // section
        );

        add_settings_field(
            'db_user', // id
            'DB user', // title
            array( $this, 'db_user_callback' ), // callback
            'rel-link-checker-admin', // page
            'rel_link_checker_setting_section' // section
        );

        add_settings_field(
            'db_pass', // id
            'DB pass', // title
            array( $this, 'db_pass_callback' ), // callback
            'rel-link-checker-admin', // page
            'rel_link_checker_setting_section' // section
        );

        add_settings_field(
            'db_host', // id
            'DB host', // title
            array( $this, 'db_host_callback' ), // callback
            'rel-link-checker-admin', // page
            'rel_link_checker_setting_section' // section
        );
    }

    public function rel_link_checker_sanitize($input) {
        $sanitary_values = array();
        if ( isset( $input['db_name'] ) ) {
            $sanitary_values['db_name'] = sanitize_text_field( $input['db_name'] );
        }

        if ( isset( $input['db_user'] ) ) {
            $sanitary_values['db_user'] = sanitize_text_field( $input['db_user'] );
        }

        if ( isset( $input['db_pass'] ) ) {
            $sanitary_values['db_pass'] = sanitize_text_field( $input['db_pass'] );
        }

        if ( isset( $input['db_host'] ) ) {
            $sanitary_values['db_host'] = sanitize_text_field( $input['db_host'] );
        }

        return $sanitary_values;
    }

    public function rel_link_checker_section_info() {

    }

    public function db_name_callback() {
        $host = $this->rel_link_checker_options['db_name'];
        $items = array("wso","fstash","wmo");
        echo "<select id='drop_down1' name='rel_link_checker_option_name[db_name]' id='db_name' value='%s'>";
        foreach($items as $item) {
            $selected = ($host == $item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";
        /*printf(
            '<input class="regular-text" type="text" name="rel_link_checker_option_name[db_name]" id="db_name" value="%s">',
            isset( $this->rel_link_checker_options['db_name'] ) ? esc_attr( $this->rel_link_checker_options['db_name']) : ''
        );*/
    }

    public function db_user_callback() {
        printf(
            '<input class="regular-text" type="text" name="rel_link_checker_option_name[db_user]" id="db_user" value="%s">',
            isset( $this->rel_link_checker_options['db_user'] ) ? esc_attr( $this->rel_link_checker_options['db_user']) : ''
        );
    }

    public function db_pass_callback() {
        printf(
            '<input class="regular-text" type="password" name="rel_link_checker_option_name[db_pass]" id="db_pass" value="%s">',
            isset( $this->rel_link_checker_options['db_pass'] ) ? esc_attr( $this->rel_link_checker_options['db_pass']) : ''
        );
    }

    public function db_host_callback() {
        printf(
            '<input class="regular-text" type="text" name="rel_link_checker_option_name[db_host]" id="db_host" value="%s">',
            isset( $this->rel_link_checker_options['db_host'] ) ? esc_attr( $this->rel_link_checker_options['db_host']) : ''
        );
    }

    public function get_test_status(){
        $this->check_path = get_home_path() . '/autocheck';
        $stats = file_get_contents($this->check_path . '/stats/data.stats');
        $stats = json_decode($stats,true);
        return $stats['status'];
    }

    public function reset_check(){
        $status = $this->get_test_status();
        //TODO: Kill php process that is running our check
        $stats = ["dir" => "autocheck/stats/","files" => ["data.stats","deleted.txt","invalid.txt",'otherhosts.txt']];
        if ( $status == 'completed' ) {
            foreach ($stats["files"] as $file) {
                $path = get_home_path() . $stats["dir"] . $file;
                if (file_exists($path)){
                    unlink($path);
                }
            }
        }
        unlink($this->check_path . '/last_links.txt');
    }

    /**
     * Deletes the files generated after a check has been completed
     */
    public function clean_check(){

    }

}

if ( is_admin() )
    $rel_link_checker = new RelLinkChecker();

/*
 * Retrieve this value with:
 * $rel_link_checker_options = get_option( 'rel_link_checker_option_name' ); // Array of All Options
 * $db_name = $rel_link_checker_options['db_name']; // Domain
 * $db_user = $rel_link_checker_options['db_user']; // DB user
 * $db_pass = $rel_link_checker_options['db_pass']; // DB pass
 * $db_host = $rel_link_checker_options['db_host']; // DB host
 */
