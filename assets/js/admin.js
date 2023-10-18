jQuery( function( $ ) {
	// Init color field
	$( '.color-field' ).wpColorPicker();

	if ( window.bpVerifiedMemberAdmin && window.bpVerifiedMemberAdmin.ajaxUrl ) {
		// Handle tooltips
		var initTooltips = function( tooltipContent ) {
			// Don't add tooltip if there's already one
			if ( $( this ).siblings( '.bp-vip-badge-tooltip' ).length )
				return;

			// Add tooltip to dom
			var $tooltip = $(
				'<span class="bp-vip-badge-tooltip" role="tooltip" style="visibility: hidden;">' +
					tooltipContent +
					'<span class="bp-vip-badge-tooltip-arrow" data-popper-arrow></span>' +
				'</span>'
			);
			$( this ).after( $tooltip );

			// Initialize Popper to handle tooltip
			if ( Popper.createPopper ) // Popper 2.x.x
				var badgeTooltip = Popper.createPopper( this, $tooltip.get( 0 ), {
					placement: 'top',
					modifiers: [
						{
							name: 'offset',
							options: {
								offset: [0, 5],
							},
						},
					],
				} );
			else // Popper 1.x.x
				var badgeTooltip = new Popper( this, $tooltip.get( 0 ), {
					placement: 'top',
					modifiers: {
						offset: {
							offset: '0, 5px',
						},
					},
				} );

			// Force update tooltip placement to prevent weird offset
			setTimeout( function() {
				badgeTooltip.update();
			}, 100 );

			// Show tooltip on hover
			$( this ).hover( function() {
				$tooltip.css( 'visibility', 'visible' );
			}, function() {
				$tooltip.css( 'visibility', 'hidden' );
			} );
		};
		var $vipBadges = $( '.bp-vip-badge' );
		$vipBadges.each( function() {
			var tooltip = window.bpVerifiedMemberAdmin.vipTooltip;
			if ( $( this ).parent().hasClass( 'bp-vip-by-role' ) )
				tooltip = window.bpVerifiedMemberAdmin.vipByRoleTooltip;
			else if ( $( this ).parent().hasClass( 'bp-vip-by-member-type' ) )
				tooltip = window.bpVerifiedMemberAdmin.vipByMemberTypeTooltip;

			initTooltips.bind( this )( tooltip );
		} );

		var unvipBadges = $( '.bp-unvip-badge' );
		unvipBadges.each( function() {
			initTooltips.bind( this )( window.bpVerifiedMemberAdmin.unvipTooltip );
		} );

		// Handle toggling the vip status of a user when clicking the vip badge in the admin column
		var loading = false;
		$( 'a.bp-vip-member-toggle' ).on( 'click', function( e ) {
			e.preventDefault();

			// Bail if already loading or if the user belongs to a vip role or member type
			if ( loading || $( this ).hasClass( 'bp-vip-by-role' ) || $( this )
				.hasClass( 'bp-vip-by-member-type' ) )
				return;

			loading = true;

			var nonce = $( this ).data( 'bp-vip-member-toggle-nonce' );
			var userId = $( this ).data( 'user-id' );
			var $this = $( this );

			if ( !nonce || !userId )
				return;

			$this.html( '<span class="dashicons dashicons-update bp-vip-member-spin"></span>' );

			$.post( window.bpVerifiedMemberAdmin.ajaxUrl, {
				action: 'bp_vip_member_toggle',
				nonce: nonce,
				userId: userId,
			}, function( result ) {
				loading = false;

				if ( result.success )
					$this.html( result.data );
			} );
		} );

		$( '.bp-vip-member-new-requests-notice .notice-dismiss' ).on( 'click', function() {
			$.post( window.bpVerifiedMemberAdmin.ajaxUrl, { action: 'bp_vip_member_dismiss_new_requests_notice' } );
		} );
	}
} );