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

/**
 * Manage authentication and authorization.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class AuthManager extends \Wikimedia\Slimapp\Auth\AuthManager {

	/**
	* Is the user an administrator?
	* @return bool True if the user is authorized to perfom admin tasks, false
	* otherwise
	*/
	public function isAdmin() {
		$user = $this->getUserData();
		return $user ? $user->isAdmin() : false;
	}

	/**
	* Is the user a reviewer?
	* @return bool True if the user is authorized to perfom review tasks, false
	* otherwise
	*/
	public function isReviewer() {
		$user = $this->getUserData();
		return $user ? $user->isReviewer() : false;
	}

	/**
	* Is the user allowed to read reports?
	* @return bool True if the user is authorized to read reports, false
	* otherwise
	*/
	public function canViewReports() {
		$user = $this->getUserData();
		return $user ? $user->isAdmin() || $user->canViewReports() : false;
	}

} // end AuthManager
