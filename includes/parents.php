<?php
/**
 * If the user is checking out for a group parent level, we want to let them
 * choose the number of seats to purchase if there is a variable amount. We
 * also want to show them the price per seat if there is one set and the levels
 * that group members will be able to claim.
 *
 * @since 1.0
 */
function pmprogroupacct_pmpro_checkout_boxes_parent() {
	// Get the level being checked out for.
	$level = pmpro_getLevelAtCheckout();

	// Get the group settings for this level.
	$settings = null;
	if ( ! empty( $level->id ) ) {
		$settings = pmprogroupacct_get_settings_for_level( $level->id );
	}

	// If there are no settings, then this is not a group parent level. Bail.
	if ( empty( $settings ) ) {
		return;
	}

	// Build the "seat number" and "pricing" messages to show.
	$static_seat_number_message = ''; // If empty, the range of seats will be shown with a number input.
	$pricing_message = '';
	switch ( $settings['pricing_model'] ) {
		case 'none':
			$static_seat_number_message = ( $settings['min_seats'] === $settings['max_seats'] ) ? 
				sprintf(
					/* translators: %d: Number of seats */
					_n(
						'This purchase includes %s additional seat.',
						'This purchase includes %s additional seats.',
						$settings['min_seats'],
						'pmpro-group-accounts'
					),
					number_format_i18n( $settings['min_seats'] )
				) : '';
			break;
		case 'fixed':
			$static_seat_number_message = ( $settings['min_seats'] === $settings['max_seats'] ) ? 
				sprintf(
					/* translators: %d: Number of seats */
					_n(
						'You are purchasing %s additional seat.',
						'You are purchasing %s additional seats.',
						$settings['min_seats'],
						'pmpro-group-accounts'
					),
					number_format_i18n( $settings['min_seats'] )
				) : '';
			switch ( $settings['price_application'] ) {
				case 'both':
					$pricing_message = sprintf( esc_html__( 'The price per seat is %s.', 'pmpro-group-accounts' ), esc_html( pmpro_formatPrice( $settings['pricing_model_settings'] ) ) );
					break;
				case 'initial':
					$pricing_message = sprintf( esc_html__( 'You will be charged an additional %s per seat for the initial payment only.', 'pmpro-group-accounts' ), esc_html( pmpro_formatPrice( $settings['pricing_model_settings'] ) ) );
					break;
				case 'recurring':
					$pricing_message = sprintf( esc_html__( 'You will be charged an additional %s per seat with each recurring payment.', 'pmpro-group-accounts' ), esc_html( pmpro_formatPrice( $settings['pricing_model_settings'] ) ) );
					break;
			}
			break;
	}

	// Build the "child levels" message to show.
	$all_levels = pmpro_getAllLevels( true, true );
	$child_level_names = array();
	foreach ( $all_levels as $child_level ) {
		if ( in_array( $child_level->id, $settings['child_level_ids'] ) ) {
			$child_level_names[] = $child_level->name;
		}
	}
	$child_levels_message = sprintf( _n( 'Group members will be able to claim the %s membership level.', 'Group members will be able to claim the following membership levels: %s.', count( $child_level_names ) ,'pmpro-group-accounts' ), implode( ', ', $child_level_names ) );

	// Build the checkout box.
	// We can check if there are a variable amount of seats by checking if min_seats is equal to max_seats.
	// The seats option should be a number input defaulting to the minimum seats.
	?>
	<fieldset id="pmprogroupacct_parent_fields" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fieldset', 'pmprogroupacct_parent_fields' ) ); ?>">
		<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card' ) ); ?>">
			<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_card_content' ) ); ?>">
				<legend class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_legend' ) ); ?>">
					<h2 class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_heading pmpro_font-large' ) ); ?>"><?php esc_html_e( 'Group Account Information', 'pmpro-group-accounts' ); ?></h2>
				</legend>
				<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_fields' ) ); ?>">
					<?php
					// Show seats.
					if ( ! empty( $static_seat_number_message ) ) {
						?>
						<input type="hidden" name="pmprogroupacct_seats" value="<?php echo esc_attr( $settings['min_seats'] ); ?>" />
						<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field-seats', 'pmpro_form_field-seats' ) ); ?>">
							<?php echo esc_html( $static_seat_number_message ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field-seats', 'pmpro_form_field-seats' ) ); ?>">
							<label for="pmprogroupacct_seats" class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_label' ) ); ?>"><?php esc_html_e( 'Number of Seats', 'pmpro-group-accounts' ); ?></label>
							<input class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_input pmpro_form_input-number pmpro_alter_price', 'pmprogroupacct_seats' ) ); ?>" id="pmprogroupacct_seats" name="pmprogroupacct_seats" type="number" min="<?php echo esc_attr( $settings['min_seats'] ); ?>" max="<?php echo esc_attr( $settings['max_seats'] ); ?>" value="<?php echo esc_attr( $settings['min_seats'] ); ?>" />
							<p class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_hint' ) ); ?>"><?php printf( esc_html__( 'Choose the number of seats to purchase. You can purchase between %s and %s seats.', 'pmpro-group-accounts' ), esc_html( number_format_i18n( ( (int)$settings['min_seats'] ) ) ), esc_html( number_format_i18n( (int)$settings['max_seats'] ) ) ); ?></p>
						</div> <!-- end .pmpro_form_field-seats -->
						<?php
					}

					// Show pricing.
					if ( ! empty( $pricing_message ) ) {
						?>
						<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field-pricing', 'pmpro_form_field-pricing' ) ); ?>">
							<?php echo esc_html( $pricing_message ); ?>
						</div>
						<?php
					}

					// Show child levels.
					?>
					<div class="<?php echo esc_attr( pmpro_get_element_class( 'pmpro_form_field pmpro_form_field-child-levels', 'pmpro_form_field-child-levels' ) ); ?>">
						<?php echo esc_html( $child_levels_message ); ?>
					</div>
				</div> <!-- end .pmpro_form_fields -->
			</div> <!-- end .pmpro_card_content -->
		</div> <!-- end .pmpro_card -->
	</fieldset> <!-- end #pmprogroupacct_parent_fields -->
	<?php
}
add_action( 'pmpro_checkout_boxes', 'pmprogroupacct_pmpro_checkout_boxes_parent' );

/**
 * If the user is checking out for a group parent level, we need to make sure
 * that their checkout selections are valid.
 *
 * @since 1.0
 *
 * @param bool $continue_checkout Whether or not to continue with checkout.
 * @return bool Whether or not to continue with checkout.
 */
function pmprogroupacct_pmpro_registration_checks_parent( $continue_checkout ) {
	// If there is already a checkout error, bail.
	if ( ! $continue_checkout ) {
		return $continue_checkout;
	}

	// Get the level being checked out for.
	$level = pmpro_getLevelAtCheckout();

	// Get the group settings for this level.
	$settings = null;
	if ( ! empty( $level->id ) ) {
		$settings = pmprogroupacct_get_settings_for_level( $level->id );
	}

	// If there are no settings, then this is not a group parent level. Bail.
	if ( empty( $settings ) ) {
		return $continue_checkout;
	}

	// If the number of seats entered is not an integer, show an error.
	if ( ! isset( $_REQUEST['pmprogroupacct_seats'] ) || ! is_numeric( $_REQUEST['pmprogroupacct_seats'] ) ) {
		$continue_checkout = false;
		pmpro_setMessage( esc_html__( 'The number of seats must be a whole number.', 'pmpro-group-accounts' ), 'pmpro_error' );
	}
	$seats = isset( $_REQUEST['pmprogroupacct_seats'] ) ? intval( $_REQUEST['pmprogroupacct_seats'] ) : 0;

	// If the number of seats is less than the minimum, show an error.
	if ( $seats < $settings['min_seats'] ) {
		$continue_checkout = false;
		pmpro_setMessage( sprintf( esc_html__( 'You must purchase at least %s seats.', 'pmpro-group-accounts' ), esc_html( number_format_i18n( (int)$settings['min_seats'] ) ) ), 'pmpro_error' );
	}

	// If the number of seats is greater than the maximum, show an error.
	if ( $seats > $settings['max_seats'] ) {
		$continue_checkout = false;
		pmpro_setMessage( sprintf( esc_html__( 'You cannot purchase more than %s seats.', 'pmpro-group-accounts' ), esc_html( number_format_i18n( (int)$settings['max_seats'] ) ) ), 'pmpro_error' );
	}

	// Check if this parent already has a group for this level. If so, check if $seats is greater than the number of seats in the group.
	$existing_group = PMProGroupAcct_Group::get_group_by_parent_user_id_and_parent_level_id( get_current_user_id(), $level->id );
	if ( ! empty( $existing_group ) ) {
		// If there are not enough seats for all the active members, show an error.
		$member_count = $existing_group->get_active_members( true );
		if ( $seats < $member_count ) {
			$continue_checkout = false;
			pmpro_setMessage( sprintf( esc_html__( 'There are currently %s members in your group. You must purchase at least that many seats.', 'pmpro-group-accounts' ), esc_html( number_format_i18n( (int)$member_count ) ) ) , 'pmpro_error' );
		}
	}

	return $continue_checkout;
}
add_filter( 'pmpro_registration_checks', 'pmprogroupacct_pmpro_registration_checks_parent' );

/**
 * If the user is checking out for a group parent level, we need to
 * add the seat price to the checkout level.
 *
 * @since 1.0
 *
 * @param object $level The level being checked out for.
 * @return object The level being checked out for.
 */
function pmprogroupacct_pmpro_checkout_level_parent( $level ) {
	// Get the group settings for this level.
	$settings = null;
	if ( ! empty( $level->id ) ) {
		$settings = pmprogroupacct_get_settings_for_level( $level->id );
	}

	// If there are no settings, then this is not a group parent level. Bail.
	if ( empty( $settings ) ) {
		return $level;
	}

	// Get the number of seats being purchased. This is either the number of seats entered or the minimum seats if set.
	$seats = intval( isset( $_REQUEST['pmprogroupacct_seats'] ) ? $_REQUEST['pmprogroupacct_seats'] : $settings['min_seats'] );

	// If the number of seats is not an integer, bail.
	if ( empty( $seats ) || ! is_numeric( $seats ) ) {
		return $level;
	}

	// Get the price per seat.
	$seat_cost = 0;
	switch ( $settings['pricing_model'] ) {
		case 'none':
			break;
		case 'fixed':
			$seat_cost = $seats * (float)$settings['pricing_model_settings'];
			break;
	}

	// If the price per seat is not a number or negative, bail.
	if ( ! is_numeric( $seat_cost ) || $seat_cost < 0 ) {
		return $level;
	}

	// Add the price per seat to the level based on the price application setting.
	switch ( $settings['price_application'] ) {
		case 'both':
			$level->initial_payment += $seat_cost;
			$level->billing_amount += $seat_cost;
			// If the level is not already recurring, default to 1 per Month.
			if ( empty ( $level->cycle_number ) ) {
				$level->cycle_number = 1;
			}
			if ( empty( $level->cycle_period ) ) {
				$level->cycle_period = 'Month';
			}
			break;
		case 'initial':
			$level->initial_payment += $seat_cost;
			break;
		case 'recurring':
			$level->billing_amount += $seat_cost;
			// If the level is not already recurring, default to 1 per Month.
			if ( empty ( $level->cycle_number ) ) {
				$level->cycle_number = 1;
			}
			if ( empty( $level->cycle_period ) ) {
				$level->cycle_period = 'Month';
			}
			break;
	}

	return $level;
}
add_filter( 'pmpro_checkout_level', 'pmprogroupacct_pmpro_checkout_level_parent' );

/**
 * If the user just completed checkout for a group parent level, we need to
 * create the group.
 *
 * @since 1.0
 *
 * @param int $user_id The ID of the user who just completed checkout.
 */
function pmprogroupacct_pmpro_after_checkout_parent( $user_id ) {
	// Get the level being checked out for.
	$level = pmpro_getLevelAtCheckout();

	// Get the group settings for this level.
	$settings = null;
	if ( ! empty( $level->id ) ) {
		$settings = pmprogroupacct_get_settings_for_level( $level->id );
	}

	// If there are no settings, then this is not a group parent level. Bail.
	if ( empty( $settings ) ) {
		return;
	}

	// Get the number of seats being purchased.
	$seats = isset( $_REQUEST['pmprogroupacct_seats'] ) ? intval( $_REQUEST['pmprogroupacct_seats'] ) : 0;

	// There were no seats purchased or included. Bail.
	if ( ! $seats ) {
		return;
	}

	// Check if there is already a group for this user and level.
	$existing_group = PMProGroupAcct_Group::get_group_by_parent_user_id_and_parent_level_id( $user_id, $level->id );
	if ( ! empty( $existing_group ) ) {
		// There is already a group for this user and level. Let's update the number of seats.
		$existing_group->update_group_total_seats( $seats );
		return;
	} else {
		// There is not already a group for this user and level. Let's create one.
		PMProGroupAcct_Group::create( $user_id, $level->id, $seats );
	}
}
add_action( 'pmpro_after_checkout', 'pmprogroupacct_pmpro_after_checkout_parent' );

/**
 * If a parent loses a membership level that they have a group for,
 * we need to remove all members from the group.
 *
 * @since 1.0
 *
 * @param $old_user_levels array The old levels the users had.
 */
function pmprogroupacct_pmpro_after_all_membership_level_changes_parent( $old_user_levels ) {
	// Track if we cancel a membership during this function.
	// If so, we need to make sure to run pmpro_do_action_after_all_membership_level_changes() afterwards.
	$cancelled_membership = false;

	// Loop through all users who have had changed levels.
	foreach ( $old_user_levels as $user_id => $old_levels ) {
		// Get the IDs of the user's old levels.
		$old_level_ids = wp_list_pluck( $old_levels, 'id' );

		// Get the new level for this user.
		$new_levels    = pmpro_getMembershipLevelsForUser( $user_id );
		$new_level_ids = wp_list_pluck( $new_levels, 'id' );		

		// Make sure the user has a group for any group parent levels they gained.
		$received_level_ids = array_diff( $new_level_ids, $old_level_ids );
		foreach ( $received_level_ids as $received_level_id ) {
			pmprogroupacct_create_free_group_if_needed( $user_id, $received_level_id );
		}


		// Check if the parent has a group for any of the levels they lost.
		$lost_level_ids = array_diff( $old_level_ids, $new_level_ids );
		foreach ( $lost_level_ids as $lost_level_id ) {
			$existing_group = PMProGroupAcct_Group::get_group_by_parent_user_id_and_parent_level_id( $user_id, $lost_level_id );
			if ( ! empty( $existing_group ) ) {
				// There is a group for this parent and level. Let's get all the active members for this group and cancel their group level.
				$active_members = $existing_group->get_active_members();
				foreach ( $active_members as $active_member ) {
					pmpro_cancelMembershipLevel( $active_member->group_child_level_id, $active_member->group_child_user_id );
					$cancelled_membership = true;
				}
			}
		}
	}

	// If we cancelled a membership during this function, we need to make sure to run pmpro_do_action_after_all_membership_level_changes() afterwards
	// so that cancelled users are removed from the corresponding groups.
	if ( $cancelled_membership ) {
		pmpro_do_action_after_all_membership_level_changes();
	}
}
// Hook at a late priority since we may change further levels and need to run pmpro_do_action_after_all_membership_level_changes() again.
add_action( 'pmpro_after_all_membership_level_changes', 'pmprogroupacct_pmpro_after_all_membership_level_changes_parent', 20, 1 );

/**
 * Add an invoice bullet if the level purchased with the invoice that we are showing
 * has a group associated with it.
 *
 * @since 1.0
 *
 * @param MemberOrder $invoice The invoice being shown.
 */
function pmprogroupacct_pmpro_invoice_bullets_bottom_parent( $invoice ) {
	// Try to get a group related to this invoice.
	$group = PMProGroupAcct_Group::get_group_by_parent_user_id_and_parent_level_id( $invoice->user_id, $invoice->membership_id );

	// If there is no group, bail.
	if ( empty( $group ) ) {
		return;
	}

	// Show an invoice bullet with the group code, seats purchased, and a link to manage the group.
	?>
	<li>
		<?php
		echo '<strong>' . esc_html__( 'Group Account', 'pmpro-group-accounts' ) . '</strong>: ';
		/* translators: 1: Group code, 2: Number of seats claimed, 3: Total number of seats in the group. */
		printf(
			esc_html__( 'Users can join your group by using the %1$s code at checkout (%2$s/%3$s seats claimed).', 'pmpro-group-accounts' ),
			'<span class="' . esc_attr( pmpro_get_element_class( 'pmpro_tag pmpro_tag-discount-code', 'pmpro_tag-discount-code' ) ) . '">' . esc_html( $group->group_checkout_code ) . '</span>',
			esc_html( number_format_i18n( (int)$group->get_active_members( true ) ) ),
			esc_html( number_format_i18n( (int)$group->group_total_seats ) )
		);

		// Check if we have a "manage group" page set.
		$manage_group_url = pmpro_url( 'pmprogroupacct_manage_group' );
		if ( ! empty( $manage_group_url ) ) {
			?>
			<a href="<?php echo esc_url( add_query_arg( 'pmprogroupacct_group_id', $group->id, $manage_group_url ) ); ?>"><?php esc_html_e( 'Manage Group', 'pmpro-group-accounts' ); ?></a>
			<?php
		}
		?>
	</li>
	<?php
}
add_action( 'pmpro_invoice_bullets_bottom', 'pmprogroupacct_pmpro_invoice_bullets_bottom_parent' );

/**
 * When a user logs in, check if they have all needed groups for their levels.
 * If not, create empty groups for them.
 *
 * @since 1.0.1
 *
 * @param string $user_login The user's login.
 * @param WP_User $user The user object.
 */
function pmprogroupacct_wp_login_parent( $user_login, $user ) {
	// Make sure that PMPro is enabled.
	if ( ! function_exists( 'pmpro_getMembershipLevelsForUser' ) ) {
		return;
	}

	// Get the user's levels.
	$levels = pmpro_getMembershipLevelsForUser( $user->ID );

	// Loop through the user's levels and create empty groups if needed.
	foreach ( $levels as $level ) {
		pmprogroupacct_create_free_group_if_needed( $user->ID, $level->id );
	}
}
add_action( 'wp_login', 'pmprogroupacct_wp_login_parent', 10, 2 );


/**
 * Creates an "free" group if needed for a given parent user and level.
 *
 * A "free" group is a new, empty group that is created with the maximum number
 * of free seats for the level. We do not want to give paid seats to a group
 * until the user has completed checkout and paid for them.
 *
 * @since 1.0.1
 *
 * @param int $user_id The ID of the parent user.
 * @param int $level_id The ID of the level.
 */
function pmprogroupacct_create_free_group_if_needed( $user_id, $level_id ) {
	// Get the group settings for this level.
	$settings = pmprogroupacct_get_settings_for_level( $level_id );

	// If there are no settings, then this is not a group parent level. Bail.
	if ( empty( $settings ) ) {
		return;
	}

	// Check if there is already a group for this user and level.
	$existing_group = PMProGroupAcct_Group::get_group_by_parent_user_id_and_parent_level_id( $user_id, $level_id );
	if ( ! empty( $existing_group ) ) {
		return;
	}

	// Create a group for this user and level.
	// If the pricing model is free, give them the max seats.
	// Otherwise, give them them 0. The `pmpro_after_checkout` filter will update the seats at chekout if needed.
	$seats = ( $settings['pricing_model'] === 'none' ) ? $settings['max_seats'] : 0;
	PMProGroupAcct_Group::create( $user_id, $level_id, $seats );
}
