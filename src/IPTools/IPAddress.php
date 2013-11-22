<?php 

namespace IPTools;

class IPAddress
{
	private $_data = NULL;

	/**
	 * Initialize
	 * Accepts a single IP address, IP address range, or CIDR block
	 * @example new IPAddress('192.168.1.1')
	 * @example new IPAddress('192.168.1.1', '192.168.1.1.150')
	 * @example new IPAddress('192.168.1.10/28')
	 * @example new IPAddress('192.168.10/24')
	 *
	 * @param string $ip_start
	 * @param string $ip_end
	 * @return IPAddress
	 */
	public function __construct($ip_start, $ip_end = NULL)
	{
		// Initialize data structure
		$this->_data = new \stdClass;
		$this->_data->start = NULL;
		$this->_data->end = NULL;
	
		// Process IP start
		$start = IPAddress::parse($ip_start);
		if($start !== FALSE)
		{
			$this->_data = $start;
		}else{
			throw new \Exception('Invalid IP address arguments.');
		}
		
		// Process IP end
		if($ip_end != NULL)
		{
			$end = IPAddress::parse($ip_end);
			if($end !== FALSE)
			{
				$this->_data->end = $end->start;
			}else{
				throw new \Exception('Invalid IP address arguments.');
			}
		}
		
		// Nothing was processed
		if($this->_data->start === NULL && $this->_data->end === NULL)
		{
			throw new \Exception('Invalid IP address arguments.');
		}
		
		// Edge cases
		if($this->_data->start !== NULL && $this->_data->end !== NULL)
		{
			// IP addresses range is reversed
			if(ip2long($this->_data->start) > ip2long($this->_data->end))
			{
				// Swap start and end IP addresses
				$tmp_end = $this->_data->end;
				$this->_data->end = $this->_data->start;
				$this->_data->start = $tmp_end;
			}
			
			// IP range is the same address
			if(ip2long($this->_data->start) == ip2long($this->_data->end))
			{
				$this->_data->end = NULL;
			}
		}

		return $this;
	}
	
	
	
	/**
	 * Parse an address/block
	 * @param string $ip_address
	 * @return stdClass|bool
	 */
	static function parse($ip_address)
	{
		$return = new \stdClass;
		$return->start = NULL;
		$return->end = NULL;
	
		// Standard single address
		if(preg_match('/^(([1-9]?\d|1\d\d|2[0-5][0-5]|2[0-4]\d)\.){3}([1-9]?\d|1\d\d|2[0-5][0-5]|2[0-4]\d)$/', $ip_address))
		{
			$return->start = $ip_address;
			return $return;
		}
		
		// Standardize CIDR block format
		if(preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\/([0-9]+)$/', $ip_address, $m))
		{
			$ip_address = "{$m[1]}.{$m[2]}.{$m[3]}.0/{$m[4]}";
		}
		
		// Look for CIDR block - supports /32-20
		if(preg_match('/^([.0-9]{1,})\/([2][0-9])|([3][0-2])$/', $ip_address, $m))
		{
			$return->start = $m[1];
			$return->end = IPAddress::add($m[1], $m[2]);
			return $return;
		}

		// Did not match any patterns
		return FALSE;
	}
	
	
	
	/**
	 * Add a block to an ip address
	 * @param string $ip_address
	 * @param int $block_size
	 */
	static function add($ip_address, $block_size)
	{
		// Create CIDR size array
		$size = 1;
		$block = array();
		for($x = 32; $x >= 20; $x-=1)
		{
			$block[$x] = ($x <= 24) ? $size-1 : $size;
			$size = $size * 2;
		}
		
		if(!isset($block[$block_size]))
		{
			throw new \Exception('Block size out of range. '.$block_size);
		}

		$long = ip2long($ip_address);
		return(long2ip($long + $block[$block_size]));
	}
	
	
	
	/**
	 * Starting IP address
	 * @return string|null
	 */
	public function start()
	{
		return $this->_data->start;
	}



	/**
	 * Ending IP address
	 * @return string|null
	 */
	public function end()
	{
		return $this->_data->end;
	}
}

