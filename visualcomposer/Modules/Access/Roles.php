<?php

namespace VisualComposer\Modules\Access;


class Roles {

	protected $postTypes = false;
	protected $excludedPostTypes = false;
	protected $parts = [
		'post_types',
		'backend_editor',
		'frontend_editor',
		'post_settings',
		'settings',
		'templates',
		'shortcodes',
		'grid_builder',
		'presets',
	];

	/**
	 * Get list of parts
	 * @return mixed|void
	 */
	public function getParts() {
		return apply_filters( 'vc:v:access:roles:get_parts', $this->parts );
	}

	/**
	 * Check required capability for this role to have user access.
	 *
	 * @param $part
	 *
	 * @return array
	 */
	public function getPartCapability( $part ) {
		return 'settings' !== $part ? [
			'edit_posts',
			'edit_pages',
		] : 'manage_options';
	}

	public function hasRoleCapability( $role, $caps ) {
		$has = false;
		$wp_role = get_role( $role );
		if ( is_string( $caps ) ) {
			$has = $wp_role->has_cap( $caps );
		} elseif ( is_array( $caps ) ) {
			$i = 0;
			while ( false === $has && $i < count( $caps ) ) {
				$has = $this->hasRoleCapability( $role, $caps[ $i ++ ] );
			}
		}

		return $has;
	}

	public function getWpRoles() {
		global $wp_roles;
		if ( function_exists( 'wp_roles' ) ) {
			return $wp_roles;
		} else {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		return $wp_roles;
	}

	public function save( $params = [ ] ) {
		$data = [ 'message' => '' ];
		$roles = $this->getWpRoles();
		$editable_roles = get_editable_roles();
		foreach ( $params as $role => $parts ) {
			if ( is_string( $parts ) ) {
				$parts = json_decode( stripslashes( $parts ), true );
			}
			if ( isset( $editable_roles[ $role ] ) ) {
				foreach ( $parts as $part => $settings ) {
					$part_key = RoleAccess::getInstance()
					                      ->who( $role )
					                      ->part( $part )
					                      ->getStateKey();
					$stateValue = '0';
					$roles->use_db = false; // Disable saving in DB on every cap change
					foreach ( $settings as $key => $value ) {
						if ( '_state' === $key ) {
							$stateValue = in_array( $value, [
								'0',
								'1',
							] ) ? (boolean) $value : $value;
						} else {
							if ( empty( $value ) ) {
								$roles->remove_cap( $role, $part_key . '/' . $key );
							} else {
								$roles->add_cap( $role, $part_key . '/' . $key, true );
							}
						}
					}
					$roles->use_db = true; //  Enable for the lat change in cap of role to store data in DB
					$roles->add_cap( $role, $part_key, $stateValue );
				}
			}
		}
		$data['message'] = __( 'Roles settings successfully saved.', 'js_composer' );

		return $data;
	}

	public function getPostTypes() {
		if ( false === $this->postTypes ) {
			$this->postTypes = [ ];
			$excluded = $this->getExcludedPostTypes();
			foreach ( get_post_types( [ 'public' => true ] ) as $post_type ) {
				if ( ! in_array( $post_type, $excluded ) ) {
					$this->postTypes[] = [ $post_type, $post_type ];
				}
			}
		}

		return $this->postTypes;
	}

	/**
	 * @return array
	 */
	public function getExcludedPostTypes() {
		if ( false === $this->excludedPostTypes ) {
			$this->excludedPostTypes = apply_filters( 'vc:v:access:roles:get_excluded_post_types', [
				'attachment',
				'revision',
				'nav_menu_item',
				'mediapage',
			] );
		}

		return $this->excludedPostTypes;
	}
}
