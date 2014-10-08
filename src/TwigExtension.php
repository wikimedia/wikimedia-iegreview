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

/**
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class TwigExtension extends \Twig_Extension {

	public function getName() {
		return 'iegreview';
	}

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('qsMerge', array($this, 'qsMerge')),
		);
	}

	public function getFilters() {
		return array(
			new \Twig_SimpleFilter(
				'wikitext', array( $this, 'wikitextFilterCallback' ),
				array( 'pre_escape' => 'html', 'is_safe' => array( 'html' ) )
			),
		);
	}

	public function qsMerge( $parms ) {
		return Form::qsMerge( $parms );
	}

	public function wikitextFilterCallback( $text ) {
		// TODO: add message caching
		// TODO: make the parsoid URL configurable
		$url = 'http://parsoid-lb.eqiad.wikimedia.org/enwiki/';
		$params = 'wt=' . urlencode( $text ) . '&body=1';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Content-Length: ' . strlen( $params ),
		) );
		curl_setopt( $ch, CURLOPT_ENCODING, 'gzip' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'IEG Grant review 0.1' );
		$body = curl_exec( $ch );
		curl_close( $ch );

		// Using a regex to parse html is generally not a sane thing to do,
		// but in this case we are trusting Parsoid to be returning clean HTML
		// and all we want to do is unwrap our payload from the
		// <body>...</body> tag.
		return preg_replace( '@^<body[^>]+>(.*)</body>$@', '$1', $body );
	}

}
