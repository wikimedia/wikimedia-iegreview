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
 * @coversDefaultClass \Wikimedia\IEGReview\Arrays
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class ArrayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::difference
	 * @dataProvider differenceProvider
	 */
	public function testDifference( $from, $to, $expectedAdd, $expectedRemove ) {
		$diff = Arrays::difference( $from, $to );
		$toAdd = $diff['add'];
		$toRemove = $diff['remove'];
		$this->assertEquals( $toAdd, $expectedAdd );
		$this->assertEquals( $toRemove, $expectedRemove );
	}


	public function differenceProvider() {
		return array(
			array(
				array( 1, 2, 3 ),
				array( 4, 5, 6 ),
				array( 4, 5, 6 ),
				array( 1, 2, 3 )
			),
			array(
				array( 1, 2, 3, 4 ),
				array( 1, 2, 3 ),
				array(),
				array( 3 => 4 )
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3, 4 ),
				array( 3 => 4 ),
				array()
			),
			array(
				array( 1, 2, 3 ),
				array( 1, 2, 3 ),
				array(),
				array()
			),
			array(
				array( 1, 2, 3, 4 ),
				array( 2, 3, 4, 5 ),
				array( 3 => 5 ),
				array( 0 => 1 )
			),
			array(
				array(),
				array(),
				array(),
				array()
			),
			array(
				array( 'Hello world' ),
				array( 'Test string'),
				array( 'Test string' ),
				array( 'Hello world' )
			),
			// Both of the following tests will fail because the method signature expects arrays
			array(
				'Lorem Ipsum',
				'Ipsum Test',
				'Test',
				'Lorem'
			),
			array(
				'',
				'',
				'',
				''
			)
		);
	}

}