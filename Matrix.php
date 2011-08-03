<?php
/**
 * Informally, the terms matrix and array are often used interchangeably.
 * More precisely, a matrix is a two-dimensional rectangular array of real or
 * complex numbers that represents a linear transformation. The linear algebraic
 * operations defined on matrices have found applications in a wide variety of
 * technical fields. MAtrix algrebra is widely used for mathematical and statistal
 * analysis.
 * 
 * @author Thanh Vo <vothany@hotmail.com>
 */
class Matrix {
	/**
	 * Contains the array of arrays defining the matrix
	 * @access private
	 * @var array i.e. array(array(...), array(...), ...)
	 */
	private $_data;

	/**
	 * The number of rows in the matrix
	 * @access private
	 * @var int
	 */
	private $_num_rows;

	/**
	 * The number of columns in the matrix
	 * @access private
	 * @var int
	 */
	private $_num_cols;

	/**
	 * A flag indicating if the matrix is square
	 * i.e. if $this->_num_cols == $this->_num_rows
	 * @access private
	 * @var boolean
	 */
	private $_square;

	/**
	 * Cutoff error used to test for singular or ill-conditioned matrices
	 * @access private
	 * @var f;oat
	 */
	private $_epsilon;

	/**
	 * Constructor for the matrix object
	 * @param array elements for the Matrix
	 * @return void
	 * @access public
	 */
	public function __construct($data=null) {
		if (!is_null($data)) {
			$this->setData($data);
		}
		else {
			$this->_data = null;
			$this->_num_rows = null;
			$this->_num_cols = null;
			$this->_square = null;
		}
		$this->_epsilon = 1E-18;
	}

	/**
	 * Returns a new Matrix object with the same data as the current one
	 * @return Matrix
	 * @access public
	 */
	public function __clone() {
		if ($this->isEmpty()) {
			throw new Exception('__clone() Matrix, isEmpty() Matrix');
		} else {
			return new Matrix($this->_data);
		}
	}

	/**
	 * Validates the data and initializes the internal variables (except for the determinant).
	 * The validation is performed by by checking that
	 * each row (first dimension in the array of arrays)
	 * contains the same number of colums (e.g. arrays of the
	 * same size)
	 * @param array elements of Matrix
	 * @return boolean TRUE Matrix has been set in Matrix
	 * @access public
	 */
	public function setData($data) {
		if (Matrix::isMatrix($data)) {
			if (!$data->isEmpty()) {
				$this->_data = $data->getData();
			} else {
				throw new Exception('setData() Matrix, isEmpty() Matrix');
			}
		} elseif (is_array($data) || is_array($data[0])) {
			// check that we got a numeric bidimensional array
			// and that all rows are of the same size
			$nc = count($data[0]);
			$nr = count($data);
			$eucnorm = 0;

			for ($i=0; $i < $nr; $i++) {
				if (count($data[$i]) != $nc) {
					throw new Exception('setData() Matrix, count($data[$i]) != $nc');
				}
				for ($j=0; $j < $nc; $j++) {
					if (!is_numeric($data[$i][$j])) {
						throw new Exception('setData() Matrix, !is_numeric($data[$i][$j]');
					}
					$data[$i][$j] = (float) $data[$i][$j];
					$tmp[] = $data[$i][$j];
					$eucnorm += $data[$i][$j] * $data[$i][$j];
				}
			}
			$this->_num_rows = $nr;
			$this->_num_cols = $nc;
			$this->_square = ($nr == $nc);
			$this->_norm = sqrt($eucnorm);
			$this->_data = $data;
			return true;
		} else {
			throw new Exception('setData() Matrix, Undefned Error');
		}
	}

	/**
	 * Returns the array of arrays.
	 * @param array elements of Matrix
	 * @return array elements of Matrix
	 * @access public
	 */
	public function getData() {
		if ($this->isEmpty()) {
			throw new Exception('getData() Matrix, $this->isEmpty()');
		} else {
			return $this->_data;
		}
	}

	/**
	 * Returns the Euclidean norm of the matrix.
	 * Euclidean norm = sqrt( sum( e[i][j]^2 ) )
	 * @return float norm of Matrix
	 * @access public
	 */
	public function norm() {
		if (!is_null($this->_norm)) {
			return $this->_norm;
		} else {
			throw new Exception('norm() Matrix, is_null($this->_norm');
		}
	}

	/**
	 * Sets the threshold to consider a numeric value as zero:
	 * if number <= epsilon then number = 0
	 * @param float new tolerance for zero
	 * @return boolean TRUE if new $epsilon is set
	 * @access public
	 */
	public function setZeroThreshold($epsilon) {
		if (!is_numeric($epsilon)) {
			throw new Exception('setZeroThreshold($epsilon) Matrix, !is_numeric($epsilon)');
		} else {
			$this->_epsilon = $epsilon;
			return true;
		}
	}

	/**
	 * Returns the value of the upper bound used to minimize round off errors
	 * @return float current tolerance for zero
	 * @access public
	 */
	public function getZeroThreshold() {
		return $this->_epsilon;
	}

	/**
	 * Checks if the matrix has been initialized.
	 * @return boolean false if MAtrix has no elements
	 * @access public
	 */
	public function isEmpty() {
		return ( empty($this->_data) || is_null($this->_data) );
	}

	/**
	 * Returns an array with the number of rows and columns in the matrix
	 * @return array number of rows and columns
	 * @access public
	 */
	public function getSize() {
		if ($this->isEmpty()) {
			throw new Exception('getSize() Matrix, $this->isEmpty()');
		}
		else {
			return array($this->_num_rows, $this->_num_cols);
		}
	}

	/**
	 * Checks if it is a square matrix (i.e. num rows == num cols)
	 * @return boolean true if number of rows equals number of columns
	 * @access public
	 */
	public function isSquare() {
		if ($this->isEmpty()) {
			throw new Exception('isSquare() Matrix, $this->isEmpty()');
		} else {
			return $this->_square;
		}
	}

	/**
	 * Sets the value of the element at (row,col)
	 * @param int row index
	 * @param int column index
	 * @return boolen true if element has been set
	 * @access public
	 */
	public function setElement($row, $col, $value) {
		if ($this->isEmpty()) {
			throw new Exception('setElement() Matrix, $this->isEmpty()');
		}
		if ($row >= $this->_num_rows && $col >= $this->_num_cols) {
			throw new Exception('setElement() Matrix, $row >= $this->_num_rows && $col >= $this->_num_cols');
		}
		if (!is_numeric($value)) {
			throw new Exception('setElement($row, $col, $value) Matrix, !is_numeric($value)');
		}
		$this->_data[$row][$col] = $value;
		return true;
	}

	/**
	 * Returns the value of the element at (row,col)
	 * @param int row index
	 * @param int column index
	 * @return float element at row index and column index
	 * @access public
	 */
	public function getElement($row, $col) {
		if ($this->isEmpty()) {
			throw new Exception('getElement($row, $col) Matrix, $this->isEmpty()');
		}
		if ($row >= $this->_num_rows && $col >= $this->_num_cols) {
			throw new Exception('getElement($row, $col) Matrix, $row >= $this->_num_rows && $col >= $this->_num_cols');
		}
		return $this->_data[$row][$col];
	}

	/**
	 * Returns the row with the given index
	 * This method checks that matrix has been initialized and that the
	 * row requested is not outside the range of rows.
	 * @param int row index
	 * @return array row at index
	 * @access public
	 */
	public function getRow($row) {
		if ($this->isEmpty()) {
			throw new Exception('getRow($row) Matrix, $this->isEmpty()');
		}
		if (is_integer($row) && $row >= $this->_num_rows) {
			throw new Exception('getRow($row) Matrix, is_integer($row) && $row >= $this->_num_rows');
		}
		return $this->_data[$row];
	}

	/**
	 * Sets the row with the given index to the array
	 * This method checks that the row is less than the size of the matrix
	 * rows, and that the array size equals the number of columns in the matrix.
	 * @param int row index
	 * @return array row at index
	 * @access public
	 */
	public function setRow($row, $arr) {
		if ($this->isEmpty()) {
			throw new Exception('setRow($row, $arr) Matrix, $this->isEmpty()');
		}
		if ($row >= $this->_num_rows) {
			throw new Exception('setRow($row, $arr) Matrix, ($row >= $this->_num_rows');
		}
		if (count($arr) != $this->_num_cols) {
			throw new Exception('Incorrect size for matrix row: expecting '.$this->_num_cols
			.' columns, got '.count($arr).' columns');
		}
		for ($i=0; $i < $this->_num_cols; $i++) {
			if (!is_numeric($arr[$i])) {
				throw new Exception('setRow($row, $arr) Matrix, !is_numeric($arr[$i]');
			}
		}
		$this->_data[$row] = $arr;
		return true;
	}

	/**
	 * Returns the column with the given index
	 * This method checks that matrix has been initialized and that the
	 * column requested is not outside the range of column.
	 * @param int column index
	 * @return array column at index
	 * @access public
	 */
	public function getCol($col) {
		if ($this->isEmpty()) {
			throw new Exception('getCol($col) Matrix, $this->isEmpty()');
		}
		if (is_integer($col) && $col >= $this->_num_cols) {
			throw new Exception('Incorrect column value');
		}
		for ($i=0; $i < $this->_num_rows; $i++) {
			$ret[$i] = $this->getElement($i,$col);
		}
		return $ret;
	}

	/**
	 * Sets the column with the given index to the array
	 * This method checks that the column is less than the size of the matrix
	 * columns, and that the array size equals the number of rows in the matrix.
	 * @param int column index
	 * @param array elements for indexed column 
	 * @return boolean true if indexed column has been set with elements of array 
	 * @access public
	 */
	public function setCol($col, $arr) {
		if ($this->isEmpty()) {
			throw new Exception('setCol($col, $arr) Matrix, $this->isEmpty()');
		}
		if ($col >= $this->_num_cols) {
			throw new Exception('setCol($col, $arr) Matrix, $col >= $this->_num_cols');
		}
		if (count($arr) != $this->_num_cols) {
			throw new Exception('setCol($col, $arr) Matrix, count($arr) != $this->_num_cols');
		}
		for ($i=0; $i < $this->_num_rows; $i++) {
			if (!is_numeric($arr[$i])) {
				throw new Exception('setCol($col, $arr) Matrix, !is_numeric($arr[$i]');
			} else {
				$isColSet = $this->setElement($i, $col, $arr[$i]);
			}
		}
		return $isColSet;
	}

	/**
	 * Swaps the rows with the given indices
	 * @param int index of first row
	 * @param int index of second row 
	 * @return boolean true both rows are interchanged
	 * @access public
	 */
	public function swapRows($i, $j) {
		$r1 = $this->getRow($i);
		$r2 = $this->getRow($j);
		$rowSwapped = $this->setRow($j, $r1);
		$rowSwapped = $this->setRow($i, $r2);
		return $rowSwapped;
	}

	/**
	 * Multiplication of a Matrix by a scalar. every element is multiply by the scalar.
	 * @param int scalar to multiplr each element in the array
	 * @return Matrix scaled Matrix
	 * @access public
	 */
	public function scale($scale) {
		if (!is_numeric($scale)) {
			throw new Exception('scale($scale) Matrix, !is_numeric($scale)');
		}
		list($nr, $nc) = $this->getSize();
		$data = array();
		for ($i=0; $i < $nr; $i++) {
			for ($j=0; $j < $nc; $j++) {
				$data[$i][$j] = $scale * $this->getElement($i,$j);
			}
		}
		if (!empty($data)) {
			return new Matrix($data);
		} else {
			throw new Exception('scale($scale) Matrix, empty($data) true');
		}
	}

	/**
	 * Multiplies (scales) a row by the given factor
	 * @param int scalar factor
	 * @param int row index
	 * @return boolean true row has been scaled by scalar factor
	 * @access public
	 */
	public function scaleRow($row, $factor) {
		if ($this->isEmpty()) {
			throw new Exception('Uninitialized Matrix object');
		}
		if (!is_integer($row) || !is_numeric($factor)) {
			throw new Exception('Row index must be an integer, and factor a valid number');
		}
		if ($row >= $this->_num_rows) {
			throw new Exception('Row index out of bounds');
		}
		$r = $this->getRow($row);
		$nr = count($r);
		for ($i=0; $i<$nr; $i++) {
			$r[$i] *= $factor;
		}
		return $this->setRow($row, $r);
	}
	
	/**
	 * Returns the index of the row with the maximum value under column of the element e[i][i]
	 * @param int index of the row
	 * @return int maximum value under column of the element e[i][i]
	 * @access private
	 */
	private function maxElementIndex($r) {
		$max = 0;
		$idx = -1;
		list($nr, $nc) = $this->getSize();
		for ($i=$r; $i<$nr; $i++) {
			$val = abs($this->_data[$i][$r]);
			if ($val > $max) {
				$max = $val;
				$idx = $i;
			}
		}
		if ($idx == -1) {
			$idx = $r;
		}
		return $idx;
	}

	/**
	 * Transposition turns a row vector into a column vector.
	 * @return Matrix tranpose of this Matrix
	 * @access public
	 */
	public function transpose() {
		list($nr, $nc) = $this->getSize();
		$data = array();
		for ($i=0; $i < $nc; $i++) {
			$col = $this->getCol($i);
			$data[] = $col;
		}
		return new Matrix($data);
	}

	/**
	 * Calculates the matrix determinant using Gaussian elimination with partial pivoting.
	 * At each step of the pivoting proccess, it checks that the normalized
	 * determinant calculated so far is less than 10^-18, trying to detect
	 * singular or ill-conditioned matrices
	 * @param int scalar factor
	 * @param int row index
	 * @return boolean true row has been scaled by scalar factor
	 * @access public
	 */
	public function determinant() {

		if ($this->isEmpty()) {
			throw new EmptyMatrixException();
		}
		if (!$this->isSquare()) {
			throw new Exception('Determinant undefined for non-square matrices');
		}
		$norm = $this->norm();
		$det = 1.0;
		$sign = 1;
		// work on a copy
		$m = clone $this;
		list($nr, $nc) = $m->getSize();
		for ($r=0; $r<$nr; $r++) {
			// find the maximum element in the column under the current diagonal element
			$ridx = $m->maxElementIndex($r);

			if ($ridx != $r) {
				$sign = -$sign;
				$e = $m->swapRows($r, $ridx);
			}
			// pivoting element
			$pelement = $m->getElement($r, $r);
			$det *= $pelement;
			// Is this an singular or ill-conditioned matrix?
			// i.e. is the normalized determinant << 1 and -> 0?
			if ((abs($det)/$norm) < $this->_epsilon) {
				throw new Exception('Probable singular or ill-conditioned matrix, normalized determinant = '
				.(abs($det)/$norm));
			}
			if ($pelement == 0) {
				throw new Exception('Cannot continue, pivoting element is zero');
			}
			// zero all elements in column below the pivoting element
			for ($i = $r + 1; $i < $nr; $i++) {
				$factor = $m->getElement($i, $r) / $pelement;
				for ($j=$r; $j < $nc; $j++) {
					$val = $m->getElement($i, $j) - $factor*$m->getElement($r, $j);
					$e = $m->setElement($i, $j, $val);
				}
			}
		}
		unset($m);
		if ($sign < 0) {
			$det = -$det;
		}
		return $det;
	}

	/**
	 * If A is square and nonsingular, the equations AX = I and XA = I have the same
	 * solution, X. This solution is called the inverse of A, is denoted by A-1, and
	 * is computed by using Gauss-Jordan elimination with partial pivoting. Inverse is only
	 * defined for square matrices.
	 * @return Matrix inverse of current Matrix
	 * @access public
	 */	
	public function inverse() {
		if ($this->isEmpty()) {
			throw new EmptyMatrixException();
		}
		if (!$this->isSquare()) {
			throw new Exception('Determinant undefined for non-square matrices');
		}
		$norm = $this->norm();
		$sign = 1;
		$det = 1.0;
		// work on a copy to be safe
		$m = clone $this;
		list($nr, $nc) = $m->getSize();
		// Unit matrix to use as target
		$q = Matrix::makeUnit($nr);
			
		for ($i=0; $i<$nr; $i++) {
			$ridx = $this->maxElementIndex($i);
			if ($i != $ridx) {
				$sign = -$sign;
				$e = $m->swapRows($i, $ridx);
				$e = $q->swapRows($i, $ridx);
			}
			$pelement = $m->getElement($i, $i);
			if ($pelement == 0) {
				throw new Exception('Cannot continue inversion, pivoting element is zero');
			}
			$det *= $pelement;
			if ((abs($det)/$norm) < $this->_epsilon) {
				throw new Exception('Probable singular or ill-conditioned matrix, normalized determinant = '
				.(abs($det)/$norm));
			}
			$e = $m->scaleRow($i, 1/$pelement);
			$e = $q->scaleRow($i, 1/$pelement);
			// zero all column elements execpt for the current one
			$tpelement = $m->getElement($i, $i);
			for ($j=0; $j<$nr; $j++) {
				if ($j == $i) {
					continue;
				}
				$factor = $m->getElement($j, $i) / $tpelement;
				for ($k=0; $k<$nc; $k++) {
					$vm = $m->getElement($j, $k) - $factor * $m->getElement($i, $k);
					$vq = $q->getElement($j, $k) - $factor * $q->getElement($i, $k);
					$m->setElement($j, $k, $vm);
					$q->setElement($j, $k, $vq);
				}
			}
		}
		$data = $q->getData();
		unset($m);
		unset($q);

		if ($sign < 0) {
			$det = -$det;
		}
		return new Matrix($data);
	}

	//  Returns a simple string representation of the matrix
	public function toString($format='%6.2f') {
		if ($this->isEmpty()) {
			throw new Exception('toString() Matrix, Matrix is empty');
		}
		$out = "";
		for ($i=0; $i < $this->_num_rows; $i++) {
			for ($j=0; $j < $this->_num_cols; $j++) {
				// remove the -0.0 output
				$entry =  $this->_data[$i][$j];
				if (sprintf('%2.1f',$entry) == '-0.0') {
					$entry = 0;
				}
				$out .= sprintf($format, $entry);
			}
			$out .= "\n";
		}
		return $out;
	}

	/**
	 * Addition and subtraction of matrices is defined just as it is for arrays,
	 * element-by-element. Adding A to B and then subtracting A from the result recovers B.
	 * Addition and subtraction require both matrices to have the same dimension, or one
	 * of them be a scalar. If the dimensions are incompatible, an error results.
	 * @param int scalar factor
	 * @param int row index
	 * @return boolean true row has been scaled by scalar factor
	 * @access public
	 */	
	public function add($m1) {
		if (!Matrix::isMatrix($m1)) {
			return new Exception("Parameter must be a Matrix object");
		}
		if ($this->getSize() != $m1->getSize()) {
			return new Exception("Matrices must have the same dimensions");
		}
		list($nr, $nc) = $m1->getSize();
		$data = array();
		for ($i=0; $i < $nr; $i++) {
			for ($j=0; $j < $nc; $j++) {
				$el1 = $m1->getElement($i,$j);
				$el = $this->getElement($i,$j);
				$data[$i][$j] = $el + $el1;
			}
		}
		if (!empty($data)) {
			return new Matrix($data);
		} else {
			throw new Exception('add() Matrix, empty Matrix');
		}
	}

	/**
	 * Substracts a matrix from this one
	 * @param Matrix other Matrix
	 * @return Matrix
	 * @access public
	 */	
	public function subtract($m1) {
		if (!Matrix::isMatrix($m1)) {
			throw new Exception("Parameter must be a Matrix object");
		}
		if ($this->getSize() != $m1->getSize()) {
			throw new Exception("Matrices must have the same dimensions");
		}
		list($nr, $nc) = $m1->getSize();
		$data = array();
		for ($i=0; $i < $nr; $i++) {
			for ($j=0; $j < $nc; $j++) {
				$el1 = $m1->getElement($i,$j);
				$el = $this->getElement($i,$j);
				$data[$i][$j] = $el - $el1;
			}
		}
		if (!empty($data)) {
			return new Matrix($data);
		} else {
			throw new Exception('subtract($m1) Matrix, empty Matrix');
		}
	}

	/**
	 * Multiplication of matrices is defined in a way that reflects composition of the
	 * underlying linear transformations and allows compact representation of systems of
	 * simultaneous linear equations. The matrix product C = AB is defined when the column
	 * dimension of A is equal to the row dimension of B, or when one of them is a scalar.
	 * If A is m-by-p and B is p-by-n, their product C is m-by-n.
	 * @param int scalar factor
	 * @param int row index
	 * @return boolean true row has been scaled by scalar factor
	 * @access public
	 */		
	public function multiply($B) {
		if (!Matrix::isMatrix($B)) {
			throw new Exception('Wrong parameter, expected a Matrix object');
		}
		list($nrA, $ncA) = $this->getSize();
		list($nrB, $ncB) = $B->getSize();
		if ($ncA != $nrB) {
			throw new Exception('Incompatible sizes columns in matrix must be the same as rows in parameter matrix');
		}
		$data = array();
		for ($i=0; $i < $nrA; $i++) {
			$data[$i] = array();
			for ($j=0; $j < $ncB; $j++) {
				$rctot = 0;
				for ($k=0; $k < $ncA; $k++) {
					$rctot += $this->getElement($i,$k) * $B->getElement($k, $j);
				}
				// take care of some round-off errors
				if (abs($rctot) <= $this->_epsilon) {
					$rctot = 0.0;
				}
				$data[$i][$j] = $rctot;
			}
		}
		if (!empty($data)) {
			return new Matrix($data);
		} else {
			throw new Exception('Undefined error');
		}
	}

	/**
	 * Checks if the object is a Matrix instance
	 * @param Matrix
	 * @return boolean true if object parameter is an instance of a Matrix 
	 * @access public
	 */		
	public function isMatrix($matrix) {
		return is_object($matrix) && ($matrix instanceof Matrix);
	}

	/**
	 * Returns a Matrix object of size (nrows, ncols) filled with a value
	 * @param int number of rows
	 * @param int number of columns
	 * @param int value of each element in Matrix
	 * @return Matrix 
	 * @access public
	 */	
	public static function makeMatrix($nrows, $ncols, $value) {
		if (!is_int($nrows) && is_int($ncols) && !is_numeric($value)) {
			throw new Exception('Number of rows, columns, and a numeric fill value expected');
		}
		for ($i=0; $i<$nrows; $i++) {
			$m[$i] = explode(":",substr(str_repeat($value.":",$ncols),0,-1));
		}
		return new Matrix($m);
	}

	/**
	 * Returns the Matrix object of size (nrows, ncols), filled with the value 1 (one)
	 * @param int number of rows
	 * @param int number of columns
	 * @return Matrix each element = 1
	 * @access public
	 */	
	public static function makeOne($nrows, $ncols) {
		return Matrix::makeMatrix($nrows, $ncols, 1);
	}

	/**
	 * Returns the Matrix object of size (nrows, ncols), filled with the value 0 (zero)
	 * @param int number of rows
	 * @param int number of columns
	 * @return Matrix each element = 0
	 * @access public
	 */	
	public static function makeZero($nrows, $ncols) {
		return Matrix::makeMatrix ($nrows, $ncols, 0);
	}

	/**
	 * Returns a square unit Matrix object of the given size
	 * A unit matrix is one in which the elements follow the rules:
	 * e[i][j] = 1, if i == j
	 * e[i][j] = 0, if i != j
	 * such a matrix is also called an 'identity matrix'
	 * @param int number of rows and number of columns
	 * @return Matrix 1's on main diagonal and 0's elsewhere
	 * @access public
	 */	
	public static function makeUnit($size) {
		if (!is_integer($size)) {
			throw new Exception('An integer expected for the size of the Identity matrix');
		}
		for ($i=0; $i<$size; $i++) {
			for ($j=0; $j<$size; $j++) {
				if ($i == $j) {
					$data[$i][$j] = (float) 1.0;
				} else {
					$data[$i][$j] = (float) 0.0;
				}
			}
		}
		return new Matrix($data);
	}

	/**
	 * Generally accepted mathematical notation uses the capital letter I to denote
	 * zeros elsewhere. These matrices have the property that  and  whenever the
	 * dimensions are compatible.
	 * @param int number of rows and number of columns
	 * @return Matrix 1's on main diagonal and 0's elsewhere
	 * @access public
	 */		
	public static function makeIdentity($size) {
		return Matrix::makeUnit($size);
	}
} // end of Matrix class
?>
