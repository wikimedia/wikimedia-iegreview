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
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */

namespace Wikimedia\IEGReview;

/**
 * Handle arrays.
 *
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class Arrays {

	/**
	 * Takes two arrays ( old and new ) and returns an array like:
	 * [ 'add' => Elements in new and not in old array
	 * 'remove'=> Elements in old array but not in new ]
	 *
	 * @param $from array Old array
	 * @param $to array New array
	 * @return array with elements only in $from and elements only in $to
	 */
	public static function difference( array $from, array $to ) {

		$toAdd = array_diff( $to, $from );
		$toRemove = array_diff( $from, $to );
		return array(
			'add' => $toAdd,
			'remove' => $toRemove
		);
	}

}
