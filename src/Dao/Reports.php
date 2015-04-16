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
 * Data access object for reports.
 *
 * @author Bryan Davis <bd808@wikimedia.org>
 * @copyright © 2014 Bryan Davis, Wikimedia Foundation and contributors.
 */
class Reports extends AbstractDao {

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
	 * @param int $campaign Active campaign ID
	 * @param array $questions
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function aggregatedScores(
		$campaign, array $questions, array $params
	) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'sort' => 'pcnt',
			'order' => 'desc',
			'items' => 20,
			'page' => 0,
			'theme' => null,
		);
		$params = array_merge( $defaults, $params );

		$questionIds = array_map(
			function ( $elm ) { return "q{$elm['id']}"; }, $questions
		);
		$validSorts = array_merge(
			array( 'id', 'title', 'amount', 'theme', 'rcnt', 'pcnt' ),
			$questionIds
		);

		$sortby = in_array( $params['sort'], $validSorts ) ?
			$params['sort'] : $defaults['sort'];
		$order = $params['order'] === 'desc' ? 'DESC' : 'ASC';

		$crit = array();
		$crit['campaign'] = $campaign;

		$where = array( 'p.campaign = :campaign' );
		if ( $params['theme'] !== null ) {
			$where[] = 'p.theme = :theme';
			$crit['theme'] = $params['theme'];
		}

		if ( $params['items'] == 'all' ) {
			$limit = '';
			$offset = '';
		} else {
			$crit['int_limit'] = (int)$params['items'];
			$crit['int_offset'] = (int)$params['page'] * (int)$params['items'];
			$limit = 'LIMIT :int_limit';
			$offset = 'OFFSET :int_offset';
		}

		$fields = array(
			'p.id',
			'p.title',
			'p.amount',
			'p.theme',
			'p.url',
		);

		$joins = array();

		foreach ( $questions as $question ) {
			$sub = "q{$question['id']}";
			if ( $question['type'] == 'score' ) {
				$subselect = self::concat(
					'SELECT proposal, AVG(points) AS points',
					'FROM review_answers',
					"WHERE question = :int_{$sub}",
					'GROUP BY proposal'
				);
				$fields[] = "{$sub}.points AS {$sub}";
			} else {
				$subselect = self::concat(
					'SELECT proposal,',
					'SUM(IF(points > 0, 1, 0)) AS recommend,',
					'SUM(IF(points = 1, 1, 0)) AS conditional,',
					'COUNT(DISTINCT reviewer) AS cnt',
					'FROM review_answers',
					"WHERE question = :int_{$sub}",
					'GROUP BY proposal'
				);

				$fields[] = "{$sub}.recommend as recommend";
				$fields[] = "IF({$sub}.conditional >0, '*', '') AS conditional";
				$fields[] = "{$sub}.cnt AS rcnt";
				$fields[] = "ROUND(({$sub}.recommend / {$sub}.cnt) * 100, 2) AS pcnt";
			}
			$joins[] = "LEFT OUTER JOIN ({$subselect}) {$sub} ON p.id = {$sub}.proposal";
			$crit["int_{$sub}"] = $question['id'];
		}
		$sql = self::concat(
			'SELECT', implode( ',', $fields ),
			'FROM proposals p',
			$joins,
			self::buildWhere( $where ),
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}


	/**
	 * @param int $campaign Active campaign ID
	 * @param array $questions
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function export( $campaign, array $questions, array $params ) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'sort' => 'pcnt',
			'order' => 'desc',
			'items' => 'all',
			'page' => 0,
			'theme' => null,
		);
		$params = array_merge( $defaults, $params );

		$results = $this->aggregatedScores( $campaign, $questions, $params );

		$commentsSql = self::concat(
			'SELECT ra.proposal, ra.question, ra.comments',
			'FROM review_answers ra',
			'INNER JOIN review_questions rq ON rq.id = ra.question',
			'WHERE rq.campaign = ?'
		);
		$commentsRows = $this->fetchAll( $commentsSql, array( $campaign ) );

		$comments = array();
		foreach ( $commentsRows as $row ) {
			if ( !isset( $comments[$row['proposal']] ) ) {
				$comments[$row['proposal']] = array();
			}
			if ( $row['comments'] ) {
				$comments[$row['proposal']][] = $row['comments'];
			}
		}

		foreach ( $results->rows as &$row ) {
			if ( isset( $comments[$row['id']] ) ) {
				$row['comments'] = $comments[$row['id']];
			}
		}

		return $results;
	}


	/**
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function campaigns( array $params ) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'sort' => 'pcnt',
			'order' => 'desc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$validSorts = array(
			'id', 'name', 'start_date', 'end_date', 'status',
		);
		$sortby = in_array( $params['sort'], $validSorts ) ?
			$params['sort'] : $defaults['sort'];
		$order = $params['order'] === 'desc' ? 'DESC' : 'ASC';

		$crit = array();

		if ( $params['items'] == 'all' ) {
			$limit = '';
			$offset = '';
		} else {
			$crit['int_limit'] = (int)$params['items'];
			$crit['int_offset'] = (int)$params['page'] * (int)$params['items'];
			$limit = 'LIMIT :int_limit';
			$offset = 'OFFSET :int_offset';
		}

		$sql = self::concat(
			'SELECT c.id, c.name, c.start_date, c.end_date, c.status',
			'FROM campaigns c',
			'WHERE status = 0', //Only expired campaigns shown in this view
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}

}
