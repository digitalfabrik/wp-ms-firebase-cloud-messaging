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

    /**
     * Starting point for database initialization. Checks for previously installed
     * plugin versions and calls table creation functions, depending on multisite setup.
     */
    public function install_database() {
        if( is_multisite() ) {
            $version = get_site_option( 'wp-ms-fcm-db-version' );
            if( False == $version ) {
                // Upgrade from version 1.0 or new installation on multisite
                $this->create_tables_v_2_0_mu();
            } else {
                return new WP_Error( 'Unknown WP FCM database version.', $version );
            }
        } else {
            $version = get_option( 'wp-ms-fcm-db-version' );
            if( False == $version ) {
                // Upgrade from version 1.0 or new installation for single blog
                $this->create_tables_v_2_0();
            } else {
                return new WP_Error( 'Unknown WP FCM database version.', $version );
            }
        }
        return true;
    }

    /**
     * Retrieve FCM messages from database for current blog.
     *
     * @param array $args contains filters for query
     * @return array
     */
    public function get_fcm_messages( $args = array() ) {
        global $wpdb;
        $defaults = array(
            'order' => 'DESC',
            'orderby' => 'timestamp',
            'limit' => False
        );
        $args = wp_parse_args( $args, $defaults );
        $query = "SELEC * FROM " . $wpdb->prefix . "fcm_messages ";
        $query .= "ORDER BY " . $args['orderby'] . " " . $args['order'] ( $args['limit'] != False ? " Limit " . $args['limit'] : "");
        $results = $wpdb->get_results( $query );
        $return = array();
        foreach( $results as $item ) {
            $return[] = array(
                'id' => $item->id,
                'request' => json_decode( $item->sent_message, true ),
                'answer' => json_decode( $item->returned_message, true ),
                'timestamp' => $item->timestamp
            )
        }
        return $return;
    }
}

?>