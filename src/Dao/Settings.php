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

namespace Wikimedia\IEGReview\Dao;

/**
 * Data access object for Admin Settings in scholarship applications.
 *
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Settings extends AbstractDao {
	/**
	 * @return array Settings from DB
	 */
	public function getSettings() {
		$settings = array();
		$records = $this->fetchAll( 'SELECT setting_name,value FROM settings' );
		foreach ( $records as $idx => $row ) {
			$settings[ $row[ 'setting_name' ] ] = $row[ 'value' ];
		}
		return $settings;
	}

}
