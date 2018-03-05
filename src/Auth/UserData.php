<?php
/**
 * @section LICENSE
 * This file is part of Wikimedia Grants Review application.
 *
 * Wikimedia Grants Review application is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Wikimedia Grants Review application is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Wikimedia Grants Review application.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @file
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview\Auth;

use Wikimedia\Slimapp\Auth\Password;
use Wikimedia\Slimapp\Auth\UserData as SlimUserData;

/**
 * Basic user information.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class UserData implements SlimUserData {

	/**
	* @var array $data
	*/
	protected $data;

	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	* Get user's unique numeric id.
	* @return int
	*/
	public function getId() {
		return isset( $this->data['id'] ) ? $this->data['id'] : false;
	}

	/**
	* Get user's password.
	* @return string
	**/
	public function getPassword() {
		return isset( $this->data['password'] ) ?
		$this->data['password'] : '';
	}

	/**
	* Is this user blocked from logging into the application?
	* @return bool True if user should not be allowed to log in to the
	*   application, false otherwise
	*/
	public function isBlocked() {
		return $this->getFlag( 'blocked' );
	}
	/**
	* Is the user an administrator?
	* @return bool True if the user is authorized to perform admin tasks,
	* false otherwise
	*/
	public function isAdmin() {
		return $this->getFlag( 'isadmin' );
	}
	/**
	* Is the user a reviewer?
	* @return bool True if the user is authorized to perfom review tasks, false
	* otherwise
	*/
	public function isReviewer() {
		return $this->getFlag( 'reviewer' );
	}

	protected function getFlag( $flag ) {
		return isset( $this->data[$flag] ) ? (bool)$this->data[$flag] : false;
	}
	public function canViewReports() {
		return $this->getFlag( 'viewreports' );
	}

}
