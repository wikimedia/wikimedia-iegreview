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
 * Data access object for proposals.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Proposals extends AbstractDao {

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
	 * Save a new proposal.
	 *
	 * @param array $data Proposal data
	 * @return int|bool False if insert fails, proposal id otherwise
	 */
	public function createProposal( array $data ) {
		$data['created_by'] = $this->userId ?: null;
		$data['status'] = 'open';
		$cols = array_keys( $data );
		$params = self::makeBindParams( $cols );
		$sql = self::concat(
			'INSERT INTO proposals (',
			implode( ',', $cols ),
			') VALUES (',
			implode( ',', $params ),
			')'
		);
		return $this->insert( $sql, $data );
	}

	/*
	 * @param int $id ID of campaign to fetch
	 */
	public function getProposal( $id ) {
		return $this->fetch(
			'SELECT * FROM proposals WHERE id = ?',
			array( $id )
		);
	}


	/*
	 * @return Array $campaigns campaign data for all campaigns
	 */
	public function getCampaigns() {
		$campaigns = array();
		$sql = 'SELECT id, name FROM campaigns';
		foreach ( $this->fetchAll( $sql ) as $row ) {
			$campaigns[$row['id']] = $row['name'];
		}
		return $campaigns;
	}


	/*
	 * @param int $id ID of proposal to update
	 * @param array $data Proposal data
	 */
	public function updateProposal( $id, $data ) {
		$fields = array(
			'title', 'description', 'url', 'amount', 'theme', 'notes',
			'campaign', 'status', 'modified_by',
		);
		$placeholders = array();
		foreach ( $fields as $field ) {
			$placeholders[] = "{$field} = :{$field}";
		}
		$sql = self::concat(
			'UPDATE proposals SET',
			implode( ', ', $placeholders ),
			', modified_at = now()',
			'WHERE id = :id'
		);
		$data['id'] = $id;
		$data['modified_by'] = $this->userId;

		return $this->update( $sql, $data );
	}

	/**
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function search( array $params ) {
		$defaults = array(
			'type' => null,
			'title' => null,
			'theme' => null,
			'campaign' => null,
			'status' => 'open',
			'sort' => 'title',
			'order' => 'asc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$where = array();
		$crit = array();
		$crit['int_userid'] = $this->userId ?: 0;

		$validSorts = array(
			'id', 'title', 'amount', 'theme', 'status',
			'reviews', 'myreviews',
		);
		$sortby = in_array( $params['sort'], $validSorts ) ?
			$params['sort'] : $defaults['sort'];
		$order = $params['order'] === 'desc' ? 'DESC' : 'ASC';

		if ( $params['items'] == 'all' ) {
			$limit = '';
			$offset = '';
		} else {
			$crit['int_limit'] = (int)$params['items'];
			$crit['int_offset'] = (int)$params['page'] * (int)$params['items'];
			$limit = 'LIMIT :int_limit';
			$offset = 'OFFSET :int_offset';
		}

		if ( $params['title'] !== null ) {
			$where[] = 'p.title like :title';
			$crit['title'] = $params['title'];
		}
		if ( $params['theme'] !== null ) {
			$where[] = 'p.theme = :theme';
			$crit['theme'] = $params['theme'];
		}
		if ( $params['campaign'] !== null ) {
			$where[] = 'p.campaign = :campaign';
			$crit['campaign'] = $params['campaign'];
		}
		if ( !empty( $params['status'] ) ) {
			$where[] = 'p.status = :status';
			$crit['status'] = $params['status'];
		}

		$fields = array(
			'p.id as id',
			'p.title as title',
			'p.url as url',
			'p.amount as amount',
			'p.theme as theme',
			'p.status as status',
			'COALESCE(rc.reviews, 0) as reviews',
			'COALESCE(mc.myreviews, 0) as myreviews',
		);

		switch ( $params['type'] ) {
			case 'unreviewed':
				$where[] = 'reviews IS NULL';
				break;
			case 'myqueue':
				$where[] = 'myreviews IS NULL';
				break;
			default:
				break;
		}

		$reviewCountSql = self::concat(
			'SELECT proposal, count(DISTINCT proposal, reviewer) as reviews',
			'FROM review_answers',
			'GROUP BY proposal'
		);

		$myReviewCountSql = self::concat(
			'SELECT proposal, count(DISTINCT proposal, reviewer) as myreviews',
			'FROM review_answers',
			'WHERE reviewer = :int_userid',
			'GROUP BY proposal'
		);

		$joins = array(
			"LEFT OUTER JOIN ({$reviewCountSql}) rc on p.id = rc.proposal",
			"LEFT OUTER JOIN ({$myReviewCountSql}) mc on p.id = mc.proposal",
		);

		$sql = self::concat(
			'SELECT SQL_CALC_FOUND_ROWS', implode( ',', $fields ),
			'FROM proposals p',
			$joins,
			self::buildWhere( $where ),
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}
}
