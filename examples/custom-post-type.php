<?php
function devtb_register_cpt() {
    register_post_type( 'portfolio', array(
        'public' => true,
        'label' => 'Portfolio',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
    ) );
}
add_action( 'init', 'devtb_register_cpt' );
