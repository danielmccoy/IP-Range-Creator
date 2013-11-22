<?php

namespace IPTools;

class IPRegex
{
	private $_ip_address = NULL;

	/**
	 * Set the IP address for regex
	 * @param IPAddress $ip_address
	 * @return IPRegex
	 */
	public function __construct(IPAddress $ip_address)
	{
		$this->_ip_address = new \stdClass;
		$this->_ip_address->start = $ip_address->start();
		$this->_ip_address->end = $ip_address->end();
		return $this;
	}



	/**
	 * Create regex
	 * @return string
	 */
	public function create()
	{
		// Start the recursion
		return "^" . $this->_run($this->_ip_address->start, $this->_ip_address->end) . "$";
	}
	
	
	
	/**
	 * Recursive regex builder
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	private function _run($start, $end)
	{
		// Split addresses
		$start_split = explode('.', $start);
		$end_split = explode('.', $end);

		// Initialize return
		$regex = '';

		// Ranges
		$zeroes = '';
		$twofivefives = '';
		for($x = 0; $x < count($start_split)-1; $x++)
		{
			$zeroes .= '0.';
			$twofivefives .= '255.';
		}

		if($start_split[0] == $end_split[0] && count($start_split) == 1)
		{
			return $start_split[0];
		}
		else if($start_split[0] == $end_split[0])
		{
			return $start_split[0] . "\\." . $this->_run(IPRegex::drop_first_octet($start), IPRegex::drop_first_octet($end));
		}
		else if(count($start_split) == 1)
		{
			return "(" . $this->_generate($start, $end) . ")";
		}
		else if($start_split[1] == "0" && $end_split[1] == "255")
		{
			return "(" . $this->_generate($start_split[0], $end_split[0]) . ")\\." . $this->_run(IPRegex::drop_first_octet($start), IPRegex::drop_first_octet($end));
		}
		else if($start_split[1] != "0" && $end_split[1] != "255" && ($start_split[1] * 1 + 1) == ($end_split[1] * 1))
		{
			$regex .= "(" . $start_split[0] . "\\." . $this->_run(IPRegex::drop_first_octet($start), $twofivefives);
			$regex .= "|" . $end_split[0] . "\\." . $this->_run($zeroes, IPRegex::drop_first_octet($end)) . ")";
			return $regex;
		}
		else if($start_split[1] != "0" && $end_split[1] != "255")
		{
			$regex .= "(" . $start_split[0] . "\\." . $this->_run(IPRegex::drop_first_octet($start), $twofivefives);
			$regex .= "|(" . $this->_run(($start_split[0] * 1 + 1) . "." . $zeroes, ($end_split[0] * 1 - 1) . "." . $twofivefives) . ")";
			$regex .= "|" . $end_split[0] + "\\." . $this->_run($zeroes, IPRegex::drop_first_octet($end)) . ")";
			return $regex;
		}
		else if($start_split[1] != "0")
		{
			$regex .= "(" . $start_split[0] . "\\." . $this->_run(IPRegex::drop_first_octet($start), $twofivefives);
			$regex .= "|" . $this->_run(($start_split[0] * 1 + 1) . "." . $zeroes, ($end_split[0] * 1) . "." . $twofivefives) . ")";
			return $regex;
		}
		else if($start_split[1] != "255")
		{
			$regex .= "(" . $this->_run($start_split[0] . "." . $zeroes, ($end_split[0] * 1 - 1) . "." . $twofivefives);
			$regex .= "|" . $end_split[0] . "\\." . $this->_run($zeroes, IPRegex::drop_first_octet($end)) . ")";
			return $regex;
		}
	}
	
	
	
	/**
	 * Generate regex sub parts
	 * @param string $a
	 * @param string $b
	 * @return string
	 */
	private function _generate($a, $b)
	{
		if(($a * 1) > ($b * 1))
		{
			$temp = $a;
			$a = $b;
			$b = $temp;
		}
		
		if($a == $b)
		{
			return $a;
		}
		else if($a{0} == $b{0} && strlen($a) == strlen($b) && strlen($a) == 3)
		{
			return $a{0} . "(" . $this->_generate(substr($a, 1), substr($b, 1)) . ")";
		}
		else if(strlen($a) == strlen($b) && strlen($a) == 3)
		{
			return $a{0} . "(" . $this->_generate(substr($a, 1), "99") . ")|" . $this->_generate(($a{0} * 1 + 1). "00", $b);
		}
		else if(strlen($b) == 3)
		{
			return $this->_generate($a, "99") . "|" . $this->_generate("100", $b);
		}
		else if($a{0} == $b{0} && strlen($a) == strlen($b) && strlen($a) == 2)
		{
			return $a{0} . $this->_range($a{1}, $b{1});
		}
		else if(strlen($a) == strlen($b) && $a{1} == "0" && $b{1} == "9" && strlen($a) == 2)
		{
			return $this->_range($a{0}, $b{0}) . $this->_range($a{1}, $b{1});
		}
		else if(strlen($a) == strlen($b) && $a{1} != "0" && strlen($a) == 2)
		{
			return $this->_generate($a, $a{0} . "9") . "|" . $this->_generate(($a{0} * 1 + 1) . "0", $b);
		}
		else if(strlen($a) == strlen($b) && $b{1} != "9" && strlen($b) == 2)
		{
			return $this->_generate($a, ($b{0} * 1 - 1) . "9") . "|" . $this->_generate($b{0} . "0", $b);
		}
		else if(strlen($a) == 1 && strlen($b) == 1)
		{
			return $this->range($a, $b);
		}
		else if(strlen($a) == 1)
		{
			return $this->_range($a, "9") . "|" . $this->_generate("10", $b);
		}
	}



	/**
	 * Range builder
	 * @param string $a
	 * @param string $b
	 * @return string
	 */
	private function _range($a, $b)
	{
		return "[{$a}-{$b}]";
	}



	/**
	 * Drop the first octet from an IP address
	 * @param string $ip_address
	 * @return string
	 */
	static function drop_first_octet($ip_address)
	{
		return (strpos($ip_address, '.') == FALSE) ? $ip_address : substr($ip_address, strpos($ip_address, '.') + 1);
	}



}
