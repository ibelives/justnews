<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Smilies{
    function __construct(){
        add_filter( 'smilies', array($this, 'smilies') );
        add_filter( 'smilies_src', array($this, 'smilies_src'), 10, 2 );
        add_filter( 'comment_excerpt', array($this, 'comment_excerpt'));

        add_action('init', array($this, 'smile_search'));
        add_action('wp_ajax_wpcom_get_smilies', array($this, 'ajax_get_smilies'));
        add_action('wp_ajax_nopriv_wpcom_get_smilies', array($this, 'ajax_get_smilies'));
    }

    function smile_search(){
        global $wp_smiliessearch, $wpsmiliestrans;
        $wp_smiliessearch = '/';
        $subchar = '';
        foreach ( (array) $wpsmiliestrans as $smiley => $img ) {
            $firstchar = substr( $smiley, 0, 1 );
            $rest      = substr( $smiley, 1 );

            if ( $firstchar != $subchar ) {
                if ( '' !== $subchar ) {
                    $wp_smiliessearch .= ')';  // End previous "subpattern".
                    $wp_smiliessearch .= '|'; // Begin another "subpattern".
                }
                $subchar           = $firstchar;
                $wp_smiliessearch .= preg_quote( $firstchar, '/' ) . '(?:';
            } else {
                $wp_smiliessearch .= '|';
            }
            $wp_smiliessearch .= preg_quote( $rest, '/' );
        }

        $wp_smiliessearch .= ')/m';
    }

    function smilies($smilies){
        $smilies = $smilies ?: array();
        $_smilies = $this->get_smilies();
        if($_smilies){
            foreach ($_smilies as $smile){
                $smilies['['.$smile['name'].']'] = $this->get_smile($smile['file'], $smile['name']);
            }
        }
        return $smilies;
    }

    function smilies_src( $img_src, $img ){
        // 兼容子主题使用自定义表情包
        if (file_exists(STYLESHEETPATH . '/images/smilies/' . $img)) {
            $img_src = get_stylesheet_directory_uri() .'/images/smilies/' . $img;
        }else if(file_exists(FRAMEWORK_PATH . '/assets/images/smilies/' . $img)){
            $img_src = FRAMEWORK_URI . '/assets/images/smilies/'.$img;
        }
        return $img_src;
    }

    function get_smile($smile, $name=''){
        $ext = preg_match( '/\.([^.]+)$/', $smile, $matches ) ? strtolower( $matches[1] ) : false;
        if($ext === 'svg'){
            $src_url = $this->smilies_src( '', $smile );
            return sprintf( '<img src="%s" alt="%s" class="wp-smiley j-lazy" />', esc_url( $src_url ), esc_attr( $name ) );
        }
        return $smile;
    }

    function get_smilies(){
        if (file_exists(STYLESHEETPATH . '/js/smilies.json')) {
            $file = STYLESHEETPATH . '/js/smilies.json';
        }else{
            $file = FRAMEWORK_PATH . '/assets/js/smilies.json';
        }
        $_smilies = file_get_contents($file);
        $smiles = $_smilies ? json_decode($_smilies, true) : array();
        return $smiles;
    }

    function ajax_get_smilies(){
        $_smilies = $this->get_smilies();
        $smilies = array();
        if($_smilies){
            foreach ($_smilies as $smile){
                $smilies[] = array(
                    'name' => '['.$smile['name'].']',
                    'title' => $smile['name'],
                    'src' => $this->smilies_src($smile['file'], $smile['file'])
                );
            }
        }
        echo json_encode($smilies);
        exit;
    }

    function comment_excerpt($excerp){
        return convert_smilies($excerp);
    }
}

new WPCOM_Smilies();