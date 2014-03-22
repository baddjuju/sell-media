<?php


/**
 * Print the buy button
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_item_buy_button( $post_id=null, $button=null, $text=null, $echo=true ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $text = apply_filters('sell_media_purchase_text', $text, $post_id );
    $html = '<a href="javascript:void(0)" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-' . $button . '">' . $text . '</a>';

    if ( $echo ) print $html; else return $html;
}


/**
 * Determines the image source for a product
 * @return (string) url to product image or feature image
 */
function sell_media_item_image_src( $post_id=null ) {

    $attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
    $image = wp_get_attachment_image_src( $attachment_id, 'medium' );
    $featured_image_id = get_post_thumbnail_id( $_POST['product_id'] );
    $featured_image = wp_get_attachment_image_src( $featured_image_id, 'medium' );

    if ( $image[0] )
        $image = $image[0];
    else
        $image = $featured_image[0];

    return $image;
}


/**
 * Returns the file extension of the product file
 * @return (string) file extension
 */
function sell_media_get_filetype( $post_id=null ){
    $filetype = wp_check_filetype( get_post_meta( $post_id, '_sell_media_attached_file', true ) );
    return $filetype['ext'];
}


/**
 * Determines the image used to represent an item for sale. If an
 * image mime type is detected than the attachment image is used.
 *
 * @return (string) an image tag
 */
function sell_media_item_icon( $post_id=null, $size='medium' ){

    $attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );
    // legacy function passed the $attachment_id into sell_media_item_icon
    // that means the above get_post_meta would be empty
    // if that's the case, than we assume the $post_id is actually the $attachment_id
    if ( empty( $attachment_id ) ){
        $attachment_id = $post_id;
    }
    $mime_type = get_post_mime_type( $attachment_id );

    // check if featured image is set
    if ( '' != get_the_post_thumbnail( $post_id ) ) {
        $image = get_the_post_thumbnail( $post_id, $size );
    } else {
        // check if protected file is an image
        if ( wp_attachment_is_image( $attachment_id ) ) {
            $image_attr = wp_get_attachment_image_src( $attachment_id, $size );
            $image = $image_attr[0]; // url
        // check the mime type of the file and return a default icon from core WP
        } else {
            switch ( $mime_type ) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $image = wp_mime_type_icon( 'image/jpeg' ); break;
                case 'video/mpeg':
                case 'video/mp4':
                case 'video/quicktime':
                    $image = wp_mime_type_icon( 'video/mpeg' ); break;
                case 'text/csv':
                case 'text/pdf':
                case 'text/plain':
                case 'text/xml':
                    $image = wp_mime_type_icon( 'application/pdf' ); break;
                default:
                    $image = wp_mime_type_icon(); break;
            }
        }
        $image =  '<img src="' . $image . '" class="sell_media_image wp-post-image" title="' . get_the_title( $post_id ) . '" alt="' . get_the_title( $post_id ) . '" data-sell_media_item_id="' . $post_id . '" style="max-width:100%;height:auto;"/>';
    }

    return $image;
}
add_action( 'wp_ajax_sell_media_image', 'sell_media_item_icon' );


/**
 * Optionally prints the plugin credit
 * Off by default in compliance with WordPress best practices
 * http://wordpress.org/extend/plugins/about/guidelines/
 *
 * @since 1.2.6
 * @author Thad Allender
 */
function sell_media_plugin_credit() {
    $settings = sell_media_get_plugin_options();

    if ( true == $settings->plugin_credit ) {
        printf( '%s <a href="http://graphpaperpress.com/plugins/sell-media/" title="Sell Media WordPress plugin">Sell Media</a>', __( 'Shopping cart by ', 'sell_media' ) );
    }
}


/**
 * Gets the except of a post by post id
 *
 * @since 1.8.5
 * @author Thad Allender
 */
function sell_media_get_excerpt( $post_id, $excerpt_length = 140, $trailing_character = '&nbsp;&hellip;' ) {
    $the_post = get_post( $post_id );
    $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_excerpt ) );

    if ( empty( $the_excerpt ) )
      $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_content ) );

    $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );

    if( count( $words ) > $excerpt_length )
      $words = array_slice( $words, 0, $excerpt_length );

    $the_excerpt = implode( ' ', $words ) . $trailing_character;
    return $the_excerpt;
}

/**
 * Put the cart dialog markup in the footer
 *
 * @since 1.8.5
 */
function sell_media_cart_dialog(){
    $settings = sell_media_get_plugin_options();
    if ( ! is_page( $settings->checkout_page ) || ! is_page( $settings->login_page ) || ! is_page( $settings->dashboard_page ) ) : ?>
        <div id="sell-media-dialog-box" class="sell-media-dialog-box" style="display:none">
            <div id="sell-media-dialog-box-target"></div>
        </div>
        <div id="sell-media-dialog-overlay" class="sell-media-dialog-overlay" style="display:none"></div>
    <?php endif; ?>
    <?php if ( is_page( $settings->checkout_page ) && ! empty ( $settings->terms_and_conditions ) ) : ?>
        <div id="sell-media-empty-dialog-box" class="sell-media-dialog-box" style="display:none">
            <span class="close">&times;</span>
            <div class="content">
                <p><?php echo stripslashes_deep( nl2br( $settings->terms_and_conditions ) ); ?></p>
            </div>
        </div>
        <div id="sell-media-empty-dialog-overlay" class="sell-media-dialog-overlay" style="display:none"></div>
    <?php endif;
}
add_action( 'wp_footer', 'sell_media_cart_dialog' );