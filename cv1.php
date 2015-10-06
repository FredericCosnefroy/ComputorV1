<?php


	function string_is_int($string)
	{
		return (preg_match("/[+-][0-9]{1,}$/", $string));
	}

	function is_operand($char)
	{
		return ($char == '+' || $char == '-');
	}

	function split_equation($raw_equation)
	{
		$occurences = substr_count($raw_equation, '=');
		if ( $occurences != 1 )
			return (FALSE);
		$pos = strpos($raw_equation, '=');
		if ( $pos == strlen($raw_equation) - 1 || $pos == 0 )
			return (FALSE);
		$split_equation = explode('=', $raw_equation);
		return ($split_equation);
	}

	function split_expression($half_equation, $index)
	{
		$i = $index;
		if (is_operand($half_equation[$i]))
			$i++;
		$len = strlen($half_equation);
		while ($i < $len && !is_operand($half_equation[$i]))
			$i++;
		$expr = substr($half_equation, $index, $i - $index);
		if ((strlen($expr) == 1 && is_operand($expr[0])) || strlen($expr) == 0)
			return (FALSE);
		return ($expr);
	}

	function split_half_equation($half_equation)
	{
		$i = 0;
		$offset = 0;
		$len = strlen($half_equation);
		while ($i < $len)
		{
			$se = split_expression($half_equation, $i);
			if ($se !== FALSE)
			{
				$array[$offset] = $se;
				$i += strlen($array[$offset]);
				if (!is_operand($array[$offset][0]))
					$array[$offset] = "+" . $array[$offset];
				$offset++;
			}
			else
				$i++;
		}
		return ($array);
	}

	function is_pattern1($expression)
	{
		return (preg_match("/[+-]X$/m", $expression));
	}

	function is_pattern2($expression)
	{
		return (preg_match("/[+-]X\^[0-9]{1,}$/", $expression));
	}

	function is_pattern3($expression)
	{
		return (preg_match("/[+-][0-9]{1,}\*X$/", $expression));
	}

	function is_pattern4($expression)
	{
		return (preg_match("/[+-][0-9]{1,}\*X\^[0-9]{1,}$/", $expression));
	}

	function get_degree($expression)
	{
		$pos = strpos($expression, '^');
		return (intval(substr($expression, $pos + 1)));
	}

	function get_value($expression)
	{
		$pos = strpos($expression, '*');
		return (intval(substr($expression, 1, $pos - 1)));
	}

	function get_x_value($expression)
	{
		$sign = ($expression[0] == '-' ? -1 : 1);
		if (string_is_int($expression))
			return (array(0, intval($expression) * $sign));
		if (is_pattern1($expression))
			return (array(1, 1));
		if (is_pattern2($expression))
			return (array(get_degree($expression), 1 * $sign));
		if (is_pattern3($expression))
			return (array(1, get_value($expression) * $sign));
		if (is_pattern4($expression))
			return (array(get_degree($expression), get_value($expression) * $sign));
		return (FALSE);
	}

	function get_x_array($half_equation)
	{
		$expressions = split_half_equation($half_equation);
		foreach ($expressions as $key => $value) {
			$xval = get_x_value($value);
			if ($xval === FALSE)
			{
				echo "Invalid pattern: " . substr($value, 1) . "\n";
				exit(-1);
			}
			if (isset($array[$xval[0]]))
				$array[$xval[0]] += $xval[1];
			else
				$array[$xval[0]] = $xval[1];
		}
		return ($array);
	}

	function get_reduced_form($lhs, $rhs)
	{
		foreach ($rhs as $key => $value) {
			$lhs[$key] -= $rhs[$key];
		}
		return ($lhs);
	}

	function abso($value)
	{
		return ($value < 0 ? -$value : $value);
	}

	function print_reduced_form($reduced_form)
	{
		$printed = 0;
		echo "Reduced form      | ";
		foreach ($reduced_form as $key => $value) {
			if ($value != 0)
			{
				if ($printed)
					echo ($value >= 0 ? " + " : " - ");
				else
				{
					if ($value < 0)
						echo "-";
					$printed = 1;
				}
				if ($key == 0)
					echo abso($value);
				else if ($key == 1)
					echo (abso($value) != 1 ? abso($value) : "") . "X";
				else
					echo (abso($value) != 1 ? abso($value) : "") . "X^" . $key;
			}
		}
		echo " = 0\n";
	}

	function find_degree($reduced_form)
	{
		foreach ($reduced_form as $key => $value) {
			if ($value != 0)
				return ($key);
		}
		return (0);
	}

	function print_degree($reduced_form)
	{
		$degree = find_degree($reduced_form);
		echo "Polynomial degree | " . $degree . "\n";
	}


	function ft_sqrt($value)
	{
		if ($value == 0 || $value == 1)
			return ($nb);
		$res = $value;
		do {
			$diff = $res;
			$res = 0.5 * ($res + $value / $res);
		} while ($res != $diff);
		return ($res);
	}

	function compute_delta($a, $b, $c)
	{
		return (($b * $b) - (4 * $a * $c));
	}

	function print_delta($delta)
	{
		echo "Discriminant      | " . $delta . "\n";
	}

	function find_roots($reduced_form)
	{
		$a = $reduced_form[2];
		$b = $reduced_form[1];
		$c = $reduced_form[0];

		$delta = compute_delta($a, $b, $c);
		print_delta($delta);
		if ($delta > 0)
			return (array((-$b - ft_sqrt($delta)) / (2 * $a), (-$b + ft_sqrt($delta)) / (2 * $a)));
		if ($delta == 0)
			return (array(-$b / (2 * $a)));
		$array[0] = -$b / (2 * $a);
		$array[1] = -ft_sqrt(abso($delta)) / (2 * $a);
		$array[2] = $array[0];
		$array[3] = -($array[1]);
		return ($array);
	}

	function find_solutions($reduced_form)
	{
		$degree = find_degree($reduced_form);
		if ($degree == 1)
			return (FALSE);
		if ($degree == 2)
			return (find_roots($reduced_form));
		if ($degree == 3)
			return (FALSE);
	}

	function print_solutions($solutions)
	{
		$size = count($solutions);
		if ($size == 1)
			echo "Discriminant is null, the polynomial has one real double root:\n" . $solutions[0] . "\n";
		else if ($size == 2)
			echo "Discriminant is strictly positive, the polynomial has two real roots:\n" . $solutions[0] . "\n" . $solutions[1] . "\n";
		else if ($size == 4)
		{
			echo "Discriminant is strictly negative, the polynomial has two complex roots:\n";
			echo $solutions[0] . ($solutions[1] < 0 ? " - " : " + ") . (abso($solutions[1]) != 1 ? abso($solutions[1]) : "") . "i\n";
			echo $solutions[2] . ($solutions[3] < 0 ? " - " : " + ") . (abso($solutions[3]) != 1 ? abso($solutions[3]) : "") . "i\n";
		}
	}

	if ($argc == 2)
	{
		$raw_equation = preg_replace('/\s+/', '', $argv[1]);
		$split_equation = split_equation($raw_equation);
		if ($split_equation === FALSE)
		{
			echo "Parsing error at function [split_equation]\n";
			return (FALSE);
		}
		$split_equation[0] = get_x_array($split_equation[0]);
		$split_equation[1] = get_x_array($split_equation[1]);
		$reduced_form = get_reduced_form($split_equation[0], $split_equation[1]);
		krsort($reduced_form);
		print_reduced_form($reduced_form);
		print_degree($reduced_form);
		$solutions = find_solutions($reduced_form);
		print_solutions($solutions);
	}
	


?>