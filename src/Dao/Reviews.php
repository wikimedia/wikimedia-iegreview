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
 * Data access object for reviews.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
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

	public function saveReview( array $data ) {
		$review = $this->reviewByUser( $data['proposal'] );
		if ( $review ) {
			return $this->updateReview( $review['id'], $data );
		} else {
			return $this->createReview( $data );
		}
	}

	/**
	 * Find the review id for the current user and given proposal.
	 *
	 * @param int $proposal
	 * @return int|bool Review id or false if not found
	 */
	public function reviewByUser( $proposal ) {
		$sql = self::concat(
			'SELECT *',
			'FROM reviews',
			'WHERE proposal = :proposal',
			'AND reviewer = :reviewer'
		);
		return $this->fetch( $sql, array(
			'proposal' => $proposal,
			'reviewer' => $this->userId
		) );
	}

	/**
	 * Save a new review.
	 *
	 * @param array $data Review data
	 * @return int|bool False if insert fails, proposal id otherwise
	 */
	public function createReview( array $data ) {
		$data['reviewer'] = $this->userId ?: null;
		$cols = array_keys( $data );
		$params = self::makeBindParams( $cols );
		$sql = self::concat(
			'INSERT INTO reviews (',
			implode( ',', $cols ),
			') VALUES (',
			implode( ',', $params ),
			')'
		);
		return $this->insert( $sql, $data );
	}

	public function updateReview( $id, $data ) {
		$fields = array_keys( $data );
		unset( $fields[array_search('proposal', $fields)] );
		$placeholders = array_map(
			function ( $elm ) {
				return "{$elm} = :{$elm}";
			},
			$fields
		);

		$sql = self::concat(
			'UPDATE reviews SET',
			implode( ', ', $placeholders ),
			', modified_at = now()',
			'WHERE id = :id',
			'AND reviewer = :reviewer',
			'AND proposal = :proposal'
		);
		$data['id'] = $id;
		$data['reviewer'] = $this->userId;

		return $this->update( $sql, $data );
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
