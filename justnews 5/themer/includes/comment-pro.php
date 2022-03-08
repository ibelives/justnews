<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Walker_Comment extends Walker_Comment {
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1;
        if($depth>0) return $output;

        switch ( $args['style'] ) {
            case 'div':
                break;
            case 'ol':
                $output .= '<ol class="comment-children">' . "\n";
                break;
            case 'ul':
            default:
                $output .= '<ul class="comment-children">' . "\n";
                break;
        }
    }
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1;
        if($depth>0) return $output;

        switch ( $args['style'] ) {
            case 'div':
                break;
            case 'ol':
                $output .= "</ol>\n";
                break;
            case 'ul':
            default:
                $output .= "</ul>\n";
                break;
        }
    }

    public function html5_comment( $comment, $depth, $args ) {
        $GLOBALS['comment'] = $comment;

        if ( 'div' == $args['style'] ) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
        $author = get_comment_author();
        $reply = '';
        if($depth>0 && $comment->comment_parent){
            $comment_parent = get_comment($comment->comment_parent);
            if($comment_parent->user_id){
                $replydata = get_userdata( $comment_parent->user_id );
                $reply = '<a class="j-user-card" data-user="'.$replydata->ID.'" href="'.get_author_posts_url( $replydata->ID ).'" target="_blank">@'.$replydata->display_name.'</a>';
            }else{
                $reply = '<a href="#comment-' . $comment->comment_parent.'">@'.get_comment_author($comment->comment_parent).'</a>';
            }
        }
        if( $comment->user_id && class_exists('WPCOM_Member') ){
            $url = get_author_posts_url( $comment->user_id );
            $userdata = get_userdata( $comment->user_id );
            $author = $userdata->display_name;
            $author = '<a class="j-user-card" data-user="'.$comment->user_id.'" href="'.$url.'" target="_blank">'.$author.'</a>';
        }else if( $comment->comment_author_url ){
            $author = get_comment_author_link( $comment->comment_ID );
        } ?>
        <<?php echo $tag ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php comment_ID() ?>">
        <div id="div-comment-<?php comment_ID() ?>" class="comment-inner">
            <div class="comment-author vcard">
                <?php if ( $args['avatar_size'] != 0 ) echo get_avatar( $comment, $args['avatar_size'], '', get_comment_author() ); ?>
            </div>
            <div class="comment-body">
                <div class="nickname"><?php echo $author;?>
                    <span class="comment-time"><?php echo get_comment_date().' '.get_comment_time(); ?></span>
                </div>
                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <div class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wpcom' ); ?></div>
                <?php endif; ?>
                <div class="comment-text">
                    <?php $comment_text = get_comment_text( $comment );
                    if($reply) $comment_text = '<span class="comment-text-reply">'.$reply.'：</span>' . $comment_text; ?>
                    <?php echo apply_filters( 'comment_text', $comment_text, $comment, $args ); ?>
                </div>
            </div>

            <div class="reply">
                <?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
            </div>
        </div>
        <?php
    }
}