<?php
require_once 'Matrix.php';
/**
 * Mean variance analysis using my PHP matrix library
 * Let us now consider how to implement mean variance analysis in PHP.
 * As shown using Matlab, the calculations are relatively simple matrix
 * expressions. To implement the calculations in PHP the best way of
 * doing it is to use a linear algebra class to deal with the
 * calculations. For illustrative purposes we will show usage of the
 * Matrix class. The following function shows how to do the basic
 * mean variance calculations, calculating means and standard deviations,
 * using Matrix. This function assumes the case where there are no
 * constraints on the weight, and we use the analytical solution directly.
 * 
 * @author Thane Vo <vothane@gmail.com>
 */
class MeanVariancePortfolio {
	/**
	 * the mean variance optimal portfolio for a given required return
	 * contains the allocated weight of funds in the portfolio
	 * @access private
	 * @var Matrix
	 */
	private $minimum_variance_portfolio;
	/**
	 * the expected return of each security
	 * @access private
	 * @var Matrix
	 */
	private $expected_returns;
	/**
	 * variance of periodical returns of each security
	 * @access private
	 * @var Matrix
	 */	
	private $variance_of_returns;
	
	/**
	 * Constructor for the MeanVariancePortfolio object
	 * @param array expected periodical returns for each security
	 * array(array(...), array(...), ...)
	 * @param array variances of periodical returns for each security
	 * array(array(...), array(...), ...)
	 * @return void
	 * @access public
	 */
	public function __construct($exp_returns, $var_returns) {
		if (!is_array($exp_returns) || !is_array($var_returns)) {
			throw new Exception("Invalid Inputs");
		}
		$this->minimum_variance_portfolio = null;
		$this->expected_returns = new Matrix($exp_returns);
		$variances = $this->squareArray($var_returns);
		$this->variance_of_returns = new Matrix($variances);
	}
	
	/**
	 * returns the minimum variance portfolio
	 * @return Matrix
	 * @access public
	 */
	public function getMinimumVariancePortfolio() {
		if (empty($this->minimum_variance_portfolio) || is_null($this->minimum_variance_portfolio)) {
			throw new Exception("no data or min var need calculating");
		}
		return $this->minimum_variance_portfolio;
	}
	
	/**
	 * returns expected returns for all securities in the portfolio
	 * @return Matrix
	 * @access public
	 */
	public function getExpectedReturns() {
		return $this->expected_returns;
	}
	
	/**
	 * returns variance of returns for all securities in the portfolio
	 * @return Matrix
	 * @access public
	 */
	public function getVarianceofReturns() {
		return $this->variance_of_returns;
	}

	/**
	 * calulates a portfolio with the smallest variance whose expected
	 * return is equal to the parameter $return_level
	 * @param float desired return for the portfolio
	 * @return void
	 * @access public
	 */	
	public function calcMinimumVariancePortfolio($return_level) {
		if (!is_numeric($return_level)) {
			throw new Exception("Invalid Input");
		}
		if (!is_null($this->minimum_variance_portfolio)) {
			unset($this->minimum_variance_portfolio);
		}
		$e = clone $this->expected_returns;
		$V = clone $this->variance_of_returns;
		$n = sizeof($e->getData());
			
		$a = Matrix::makeOne(1, $n);
		$a = $a->multiply($V->inverse());
		$a = $a->multiply($e);
			
		$temp = $e->transpose();
		$b = $temp->multiply($V->inverse());
		$b = $b->multiply($e);

		$c = Matrix::makeOne(1, $n);
		$c = $c->multiply($V->inverse());
		$c = $c->multiply(Matrix::makeOne($n, 1));
			
		$temp = $a->getData();
		$aValue = $temp[0][0];
		$temp = $b->getData();
		$bValue = $temp[0][0];
		$temp = $c->getData();
		$cValue = $temp[0][0];

		$temp = array(array($bValue, $aValue), array($aValue, $cValue));
		$A = new Matrix($temp);
		unset($temp);
		$d = $A->determinant();

		$g = $b->multiply(Matrix::makeOne(1, $n));
		$subtract_by = $a->multiply($e->transpose());
		$g = $g->subtract($subtract_by);
		$g = $g->scale(1/$d);
		$g = $g->multiply($V->inverse());

		$h = $c->multiply($e->transpose());
		$subtract_by = $a->multiply(Matrix::makeOne(1, $n));
		$h = $h->subtract($subtract_by);
		$h = $h->scale(1/$d);
		$h = $h->multiply($V->inverse());

		$w = $h->scale($return_level);
		$this->minimum_variance_portfolio = $g->add($w);
	}

	/**
	 * returns expected return for the the portfolio as a whole
	 * @return float
	 * @access public
	 */	
	public function getPortfolioMean() {
		$e = clone $this->expected_returns;
		$w = clone $this->minimum_variance_portfolio;

		$e = $e->transpose();
		$w = $w->transpose();
		$portfolio_mean = $e->multiply($w);
		return $portfolio_mean;
	}

	/**
	 * The measure of risk.
	 * returns variance of the the portfolio as a whole.
	 * @return float
	 * @access public
	 */	
	public function getPortfolioVariance() {
		$V = clone $this->variance_of_returns;
		$w = clone $this->minimum_variance_portfolio;

		$portfolio_var = $w->multiply($V);
		$w = $w->transpose();
		$portfolio_var = $portfolio_var->multiply($w);
		return $portfolio_var;
	}

	/**
	 * returns standard deviation of the the portfolio as a whole
	 * square root of variance
	 * @return float
	 * @access public
	 */		
	public function getPortfolioStDev() {
		$temp = $this->getPortfolioVariance();
		return sqrt($temp->getElement(0, 0));
	}
	
	/**
	 * returns array of arrays with param array elements on 
	 * diagonal
	 * @param array 
	 * @return nxn block array
	 * @access private
	 */		
	private function squareArray($arr) {
		$n = count($arr);
		$block_array = array();

		for ($i = 0; $i < $n; $i++) {
			$row_array = array();
			for ($j = 0; $j < $n; $j++) {
				if ($i == $j) {
					$temp = $arr[$j];
					$row_array[$j] = $temp[0];
				}
				else {
					$row_array[$j] = 0;
				}
			}
			$block_array[] = $row_array;
			unset($row_array);
		}
		return $block_array;
	}
}
?>