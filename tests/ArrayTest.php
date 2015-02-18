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
 * @coversDefaultClass \Wikimedia\IEGReview\Form
 * @uses \Wikimedia\IEGReview\Arrays
 * @author Niharika Kohli <nkohli@wikimedia.org>
 * @copyright © 2015 Niharika Kohli, Wikimedia Foundation and contributors.
 */
class ArrayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::difference
	 */
	public function testDifference() {
		$array1 = ['1', '2', '3'];			//Old
		$array2 = ['4', '5', '6'];			//New
		$diff = Arrays::difference( $array1, $array2 );
		$this->assertArrayHasKey( 'add', $diff );
		$this->assertArrayHasKey( 'remove', $diff );
		$this->assertEquals( $diff['add'], $array2 );
		$this->assertEquals( $diff['remove'], $array1 );

		$array1 = ['1', '2', '3', '4'];
		$array2 = ['1', '2', '3', '4'];
		$diff = Arrays::difference( $array1, $array2 );
		$this->assertEmpty( $diff['add'] );
		$this->assertEmpty( $diff['remove'] );

		$array1 = ['1', '2', '3' ];
		$array2 = ['1', '2', '3', '4'];
		$diff = Arrays::difference( $array1, $array2 );
		$this->assertEmpty( $diff['remove'] );
		$this->assertCount( 1, $diff['add'] );

		$array1 = ['1', '2', '3', '4'];
		$array2 = ['1', '2', '3'];
		$diff = Arrays::difference( $array1, $array2 );
		$this->assertEmpty( $diff['add'] );
		$this->assertCount( 1, $diff['remove'] );
	}

}