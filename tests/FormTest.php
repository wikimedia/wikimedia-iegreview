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

namespace Wikimedia\IEGReview;

/**
 * @coversDefaultClass \Wikimedia\IEGReview\Form
 * @uses \Wikimedia\IEGReview\Form
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class FormTest extends \PHPUnit_Framework_TestCase {

	public function testRequired () {
		$form = new Form();
		$form->expectString( 'foo', array( 'required' => true ) );

		$this->assertFalse( $form->validate(), 'Form should be invalid' );
		$vals = $form->getValues();
		$this->assertArrayHasKey( 'foo', $vals );
		$this->assertNull( $vals['foo'] );
		$this->assertContains( 'foo', $form->getErrors() );
	}

	public function testDefaultWhenEmpty () {
		$form = new Form();
		$form->expectString( 'foo', array( 'default' => 'bar' ) );

		$this->assertTrue( $form->validate(), 'Form should be valid' );
		$vals = $form->getValues();
		$this->assertArrayHasKey( 'foo', $vals );
		$this->assertNull( $vals['foo'] );
		$this->assertSame( 'bar', $form->get( 'foo' ) );
		$this->assertNotContains( 'foo', $form->getErrors() );
	}

	public function testNotInArray () {
		$form = new Form();
		$form->expectInArray( 'foo', array( 'bar' ), array( 'required' => true ) );

		$this->assertFalse( $form->validate(), 'Form should be invalid' );
		$vals = $form->getValues();
		$this->assertArrayHasKey( 'foo', $vals );
		$this->assertNull( $vals['foo'] );
		$this->assertContains( 'foo', $form->getErrors() );
	}

	public function testInArray () {
		$_POST['foo'] = 'bar';
		$form = new Form();
		$form->expectInArray( 'foo', array( 'bar' ), array( 'required' => true ) );

		$this->assertTrue( $form->validate(), 'Form should be valid' );
		$vals = $form->getValues();
		$this->assertArrayHasKey( 'foo', $vals );
		$this->assertEquals( 'bar', $vals['foo'] );
		$this->assertNotContains( 'foo', $form->getErrors() );
	}

	public function testNotInArrayNotRequired () {
		unset( $_POST['foo'] );
		$form = new Form();
		$form->expectInArray( 'foo', array( 'bar' ) );

		$this->assertTrue( $form->validate(), 'Form should be valid' );
		$vals = $form->getValues();
		$this->assertArrayHasKey( 'foo', $vals );
		$this->assertEquals( '', $vals['foo'] );
		$this->assertNotContains( 'foo', $form->getErrors() );
	}

	public function testEncodeBasic () {
		$input = array(
			'foo' => 1,
			'bar' => 'this=that',
			'baz' => 'tom & jerry',
		);
		$output = Form::urlEncode( $input );
		$this->assertEquals( 'foo=1&bar=this%3Dthat&baz=tom+%26+jerry', $output );
	}

	public function testEncodeArray () {
		$input = array(
			'foo' => array( 'a', 'b', 'c' ),
			'bar[]' => array( 1, 2, 3 ),
		);
		$output = Form::urlEncode( $input );
		$this->assertEquals(
			'foo=a&foo=b&foo=c&bar%5B%5D=1&bar%5B%5D=2&bar%5B%5D=3', $output );
	}

	public function testQsMerge () {
		$_GET['foo'] = 1;
		$_GET['bar'] = 'this=that';
		$_GET['baz'] = 'tom & jerry';

		$output = Form::qsMerge();
		$this->assertEquals( 'foo=1&bar=this%3Dthat&baz=tom+%26+jerry', $output );

		$output = Form::qsMerge( array( 'foo' => 2, 'xyzzy' => 'grue' ) );
		$this->assertEquals( 'foo=2&bar=this%3Dthat&baz=tom+%26+jerry&xyzzy=grue', $output );
	}

	public function testQsRemove () {
		$_GET['foo'] = 1;
		$_GET['bar'] = 'this=that';
		$_GET['baz'] = 'tom & jerry';

		$output = Form::qsRemove();
		$this->assertEquals( 'foo=1&bar=this%3Dthat&baz=tom+%26+jerry', $output );

		$output = Form::qsRemove( array( 'bar' ) );
		$this->assertEquals( 'foo=1&baz=tom+%26+jerry', $output );
	}
}
