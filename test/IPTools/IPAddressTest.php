<?php

use IPTools\IPAddress;

class IPAddressTest extends PHPUnit_Framework_TestCase
{
	public function testParser()
	{
		// Single IP
		$ip1 = new IPAddress('192.168.1.15');
		$this->assertTrue(is_object($ip1));
		$this->assertEquals($ip1->start(), '192.168.1.15');
		$this->assertNull($ip1->end());

		// IP range
		$ip2 = new IPAddress('192.168.1.1', '192.168.1.150');
		$this->assertTrue(is_object($ip2));
		$this->assertEquals($ip2->start(), '192.168.1.1');
		$this->assertEquals($ip2->end(), '192.168.1.150');

		// IP range (backwards)
		$ip3 = new IPAddress('192.168.1.150', '192.168.1.1');
		$this->assertTrue(is_object($ip3));
		$this->assertEquals($ip3->start(), '192.168.1.1');
		$this->assertEquals($ip3->end(), '192.168.1.150');

		// IP range (same IP)
		$ip4 = new IPAddress('192.168.1.100', '192.168.1.100');
		$this->assertTrue(is_object($ip4));
		$this->assertEquals($ip4->start(), '192.168.1.100');
		$this->assertNull($ip4->end());

		// Class D sub block
		$ip5 = new IPAddress('192.168.1.10/28');
		$this->assertTrue(is_object($ip5));
		$this->assertEquals($ip5->start(), '192.168.1.10');
		$this->assertEquals($ip5->end(), '192.168.1.26');

		// Class C block
		$ip6 = new IPAddress('192.168.10/22');
		$this->assertTrue(is_object($ip6));
		$this->assertEquals($ip6->start(), '192.168.10.0');
		$this->assertEquals($ip6->end(), '192.168.13.255');
		
		// Exceptions
		
		// Invalid addresses
		$this->setExpectedException('Exception');
		new IPAddress('Not Even Close');
		$this->setExpectedException('Exception');
		new IPAddress('192.168.1.300');
		$this->setExpectedException('Exception');
		new IPAddress('192.168.1.255', '192.168.1.300');
		
		// Invalid block
		$this->setExpectedException('Exception');
		new IPAddress('192.168.1.255/12345');
		$this->setExpectedException('Exception');
		new IPAddress('192.168.1.255/1');
		$this->setExpectedException('Exception');
		new IPAddress('192.168.1.255/BLOCK');
	}
}
