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
	 * @param array $params
	 * @return object StdClass with rows and found memebers
	 */
	public function aggregatedScores( array $params ) {
		$this->logger->debug( __METHOD__, $params );
		$defaults = array(
			'sort' => 'pcnt',
			'order' => 'desc',
			'items' => 20,
			'page' => 0,
		);
		$params = array_merge( $defaults, $params );

		$validSorts = array(
			'id', 'title', 'amount', 'theme',
			'impact', 'innovation', 'ability', 'engagement', 'recommend',
			'rcnt', 'pcnt',
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

		$sql = self::concat(
			'SELECT p.id, p.title, p.theme, p.amount,',
			'r.impact,',
			'r.innovation,',
			'r.ability,',
			'r.engagement,',
			'r.recommend,',
			'r.cnt AS rcnt,',
			'ROUND((r.recommend / r.cnt) * 100, 2) AS pcnt',
			'FROM proposals p',
			'INNER JOIN (',
				'SELECT COUNT(*) AS cnt,',
				'AVG(impact) AS impact,',
				'AVG(innovation) AS innovation,',
				'AVG(ability) AS ability,',
				'AVG(engagement) AS engagement,',
				'SUM(recommendation) AS recommend,',
				'proposal',
				'FROM reviews',
				'GROUP BY proposal',
			') r ON p.id = r.proposal',
			"ORDER BY {$sortby} {$order}, id {$order}",
			$limit, $offset
		);
		return $this->fetchAllWithFound( $sql, $crit );
	}
}
