<?php
class CMDR_Dual_Synchronizer {
	public static $error;
	
	// Single user update
	public static function cmdr_user_update( $user_id, $args = null, $user_email = null, $new_user = false ) {
		global $cmdr_fields_to_hide;
		
		$cmdr_user_fields = ( array ) unserialize( base64_decode( get_option( 'cmdr_user_fields' ) ) );
		
		if ( ! class_exists( 'CS_REST_Subscribers' ) ) {
			require_once CMDR_PLUGIN_PATH . 'campaignmonitor-createsend-php/csrest_subscribers.php';
		}
		
		$auth = array( 'api_key' => get_option( 'cmdr_api_key' ) );
		$wrap_s = new CS_REST_Subscribers( get_option( 'cmdr_list_id' ), $auth );

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}
		
		if ( ! is_array( $args ) ) {
			$args = array();
			
			$args[ 'EmailAddress' ] = $user->user_email;
			$args[ 'Name' ] = $user->first_name . ' ' . $user->last_name;
			foreach( $cmdr_user_fields as $key => $field ) {
				if ( ! in_array( $field, $cmdr_fields_to_hide ) ) {
					if ( is_scalar( get_user_meta( $user->ID, $field, true ) ) ) {
						$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => get_user_meta( $user->ID, $field, true ) );
					} else {
						$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => '' );
					}
				}
			}
		}
		
		if ( empty( $user_email ) ) {
			$user_email = $user->user_email;
		}
		
		if ( count( $args ) ) {
			if ( $new_user ) {
				$result = $wrap_s->add( $args );
			} else {
				$result = $wrap_s->update( $user_email, $args );
			}
			if ( ! $result->was_successful() ) {
				self::$error = $result->response;
				return false;
			}
		}
		
		return true;
	}
	
	// The main sync function
	public static function cmdr_mass_update() {
		global $cmdr_fields_to_hide;
		
		set_time_limit ( 3600 );
		
		$cmdr_user_fields = ( array ) unserialize( base64_decode( get_option( 'cmdr_user_fields' ) ) );
		
		if ( ! class_exists( 'CS_REST_Lists' ) ) {
			require_once CMDR_PLUGIN_PATH . 'campaignmonitor-createsend-php/csrest_lists.php';
		}
		if ( ! class_exists( 'CS_REST_Subscribers' ) ) {
			require_once CMDR_PLUGIN_PATH . 'campaignmonitor-createsend-php/csrest_subscribers.php';
		}
		
		$auth = array( 'api_key' => get_option( 'cmdr_api_key' ) );
		$wrap_l = new CS_REST_Lists( get_option( 'cmdr_list_id' ), $auth );
		$wrap_s = new CS_REST_Subscribers( get_option( 'cmdr_list_id' ), $auth );
		
		// Update custom fields
		$missing_fields = $cmdr_user_fields;

		$result = $wrap_l->get_custom_fields();
		if ( ! $result->was_successful() ) {
			self::$error = $result->response;
			return false;
		}
		if ( is_array( $result->response ) ) {
			foreach( $result->response as $key => $field ) {
				$k = str_replace( '[', '', str_replace( ']', '', $field->Key ) );
				
				if ( in_array( $k, $missing_fields ) ) {
					unset( $missing_fields[ array_search( $k, $missing_fields ) ] );
				}
			}
		}

		foreach( $missing_fields as $key => $field ) {
			if ( ! in_array( $field, $cmdr_fields_to_hide ) ) {
				$result = $wrap_l->create_custom_field( array(
					'FieldName' => $field,
					'Key' => $field,
					'DataType' => CS_REST_CUSTOM_FIELD_TYPE_TEXT
				) );
				if ( ! $result->was_successful() ) {
					self::$error = $result->response;
					return false;
				}
			}
		}

		// Update users
		$missing_users = array();
		$u = get_users();
		foreach( $u as $key => $value ) {
			$missing_users[] = $value->user_email;
		}

		$result = $wrap_l->get_active_subscribers( '', 1, 1000 );
		if ( ! $result->was_successful() ) {
			self::$error = $result->response;
			return false;
		}
		
		$i = 2;
		while ( count( $result->response->Results ) ) {
			if ( ! empty( $result->response->Results ) && is_array( $result->response->Results ) ) {
				foreach( $result->response->Results as $key => $subscriber ) {
					$user = get_user_by( 'email', $subscriber->EmailAddress );

					if ( $user ) {
						$args = array();
						
						if ( trim( $user->first_name . ' ' . $user->last_name ) != trim( $subscriber->Name ) ) {
							$args[ 'Name' ] = $user->first_name . ' ' . $user->last_name;
						}
						
						$custom_values = array();
						foreach( $subscriber->CustomFields as $field ) {
							$k = str_replace( '[', '', str_replace( ']', '', $field->Key ) );
							
							$custom_values[ $k ] = $field->Value;
						}
						foreach( $cmdr_user_fields as $key => $field ) {
							if ( ! in_array( $field, $cmdr_fields_to_hide ) ) {
								if ( empty( $custom_values[ $field ] ) || trim( $custom_values[ $field ] ) != trim( get_user_meta( $user->ID, $field, true ) ) ) {
									// We export scalar values only
									if ( is_scalar( get_user_meta( $user->ID, $field, true ) ) ) {
										$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => get_user_meta( $user->ID, $field, true ) );
									} else {
										$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => '' );
									}
								}
							}
						}
						
						if ( count( $args ) ) {
							$result = $wrap_s->update( $user->user_email, $args );
							if ( ! $result->was_successful() ) {
								self::$error = $result->response;
								return false;
							}
						}
					}
					
					if ( in_array( $subscriber->EmailAddress, $missing_users ) ) {
						unset( $missing_users[ array_search( $subscriber->EmailAddress, $missing_users ) ] );
					}
				}
			}
			
			$result = $wrap_l->get_active_subscribers( '', $i, 1000 );
			if ( ! $result->was_successful() ) {
				self::$error = $result->response;
				return false;
			}
			$i ++;
		}

		// Get bounced and unsubscribed users
		$unsubscribed = array();
		$result = $wrap_l->get_unsubscribed_subscribers();
		if ( ! $result->was_successful() ) {
			self::$error = $result->response;
			return false;
		}
		if ( ! empty( $result->response->Results ) && is_array( $result->response->Results ) ) {
			foreach( $result->response->Results as $key => $subscriber ) {
				$unsubscribed[] = $subscriber->EmailAddress;
			}
		}
		$bounced = array();
		$result = $wrap_l->get_bounced_subscribers();
		if ( ! $result->was_successful() ) {
			self::$error = $result->response;
			return false;
		}
		if ( ! empty( $result->response->Results ) && is_array( $result->response->Results ) ) {
			foreach( $result->response->Results as $key => $subscriber ) {
				$bounced[] = $subscriber->EmailAddress;
			}
		}

		$subscribers = array();
		foreach( $missing_users as $key => $user_email ) {
			if ( ! in_array( $user_email, $unsubscribed ) && ! in_array( $user_email, $bounced ) ) {
				// Subscriber does not exist, let's add him
				$user = get_user_by( 'email', $user_email );

				if ( $user ) {
					$args = array(
						'EmailAddress' => $user->user_email,
						'Name' => $user->first_name . ' ' . $user->last_name,
						'CustomFields' => array(
						)
					);

					foreach( $cmdr_user_fields as $key => $field ) {
						if ( ! in_array( $field, $cmdr_fields_to_hide ) ) {
							// We export scalar values only
							if ( is_scalar( get_user_meta( $user->ID, $field, true ) ) ) {
								$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => get_user_meta( $user->ID, $field, true ) );
							} else {
								$args[ 'CustomFields' ][] = array( 'Key' => $field, 'Value' => '' );
							}
						}
					}
					
					$subscribers[] = $args;
				}
			}
		}

		while ( count( $subscribers ) ) {
			$subscribers1000 = array();
			for ( $i = 0; $i < 1000; $i++ ) {
				$subscribers1000[] = array_shift( $subscribers );
				if ( ! count( $subscribers ) ) {
					break;
				}
			}

			$result = $wrap_s->import( $subscribers1000, true );
			if ( ! $result->was_successful() ) {
				self::$error = $result->response;
				return false;
			}
		}
		
		return true;
	}
}
