<?php
/**
 * @section LICENSE
 * This file is part of Wikimedia IEG Grant Review application.
 *
 * Wikimedia IEG Grant Review application is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Wikimedia IEG Grant Review application is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Wikimedia IEG Grant Review application.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @file
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview;

use Wikimedia\IEGReview\Dao\Users as UsersDao;
use Wikimedia\IEGReview\Password;

/**
 * Manage authentication and authorization.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class AuthManager {

	const USER_SESSION_KEY = 'AUTH_USER';
	const NEXTPAGE_SESSION_KEY = 'AUTH_NEXTPAGE';

	/**
	 * @var UsersDao $dao
	 */
	protected $dao;


	/**
	 * @param UsersDao $dao DAO
	 */
	public function __construct( UsersDao $dao = null ) {
		$this->dao = $dao ?: new UsersDao();
	}


	/**
	 * Get the current user's information
	 * @return array|bool User information or false if not available
	 */
	public function getUser() {
		if ( isset( $_SESSION[self::USER_SESSION_KEY] ) ) {
			return $_SESSION[self::USER_SESSION_KEY];

		} else {
			return false;
		}
	}


	/**
	 * Get the current user's Id.
	 * @return int|bool Numeric user id or false if not available
	 */
	public function getUserId() {
		$user = $this->getUser();
		return $user ? $user['id'] : false;
	}


	/**
	 * Store the user's information.
	 * @param array $user User information
	 */
	public function setUser( $user ) {
		$_SESSION[self::USER_SESSION_KEY] = $user;
	}


	/**
	 * Is the user authenticated?
	 * @return bool True if authenticated, false otherwise
	 */
	public function isAuthenticated() {
		return $this->getUser() !== false;
	}


	/**
	 * Is the user anonymous?
	 * @return bool True if the user is not authenticated, false otherwise
	 */
	public function isAnonymous() {
		return $this->getUser() === false;
	}


	/**
	 * Is the user an administrator?
	 * @return bool True if the user is authorized to perfom admin tasks, false
	 * otherwise
	 */
	public function isAdmin() {
		$user = $this->getUser();
		return $user ? (bool)$user['isadmin'] : false;
	}


	/**
	 * Is the user a reviewer?
	 * @return bool True if the user is authorized to perfom review tasks, false
	 * otherwise
	 */
	public function isReviewer() {
		$user = $this->getUser();
		return $user ? (bool)$user['reviewer'] : false;
	}


	/**
	 * Is the user allowed to read reports?
	 * @return bool True if the user is authorized to read reports, false
	 * otherwise
	 */
	public function canViewReports() {
		$user = $this->getUser();
		if ( $user ) {
			return (bool)$user['isadmin'] || (bool)$user['viewreports'];
		} else {
			return false;
		}
	}

	/**
	 * Attempt to authenticate a user.
	 * @param string $uname Username
	 * @param string $password Password
	 * @return bool True if authentication is successful, false otherwise
	 */
	public function authenticate( $uname, $password ) {
		$user = $this->dao->getUser( $uname );
		$check = Password::comparePasswordToHash( $password, $user['password'] );
		if ( $check && !$user['blocked'] ) {
			// clear session
			foreach ( $_SESSION as $key => $value ) {
				unset( $_SESSION[$key] );
			}

			// generate new session id
			session_regenerate_id(true);

			// store user info in session
			$this->setUser( $user );

			return true;

		} else {
			return false;
		}
	}


	/**
	 * Remove authentication.
	 */
	public function logout() {
		// clear session
		foreach ( $_SESSION as $key => $value ) {
			unset( $_SESSION[$key] );
		}

		// delete the session cookie on the client
		if ( ini_get( 'session.use_cookies' ) ) {
			$params = session_get_cookie_params();
			setcookie( session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']
			);
		}

		// destroy local session storage
		session_destroy();
		// generate new session id
		session_regenerate_id(true);
	}

} //end AuthManager
