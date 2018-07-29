<?php

class wp_ms_fcm_database {
    function __construct() {
        // nothing right now
    }

    /**
     * Create database tables for each blog of a multisite.
     * Tables:
     * 1) PREFIX_BLOG-ID_sent_fc_messages
     */
    private function create_tables_v_2_0_mu () {
        global $wpdb;
        $all_blogs = get_sites();
        foreach ( $all_blogs as $blog ) {
            $table_name = $wpdb->base_prefix . $blog->blog_id . "_" . "fcm_messages"
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        `id` INT NOT NULL AUTO_INCREMENT,
                        `sent_message` TEXT NOT NULL,
                        `returned_message` TEXT NOT NULL,
                        `timestamp` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL,
                        PRIMARY KEY (`id`)
                    ) $charset_collate;";
            if ($wpdb->query( $sql ) ) {
                // nothing
            } else {
                return new WP_Error( 'Cannot create database table', $query );
            }
        }
    }

    /**
     * Create database table
     * Tables:
     * 1) PREFIX_sent_fc_messages
     */
    private function create_tables_v_2_0 () {
        global $wpdb;
        $table_name = $wpdb->prefix . "fcm_messages"
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sent_message` TEXT NOT NULL,
            `returned_message` TEXT NOT NULL,
            `timestamp` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;";
        if ($wpdb->query( $sql ) ) {
            // nothing
        } else {
            return new WP_Error( 'Cannot create database table', $query );
        }
    }

    public function install_database() {
        $version = get_site_option('wp-ms-fcm-db-version');

        if( False == $version && is_multisite() ) {
            // Upgrade from version 1.0 or new installation on multisite
            add_site_option( 'wp-ms-fcm-db-version',  '2.0' );
            $this->create_tables_v_2_0_mu();
        } elseif( False == $version && !is_multisite() ) {
            // Upgrade from version 1.0 or new installation on single site
            add_option( 'wp-ms-fcm-db-version',  '2.0' );
            $this->create_tables_v_2_0();
        } else {
            die("Unkown Database Version!")
        }
    }
}

?>