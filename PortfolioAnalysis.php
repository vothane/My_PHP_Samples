<?php

/**
 * From a set N securities is possible to combine the infinite number of
 * portfolios. Fortunately, the investor should only consider a subset of the
 * set (attainable portfolios) of all possible portfolios, belonging to the so-called efficient set.
 * The investor will choose the optimal portfolio from the portfolio set, where
 * every portfolio provides the minimal risk for some value of the expected yield. The
 * set of portfolios, satisfying these two conditions, is the efficient set.
 *
 * @author Thane Vo <vothane@gmail.com>
 */

require_once 'StocksDataBase.php';
require_once 'Statistics.php';
require_once 'MeanVariancePortfolio.php';

class PortfolioAnalysis implements Iterator {
	private $portfolio;
	private $attainable_portfolios;
	private $pointer = 0;
	private $db;
	
	/**
	 * Constructor for the PortfolioAnalysis object
	 * @return void
	 * @access public
	 */
	public function __construct() {
		$this->portfolio = null;
		$this->attainable_portfolios = array();
		$this->db = StocksDatabase::getInstance();
	}
	
	/**
	 * Set the Portfolio to hold individual assets defined by symbol(s) 
	 * get applicable historical data from database to do calculations 
	 * @param array strings defining certain individuals stocks in the database
	 * @return boolean
	 * @access public
	 */
	public function setPortfolio($symbols) {
		$returns_of_stocks = array();
		$expected_returns = array();
		$variances_of_returns = array();

		foreach ($symbols as $symbol) {
			$returns_of_stocks[] = $this->db->findReturnsBySymbol($symbol);
		}
		foreach ($returns_of_stocks as $returns) {
			$expected_returns[] = array(Statistics::calcMean($returns));
		}
		foreach ($returns_of_stocks as $index => $returns) {
			$mean = $expected_returns[$index];
			$variance = Statistics::calcVariance($returns, $mean[0]);
			$variances_of_returns[] = array($variance);
		}
		unset($returns_of_stocks);
		$this->portfolio = new MeanVariancePortfolio($expected_returns, $variances_of_returns);
		return true;
	}

	/**
	 * Finds the optimal portfolio with least rick for a given return level
	 * @param float target return rate of the optimal portfolio
	 * @return array allocated weights (the percentage of funds to each individual asset) and the standard deviation 
	 * of the portfolio (normally distributed)     
	 * @access public
	 */
	public function analyzePortfolio($return_level) {
		$this->portfolio->calcMinimumVariancePortfolio($return_level);
		$allocated_weights = $this->portfolio->getMinimumVariancePortfolio();
		$allocated_weights = $allocated_weights->getData();
		$portfolio_stdev = $this->portfolio->getPortfolioStDev();
		return array($allocated_weights[0], $portfolio_stdev);
	}
	
	/**
	 * Finds the optimal portfolios with least rick for a given set of return levels
	 * @param array target return rates of optimal portfolios
	 * @access public
	 */
	public function findAttainablePortfolios($returns) {
		if (!is_array($returns)) {
			throw new Exception("Parameter must be an array.");
		}
		if (empty($returns)) {
			throw new Exception("Array is empty.");
		}
		foreach ($returns as $return) {
			list($weights, $stdev) = $this->analyzePortfolio($return);
			if ($this->isAttainable($weights)) {
				$temp = array();
				$temp["allocated_weights"] = $weights;
				$temp["portfolio_stdev"] = $stdev;
				$temp["return_level"] = $return;
				$this->attainable_portfolios[] = $temp;
			}
			unset($temp);
		}
	}

	public function getAttainablePortfolios() {
		if (empty($this->attainable_portfolios)) {
			throw new Exception("empty array.");
		}
		return $this->attainable_portfolios;
	}

	private function isAttainable($weights) {
		if (min($weights) < 0) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * --- Methods Defined by the Iterator Interface ---
	 */

	public function rewind() {
		$this->pointer = 0;
	}

	public function current() {
		return $this->attainable_portfolios( $this->pointer );
	}

	public function key() {
		return $this->pointer;
	}

	public function next() {
		$this->pointer++;
	}

	public function valid() {
		return ( ! is_null( $this->current() ) );
	}
}

?>