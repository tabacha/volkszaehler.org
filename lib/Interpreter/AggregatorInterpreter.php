<?php
/**
 * @copyright Copyright (c) 2011, The volkszaehler.org project
 * @package default
 * @license http://www.opensource.org/licenses/gpl-license.php GNU Public License
 * @todo finish
 */
/*
 * This file is part of volkzaehler.org
 *
 * volkzaehler.org is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * volkzaehler.org is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with volkszaehler.org. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Volkszaehler\Interpreter;

use Doctrine\ORM;
use Volkszaehler\Model;
use Volkszaehler\Util;

/**
 * Interpreter too aggregate several other Channels or Aggregators
 *
 * The AggregatorInterpreter is used to aggregate multiple channels with the same
 * indicator
 *
 * @author Steffen Vogel <info@steffenvogel.de>
 * @package default
 */
class AggregatorInterpreter {
	/**
	 * @var array of Interpreter
	 */
	protected $childrenInterpreter = array();

	protected $aggregator;

	/**
	 * Constructor
	 *
	 * @param Model\Aggregator $group should only contain channels of the same indicator
	 * @param ORM\EntityManager $em
	 * @param integer $from timestamp in ms since 1970
	 * @param integer $to timestamp in ms since 1970
	 * @todo handle channels in nested aggregators
	 */
	public function __construct(Model\Aggregator $aggregator, ORM\EntityManager $em, $from, $to, $tupleCount, $groupBy) {
		$this->aggregator = $aggregator;
		
		foreach ($aggregator->getChildren() as $child) {
			if ($child instanceof Model\Channel) {
				$class = $child->getDefinition()->getInterpreter();
				$this->childrenInterpreter[] = new $class($child, $em, $from, $to, $tupleCount, $groupBy);
			}
		}
	}

	/**
	 * Just a passthrough to the channel interpreters
	 *
	 * @param string|integer $groupBy
	 * @todo to be implemented
	 * @return array of values
	 */
	public function processData($tuples = NULL, $groupBy = NULL) {

	}

	/**
	 * Get total consumption of all channels
	 *
	 * @todo to be implemented
	 */
	public function getConsumption() {

	}

	/**
	 * Just a passthrough to the channel interpreters
	 *
	 * @return array with the smallest value
	 */
	public function getMin() {
		$min = current($this->childrenInterpreter)->getMin();
		foreach ($this->childrenInterpreter as $interpreter) {
			$arr = $interpreter->getMax();
			if ($arr['value '] < $min['value']) {
				$min = $arr;
			}
		}
		return $min;
	}

	/**
	 * Just a passthrough to the channel interpreters
	 *
	 * @return array with the biggest value
	 */
	public function getMax() {
		$max = current($this->childrenInterpreter)->getMax();
		foreach ($this->childrenInterpreter as $interpreter) {
			$arr = $interpreter->getMax();
			if ($arr['value '] > $max['value']) {
				$max = $arr;
			}
		}
		return $max;
	}

	/**
	 * Just a passthrough to the channel interpreters
	 *
	 * @return float average value
	 */
	public function getAverage() {
		$sum = 0;

		foreach ($this->childrenInterpreter as $interpreter) {
			$sum += $interpreter->getAverage();
		}
		return ($sum / count($this->childrenInterpreter));
	}

	/*
	 * Getter & setter
	 */

	public function getEntity() { return $this->aggregator; }
	public function getChildrenInterpreter() { return $this->childrenInterpreter; }
}
