<?php

class FirebaseNotificationsDatabase {
    function __construct() {
        $this->last_status = False;
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
            if( "1" === $blog->blog_id ) {
                continue;
            }
            $table_name = $wpdb->base_prefix . $blog->blog_id . "_" . "fcm_messages";
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                        `id` INT NOT NULL AUTO_INCREMENT,
                        `sent_message` TEXT NOT NULL,
                        `returned_message` TEXT NOT NULL,
                        `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`)
                    ) $charset_collate;";
            if ($wpdb->query( $sql ) ) {
                $this->last_status = true;
            } else {
                $this->last_status = new WP_Error( 'Cannot create database table', $query );
                return $this->last_status;
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
        $table_name = $wpdb->prefix . "fcm_messages";
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sent_message` TEXT NOT NULL,
            `returned_message` TEXT NOT NULL,
            `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) $charset_collate;";
        if ($wpdb->query( $sql ) ) {
            $this->last_status = true;
            return $this->last_status;
        } else {
            $this->last_status = new WP_Error( 'Cannot create database table', $query );
            return $this->last_status;
        }
    }

    /**
     * Starting point for database initialization. Checks for previously installed
     * plugin versions and calls table creation functions, depending on multisite setup.
     */
    public function install_database() {
        if( is_multisite() ) {
            $version = get_site_option( 'fbn_db_version' );
            if( False == $version ) {
                // Upgrade from version 1.0 or new installation on multisite
                add_site_option( 'fbn_db_version', '2.0');
                self::create_tables_v_2_0_mu();
            } else {
                $this->last_status = WP_Error( 'Unknown WP FCM database version.', $version );
                return $this->last_status;
            }
        } else {
            $version = get_option( 'fbn_db_version' );
            if( False == $version ) {
                // Upgrade from version 1.0 or new installation for single blog
                add_option( 'fbn_db_version', '2.0');
                self::create_tables_v_2_0();
            } else {
                $this->last_status = WP_Error( 'Unknown WP FCM database version.', $version );
                return $this->last_status;
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
    public function get_messages( $args = array() ) {
        global $wpdb;
        $defaults = array(
            'order' => 'DESC',
            'orderby' => 'timestamp',
            'limit' => False
        );
        $args = wp_parse_args( $args, $defaults );
        $query = "SELEC * FROM " . $wpdb->prefix . "fcm_messages ";
        $query .= "ORDER BY " . $args['orderby'] . " " . $args['order'] ( $args['limit'] != False ? " Limit " . $args['limit'] : "");
        if($results = $wpdb->get_results( $query )) {
            $this->last_status = true;
        } else {
            $this->last_status = false;
        }
        $return = array();
        foreach( $results as $item ) {
            $return[] = array(
                'id' => $item->id,
                'request' => json_decode( $item->sent_message, true ),
                'answer' => json_decode( $item->returned_message, true ),
                'timestamp' => $item->timestamp
            );
        }
        return $return;
    }

    /**
     * Save a FCM message with answer in the database.
     *
     * @param string $request JSON sent to the FCM REST API
     * @param string $answer JSON returned by the FCM REST API
     *
     * @return boolean of success
     */
    public function save_message( $request, $answer ) {
        global $wpdb;
        $query = "INSERT INTO " . $wpdb->prefix . "fcm_messages (sent_message, returned_message) VALUES ('" . $request . "', '" . $answer . "')";
        if( $wpdb->query($query) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve messages by language.
     *
     * @param string $lang language code
     * @param integer $amount number of messages that are returned
     * @return array of messages as assoc arrays
     */
    public function messages_by_language( $lang = ICL_LANGUAGE_CODE, $amount = 10 ) {
        $fcmdb = New FirebaseNotificationsDatabase();
        $args = array(
            'order' => 'DESC',
            'orderby' => 'timestamp',
            'limit' => False
        );
        $result = array();
        $messages = $fcmdb->get_messages( $args );
        $count = 1;
        foreach( $messages as $message ){
            $array = json_decode( $message, true );
            if( $array['data']['language_code'] == $lang ) {
                $result[] = $array;
            }
            $count ++;
            if( $count === $amount ) {
                break;
            }
        }
        return $result;
    }
}

?>
