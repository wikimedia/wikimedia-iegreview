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


/*
 * @param $array1 First array //Actually, a SQL query result set here
 * @param $array2 Second array
 */
public static function difference( $array1, $array2 ) {

	$old_reviewers = array();
	foreach ( $array1 as $r ) {
		array_push( $old_reviewers, $r );
	}

	$new_reviewers = array();
	foreach ( $array2 as $r ) {
		array_push( $new_reviewers, $r );
	}

	$toAdd = array_diff( $new_reviewers, $old_reviewers );
	$toRemove = array_diff( $old_reviewers, $new_reviewers );

	return array(
		'add' => $toAdd,
		'remove' => $toRemove
	);

}

}
