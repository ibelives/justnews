<?php
defined( 'ABSPATH' ) || exit;

if(!class_exists('WPCOM_Object_Cache')) {
    class WPCOM_Object_Cache{
        public function __construct(){
            add_filter('posts_pre_query', array($this, 'cache_post_query'), 10, 2);
            add_filter('the_posts', array($this, 'set_post_query_cache'), 10, 2);

            add_action('save_post', array($this, 'set_last_changed'), 10, 2);
            add_action('deleted_post', array($this, 'set_last_changed'), 10, 2);
        }

        function set_last_changed($post_id, $post){
            wp_cache_set( $post->post_type, microtime(), 'last_changed' );
        }

        function get_last_changed($type){
            $last_changed = wp_cache_get( $type, 'last_changed' );
            if ( ! $last_changed ) {
                wp_cache_set( $type, microtime(), 'last_changed' );
            }
            return $last_changed;
        }

        public static function get_posts_by_ids($post_ids, $args = array()){
            $_posts = self::update_caches($post_ids, $args);
            $posts = array();
            if($_posts){
                foreach ($_posts as $post){
                    $posts[] = self::get_post($post);
                }
            }
            return $posts;
        }

        public static function update_caches($post_ids, $args = array()){
            if ($post_ids) $post_ids = array_unique(array_filter($post_ids));
            if (empty($post_ids)) return array();

            $update_term_cache = isset($args['update_post_term_cache']) ? $args['update_post_term_cache'] : true;
            $update_meta_cache = isset($args['update_post_meta_cache']) ? $args['update_post_meta_cache'] : true;

            _prime_post_caches($post_ids, $update_term_cache, $update_meta_cache);

            if(function_exists('wp_cache_get_multiple')) {
                $cache_values = wp_cache_get_multiple($post_ids, 'posts');
                foreach ($post_ids as $post_id) {
                    if (empty($cache_values[$post_id])) {
                        wp_cache_add($post_id, false, 'posts', 10);
                    }
                }

                return $cache_values;
            } else {
                $cache_values = array();

                foreach ($post_ids as $post_id) {
                    $cache = wp_cache_get($post_id, 'posts');

                    if ($cache !== false) {
                        $cache_values[$post_id] = $cache;
                    }
                }

                return $cache_values;
            }
        }

        public static function get_post($post, $output=OBJECT, $filter='raw'){
            if($post && is_numeric($post)){	// 不存在情况下的缓存优化
                $found	= false;
                $cache	= wp_cache_get($post, 'posts', false, $found);

                if($found){
                    if(is_wp_error($cache)){
                        return $cache;
                    }elseif(!$cache){
                        return null;
                    }
                }else{
                    $_post	= WP_Post::get_instance($post);

                    if(!$_post){
                        wp_cache_add($post, false, 'posts', 10);
                        return null;
                    }
                }
            }

            return get_post($post, $output, $filter);
        }

        public function cache_post_query($pre, $wp_query){
            $post_type = isset($wp_query->query_vars['post_type']) ? $wp_query->query_vars['post_type'] : '';
            if ($wp_query->is_main_query()) { // 主循环
                $cache_key = md5(maybe_serialize($wp_query->query_vars)) . ':' . $this->get_last_changed($post_type?:'post');
                $wp_query->set('cache_key', $cache_key);
                $post_ids = wp_cache_get($cache_key, 'wpcom_post_ids');

                if ($post_ids === false) return $pre;

                $found_posts = wp_cache_get($cache_key. '_found_posts', 'wpcom_found_posts');
                if($found_posts) {
                    $wp_query->found_posts = $found_posts;
                    $wp_query->max_num_pages = ceil( $wp_query->found_posts / $wp_query->query_vars['posts_per_page'] );
                }

                return self::get_posts_by_ids($post_ids);
            }else if($post_type === 'nav_menu_item' || $post_type === 'wp_template'){
                $cache_key = md5(maybe_serialize($wp_query->query_vars));
                $wp_query->set('cache_key', $cache_key);
                $wp_query->set('suppress_filters', 0);
                $post_ids = wp_cache_get($cache_key, 'wpcom_post_ids');
                if ($post_ids === false) return $pre;

                $found_posts = wp_cache_get($cache_key. '_found_posts', 'wpcom_found_posts');
                if($found_posts) {
                    $wp_query->found_posts = $found_posts;
                    $wp_query->max_num_pages = ceil( $wp_query->found_posts / $wp_query->query_vars['posts_per_page'] );
                }

                return self::get_posts_by_ids($post_ids);
            }

            return $pre;
        }

        public function set_post_query_cache($posts, $wp_query){
            $cache_key = $wp_query->get('cache_key');
            if ($cache_key) {
                $post_ids = wp_cache_get($cache_key, 'wpcom_post_ids');
                if ($post_ids === false) {
                    wp_cache_set($cache_key, array_column($posts, 'ID'), 'wpcom_post_ids', DAY_IN_SECONDS);
                    wp_cache_set($cache_key. '_found_posts', $wp_query->found_posts, 'wpcom_found_posts', DAY_IN_SECONDS);
                }
            }
            return $posts;
        }
    }
}