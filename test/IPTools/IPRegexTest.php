<?php

use IPTools\IPRegex;

class IPRegexTest extends PHPUnit_Framework_TestCase
{
	public function testParser()
	{
		$ips = array(
			0 => array(
				'start'	=> '1.2.3.4',
				'end'	=> '1.2.3.79',
			),
			1 => array(
				'start'	=> '1.2.3.0',
				'end'	=> '1.2.3.255',
			),
			2 => array(
				'start'	=> '1.2.3.255',
				'end'	=> '1.2.3.255',
			),
			3 => array(
				'start'	=> '1.2.3.10',
				'end'	=> '1.2.3.100',
			),
			4 => array(
				'start'	=> '1.2.3/20',
				'end'	=> NULL,
			),
			5 => array(
				'start'	=> '199.7.26/24',
				'end'	=> NULL,
			),
		);
		
		
		foreach($ips as $k => $data)
		{
			$ip_object = new IPTools\IPAddress($data['start'], $data['end']);
			$regex = new IPTools\IPRegex($ip_object);
			$pass = TRUE;
			
			$start = ip2long($ip_object->start());
			$end = ip2long($ip_object->end());
			
			for($x = $start - 300; $x <= $end + 300; $x++)
			{
				$match = preg_match("/{$regex->create()}/", long2ip($x));
				if($x >= $start && $x <= $end)
				{
					if($match == FALSE)
					{
						$pass = FALSE;
					}
				}else{
					if($match != FALSE)
					{
						$pass = FALSE;
					}
				}
			}
			$this->assertTrue($pass);
		}
	}
	
	
	
	public function testDropFirstOctet()
	{
		$drop = IPTools\IPRegex::drop_first_octet('192.168.1.1');
		$this->assertEquals('168.1.1', $drop);

		$drop = IPTools\IPRegex::drop_first_octet('1');
		$this->assertEquals('1', $drop);
	}
}
