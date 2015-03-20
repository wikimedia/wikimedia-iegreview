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
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function aggregatedScores( $campaign, array $params ) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'sort' => 'id',
			'order' => 'desc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$validSorts = array(
			'id', 'theme', 'p.amount', 'recommend', 'conditional'
		);
		$sortby = in_array( $params['sort'], $validSorts ) ?
			$params['sort'] : $defaults['sort'];
		$order = $params['order'] === 'desc' ? 'DESC' : 'ASC';

		$crit = array();
		$crit['campaign'] = $campaign;

		$fields = array(
			'rq.id AS question',
			'rq.campaign',
			'rq.type',
			'ra.proposal',
			'ra.avg',
			'ra.recommend',
			'ra.conditional',
			'ra.cnt',
			'p.title',
			'p.amount',
			'p.theme'
		);
		$sql = self::concat(
			'SELECT', implode( ',', $fields ),
			'FROM review_questions rq',
			'LEFT JOIN (',
				'SELECT AVG(points) as avg,',
				'COUNT(*) AS cnt,',
				'SUM( CASE WHEN points = 2 THEN 1 ELSE 0 END ) AS recommend,',
				'SUM( CASE WHEN points = 1 THEN 1 ELSE 0 END ) AS conditional,',
				'question,',
				'proposal',
				'FROM review_answers',
				'GROUP BY proposal, question',
			') ra ON ra.question = rq.id',
			'LEFT JOIN (',
				'SELECT id AS pid,',
				'title,',
				'theme,',
				'amount',
				'FROM proposals',
			') p ON p.pid = ra.proposal',
			'WHERE rq.campaign = :campaign',
			"ORDER BY {$sortby} {$order}, id {$order}"
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}

	/**
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function export( array $params ) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'theme' => null,
		);
		$params = array_merge( $defaults, $params );

		$where = array();
		$crit = array();
		if ( $params['theme'] !== null ) {
			$where[] = 'p.theme = :theme';
			$crit['theme'] = $params['theme'];
		}

		$sql = self::concat(
			'SELECT p.id, p.title, p.url,p.theme,',
			'r.impact,',
			'r.innovation,',
			'r.ability,',
			'r.engagement,',
			'r.recommend,',
			'IF(r.conditional >0, \'*\', \'\') AS conditional,',
			'r.cnt AS rcnt,',
			'ROUND((r.recommend / r.cnt) * 100, 2) AS pcnt',
			'FROM proposals p',
			'INNER JOIN (',
				'SELECT COUNT(*) AS cnt,',
				'AVG(impact) AS impact,',
				'AVG(innovation) AS innovation,',
				'AVG(ability) AS ability,',
				'AVG(engagement) AS engagement,',
				'SUM(IF(recommendation > 0, 1, 0)) AS recommend,',
				'SUM(IF(recommendation = 1, 1, 0)) AS conditional,',
				'proposal',
				'FROM reviews',
				'GROUP BY proposal',
			') r ON p.id = r.proposal',
			self::buildWhere( $where ),
			"ORDER BY pcnt DESC, id DESC"
		);
		$results = $this->fetchAllWithFound( $sql, $crit );

		$commentsSql = self::concat(
			'SELECT proposal,',
			'impact_note,',
			'innovation_note,',
			'ability_note,',
			'engagement_note,',
			'comments',
			'FROM reviews'
		);

		$comments = array();
		foreach ( $this->fetchAll( $commentsSql ) as $row ) {
			if ( !isset( $comments[ $row['proposal'] ] ) ) {
				$comments[ $row['proposal'] ] = array();
			}
			if ( $row['impact_note'] ) {
				$comments[$row['proposal']][] = $row['impact_note'];
			}
			if ( $row['innovation_note'] ) {
				$comments[$row['proposal']][] = $row['innovation_note'];
			}
			if ( $row['ability_note'] ) {
				$comments[$row['proposal']][] = $row['ability_note'];
			}
			if ( $row['engagement_note'] ) {
				$comments[$row['proposal']][] = $row['engagement_note'];
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
