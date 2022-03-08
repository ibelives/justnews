<?php
defined( 'ABSPATH' ) || exit;

add_action('wp_ajax_wpcom_mobile_share', 'wpcom_mobile_share');
add_action('wp_ajax_nopriv_wpcom_mobile_share', 'wpcom_mobile_share');
function wpcom_mobile_share(){
    global $options, $post;
    if(isset($_POST['id']) && $_POST['id'] && $post = get_post($_POST['id'])){
        setup_postdata( $post );
        $img_url = WPCOM::thumbnail_url($post->ID);
        $share_head = $img_url ?: (isset($options['wx_thumb']) ? $options['wx_thumb'] : '');
        $share_head = is_numeric($share_head) ? wp_get_attachment_url( $share_head ) : $share_head;
        $share_logo = isset($options['mobile_share_logo']) && $options['mobile_share_logo'] ? $options['mobile_share_logo'] : $options['logo'];
        $share_logo = is_numeric($share_logo) ? wp_get_attachment_url( $share_logo ) : $share_logo;
        $excerpt = rtrim(rtrim(strip_tags(apply_filters( 'the_excerpt', get_the_excerpt() )), "\n"), '['._x('Read More', '原文链接', 'wpcom').']');
        $_author = get_user_by('ID', $post->post_author);
        $avatar = get_avatar_url( $post->post_author, array( 'size' => 48 ) );
        $avatar = wpcom_image_to_base64($avatar);
        $author = '<img src="'.$avatar.'">' . $_author->display_name;

        $data = array(
            'head' => wpcom_image_to_base64($share_head),
            'logo' => wpcom_image_to_base64($share_logo),
            'title' => $post->post_title,
            'excerpt' => $excerpt,
            'time' => get_the_time(get_option('date_format') . ' '. get_option('time_format')),
            'author' => $author
        );

        wp_reset_postdata();

        echo wpcom_mobile_share_render($data);
        exit;
    }
}

function wpcom_mobile_share_render($data){
    global $options;
    $show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
    ?>
    <div class="top_tips"><?php _e('Save the poster and share with more friends', 'wpcom');?></div>
    <div class="mobile-share-container">
        <div class="mobile-share-inner">
            <div class="mobile-share-head">
                <img src="<?php echo $data['head'];?>">
                <h2 class="mobile-share-title"><div class="mobile-share-title-bg"></div><?php echo $data['title'];?></h2>
            </div>
            <div class="mobile-share-body">
                <div class="mobile-share-meta">
                    <?php if($show_author){ ?>
                        <div class="mobile-share-author"><?php echo $data['author'];?></div>
                    <?php } ?>
                    <div class="mobile-share-time">
                        <?php echo $data['time']; ?>
                    </div>
                </div>
                <div class="mobile-share-text"><?php echo wpautop($data['excerpt']);?></div>
                <div class="mobile-share-body-line">______________________________________</div>
            </div>
            <div class="mobile-share-footer">
                <div class="mobile-share-logo">
                    <img src="<?php echo $data['logo'];?>">
                </div>
                <div class="mobile-share-qr">
                    <div class="mobile-share-qrbg"><img src="data:image/svg+xml,%3Csvg width='82' height='82' viewBox='0 0 82 82' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M81 63v18H61m-41 0H1V62m0-43V1h20m40 0h20v18' stroke='%232157B2' fill='none' fill-rule='evenodd' opacity='.405' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E" alt="bg"></div>
                    <div class="mobile-share-qrcode"></div>
                </div>
            </div>
        </div>
        <div class="mobile-share-canvas">
            <div class="canvas-loading"><?php WPCOM::icon('loader', true, 'wpcom-icon-loader');?></div>
        </div>
    </div>
    <div class="mobile-share-close"><?php WPCOM::icon('close');?></div>
<?php }

function wpcom_image_to_base64( $image ){
    $site_domain = parse_url(get_bloginfo('url'), PHP_URL_HOST);
    $img_domain = parse_url($image, PHP_URL_HOST);
    if ( $img_domain != $site_domain ) {
        $http_options = array(
            'httpversion' => '1.0',
            'timeout' => 20,
            'redirection' => 20,
            'sslverify' => FALSE,
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
            'headers' => array(
                'referer' => home_url()
            )
        );
        if(preg_match('/^\/\//i', $image)) $image = 'http:' . $image;
        $get = wp_remote_get($image, $http_options);
        if (!is_wp_error($get) && 200 === $get ['response'] ['code']) {
            $img_base64 = 'data:' . $get['headers']['content-type'] . ';base64,' . base64_encode($get ['body']);
            return $img_base64;
        }
    }
    $image = preg_replace('/^(http:|https:)/i', '', $image);
    return $image;
}

add_filter( 'wpcom_localize_script', 'wpcom_mobile_share_localize' );
function wpcom_mobile_share_localize($scripts){
    $scripts['poster'] = array(
        'notice' => __('Save the poster and share with more friends', 'wpcom'),
        'generating' => __('Poster generation...', 'wpcom'),
        'failed' => __('Poster generation failed', 'wpcom')
    );
    return $scripts;
}
