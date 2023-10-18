jQuery( function( $ ) {
	var $extensionforgeGlider = $( '.extensionforge-promo .glider' );

	if ( $extensionforgeGlider.length ) {
		new Glider( $extensionforgeGlider.get( 0 ), {
			dots: '.dots',
			arrows: {
				prev: '.glider-prev',
				next: '.glider-next',
			},
		} );
	}
} );