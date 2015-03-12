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

namespace Wikimedia\IEGReview\Dao;

/**
 * Data access object for reviews.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Niharika Kohli, Wikimedia Foundation and contributors.
 */
class Reviews extends AbstractDao {

	/**
	 * @var int|bool $userId
	 */
	protected $userId;


	/**
	 * @param string $dsn PDO data source name
	 * @param string $user Database user
	 * @param string $pass Database password
	 * @param int|bool $uid Authenticated user
	 * @param LoggerInterface $logger Log channel
	 */
	public function __construct(
		$dsn, $user, $pass, $uid = false, $logger = null
	) {
		parent::__construct( $dsn, $user, $pass, $logger );
		$this->userId = $uid;
	}


	/**
	 * Fetch the review data for the current user and given proposal.
	 *
	 * @param int $proposal
	 * @return array Review data for all questions; false if not found
	 */
	public function reviewByUser( $proposal ) {
		$fields = array(
			'ra.points',
			'ra.comments',
		);
		$sql = self::concat(
			'SELECT',
			implode( ',', $fields ),
			'FROM review_questions rq',
			'LEFT OUTER JOIN review_answers ra ON ra.question = rq.id',
			'WHERE proposal = :proposal',
			'AND reviewer = :reviewer'
		);
		return $this->fetchAll( $sql, array(
			'proposal' => $proposal,
			'reviewer' => $this->userId
		) );
	}


	/**
	 * Create a review or update an existing one
	 *
	 * @param array $data Review data
	 */
	public function insertOrUpdateReview( array $data ) {
		$comments = $data['notes'];
		$points = $data['points'];
		$reviewer = $this->userId;
		$cols = array( 'proposal', 'question', 'reviewer', 'points', 'comments' );
		$params = self::makeBindParams( $cols );

		foreach( $points as $id => $value ) {
			$sql = self::concat(
				'INSERT INTO review_answers(',
				implode( ', ', $cols ),
				') VALUES (',
				implode( ', ', $params ),
				') ON DUPLICATE KEY UPDATE',
				'points = :points, comments = :comments'
			);
			$values = array(
				'points' => $value,
				'comments' => isset($comments[$id]) ? $comments[$id] : '',
				'proposal' => $data['proposal'],
				'question' => $id,
				'reviewer' => $reviewer
			);
			$this->insert( $sql, $values );
		}
		return true;
	}


	public function getReview( $id ) {
		return $this->fetch(
			'SELECT * FROM reviews WHERE id = ?',
			array( $id )
		);
	}

	public function getReviews( $proposal ) {
		$sql = self::concat(
			'SELECT r.*, u.username as reviewer_name',
			'FROM reviews r',
			'LEFT OUTER JOIN users u on u.id = r.reviewer',
			'WHERE proposal = ?',
			'ORDER BY r.id'
		);
		return $this->fetchAll( $sql, array( $proposal ) );
	}
}
