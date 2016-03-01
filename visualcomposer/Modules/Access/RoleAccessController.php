<?php

namespace VisualComposer\Modules\Access;

class RoleAccessController extends Access {

	protected static $partNamePrefix = 'vc_v_access_rules_';
	protected $part = false;
	protected $roleName = false;
	protected $role = false;
	protected $validAccess = true;
	protected $mergedCaps = [
		'vc_row_inner_all' => 'vc_row_all',
		'vc_column_all' => 'vc_row_all',
		'vc_column_inner_all' => 'vc_row_all',
		'vc_row_inner_edit' => 'vc_row_edit',
		'vc_column_edit' => 'vc_row_edit',
		'vc_column_inner_edit' => 'vc_row_edit',
	];

	/**
	 * RoleAccessController constructor.
	 *
	 * @param $part
	 */
	public function __construct( $part ) {
		$this->part = $part;
	}

	/**
	 * Set role name
	 *
	 * @param $role_name
	 */
	public function setRoleName( $role_name ) {
		$this->roleName = $role_name;
	}

	/**
	 * Get part for role.
	 * @return bool
	 */
	public function getPart() {
		return $this->part;
	}

	/**
	 * Get state of the Vc access rules part
	 *
	 * @return mixed;
	 */
	public function getState() {
		$role = $this->getRole();
		$state = null;
		if ( $role && isset( $role->capabilities, $role->capabilities[ $this->getStateKey() ] ) ) {
			$state = $role->capabilities[ $this->getStateKey() ];
		}

		return apply_filters( 'vc:v:role:get_state:access_with_' . $this->getPart(), $state, $this->getRole() );
	}

	/**
	 * Set state for full part
	 *
	 * State can have 3 values:
	 * true - all allowed under this part;
	 * false - all disabled under this part;
	 * string|'custom' - custom settings. It means that need to check exact capability.
	 *
	 * @param bool $value
	 *
	 * @return bool
	 */
	public function setState( $value = true ) {
		$this->getRole() && $this->getRole()
		                         ->add_cap( $this->getStateKey(), $value );
	}

	/**
	 * Can user do what he doo
	 * Any rule has three types of state: true, false, string
	 *
	 * @param string $rule
	 * @param bool|true $check_state
	 *
	 * @return $this
	 */
	public function can( $rule = '', $check_state = true ) {
		if ( null === $this->getRole() ) {
			$this->setValidAccess( is_super_admin() );
		} elseif ( $this->getValidAccess() ) {
			// YES it is hard coded :)
			if ( 'administrator' === $this->getRole()->name && 'settings' === $this->getPart() && ( 'vc-roles-tab' === $rule || 'vc-updater-tab' === $rule ) ) {
				$this->setValidAccess( true );

				return $this;
			}
			$rule = $this->updateMergedCaps( $rule );

			if ( true === $check_state ) {
				$state = $this->getState();
				$return = false !== $state;
				if ( null === $state ) {
					$return = true;
				} elseif ( is_bool( $state ) ) {
					$return = $state;
				} elseif ( '' !== $rule ) {
					$return = $this->getCapRule( $rule );
				}
			} else {
				$return = $this->getCapRule( $rule );
			}
			$return = apply_filters( 'vc:v:role:can:access_with_' . $this->getPart(), $return, $this->getRole(), $rule );
			$return = apply_filters( 'vc:v:role:can:access_with_' . $this->getPart() . ':' . $rule, $return, $this->getRole() );
			$this->setValidAccess( $return );
		}

		return $this;
	}

	/**
	 * Can user do what he do
	 * Any rule has three types of state: true,false, string
	 */
	public function canAny() {
		if ( $this->getValidAccess() ) {
			$args = func_get_args();
			$this->checkMulti( [ $this, 'can' ], true, $args );
		}

		return $this;
	}

	/**
	 * Can user do what he do
	 * Any rule has three types of state: true,false, string
	 */
	public function canAll() {
		if ( $this->getValidAccess() ) {
			$args = func_get_args();
			$this->checkMulti( [ $this, 'can'], false, $args );
		}

		return $this;
	}

	/**
	 * Get capability for role
	 *
	 * @param $rule
	 *
	 * @return bool
	 */
	public function getCapRule( $rule ) {
		$rule = $this->getStateKey() . '/' . $rule;

		return $this->getRole() ? $this->getRole()->has_cap( $rule ) : false;
	}

	/**
	 * Add capability to role
	 *
	 * @param $rule
	 * @param bool $value
	 */
	public function setCapRule( $rule, $value = true ) {
		$role_rule = $this->getStateKey() . '/' . $rule;
		$this->getRole() && $this->getRole()->add_cap( $role_rule, $value );
	}

	/**
	 * Get all capability for this part
	 */
	public function getAllCaps() {
		$role = $this->getRole();
		$caps = [];
		if ( $role ) {
			$role = apply_filters( 'vc:v:role:get_all_caps:role', $role );
			if ( isset( $role->capabilities ) && is_array( $role->capabilities ) ) {
				foreach ( $role->capabilities as $key => $value ) {
					if ( preg_match( '/^' . $this->getStateKey() . '\//', $key ) ) {
						$rule = preg_replace( '/^' . $this->getStateKey() . '\//', '', $key );
						$caps[ $rule ] = $value;
					}
				}
			}
		}

		return $caps;
	}

	/**
	 * @return null|\WP_Role
	 * @throws \Exception
	 */
	public function getRole() {
		if ( ! $this->role ) {
			if ( ! $this->getRoleName() ) {
				throw new \Exception( 'roleName for role_manager is not set, please use ->who(roleName) method to set!' );
			}
			$this->role = get_role( $this->getRoleName() );
		}

		return $this->role;
	}

	/**
	 * @return null|string
	 */
	public function getRoleName() {
		return $this->roleName;
	}

	public function getStateKey() {
		return self::$partNamePrefix . $this->getPart();
	}

	public function checkState( $data ) {
		if ( $this->getValidAccess() ) {
			$this->setValidAccess( $this->getState() === $data );
		}

		return $this;
	}

	public function checkStateAny() {
		if ( $this->getValidAccess() ) {
			$args = func_get_args();
			$this->checkMulti( [ $this, 'checkState' ], true, $args );
		}

		return $this;
	}

	/**
	 * Return access value
	 * @return string
	 */
	public function __toString() {
		return (string) $this->get();
	}

	public function updateMergedCaps( $rule ) {
		if ( isset( $this->mergedCaps[ $rule ] ) ) {
			return $this->mergedCaps[ $rule ];
		}

		return $rule;
	}

	/**
	 * @return array
	 */
	public function getMergedCaps() {
		return $this->mergedCaps;
	}
}
