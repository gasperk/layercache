<?php
	/**
	Copyright 2009 Gasper Kozak
	
	This file is part of LayerCache.
		
	LayerCache is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LayerCache is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with LayerCache.  If not, see <http://www.gnu.org/licenses/>.

	@package Tests
	**/
	
	require_once 'PHPUnit/Framework.php';
	include_once dirname(__FILE__) . '/../../lib/LayerCache.php';
	
	class MemcacheTest extends PHPUnit_Framework_TestCase
	{
		function setUp()
		{
			if (!extension_loaded('memcache'))
				$this->markTestSkipped("Memcache extension not available.");
		}
		
		function testReadEmpty()
		{
			$mc = $this->getMock('Memcache', array('get'));
			$mc->expects($this->once())->method('get')->with('test')->will($this->returnValue(false));
			$cache = new LayerCache_Cache_Memcache($mc);
			$this->assertSame(null, $cache->read('test'));
		}
		
		function testWriteAndRead()
		{
			$mc = $this->getMock('Memcache', array('get', 'set'));
			
			$mc->expects($this->at(0))->method('get')->with('test')->will($this->returnValue(false));
			$mc->expects($this->at(1))->method('set')->with('test', serialize('DATA'), 0, 10);
			$mc->expects($this->at(2))->method('get')->with('test')->will($this->returnValue(serialize('DATA')));
			
			$cache = new LayerCache_Cache_Memcache($mc, 10);
			$this->assertSame(null, $cache->read('test'));
			$cache->write('test', 'DATA');
			$this->assertSame('DATA', $cache->read('test'));
		}
		
		function testWriteAndReadComplexStructure()
		{
			$mc = $this->getMock('Memcache', array('get', 'set'));
			
			$o = new StdClass;
			$o->z = 34;
			$data = array('x', $o, array('a' => 12));
			
			$mc->expects($this->at(0))->method('get')->with('test')->will($this->returnValue(false));
			$mc->expects($this->at(1))->method('set')->with('test', serialize($data), 0, 10);
			$mc->expects($this->at(2))->method('get')->with('test')->will($this->returnValue(serialize($data)));
			
			$cache = new LayerCache_Cache_Memcache($mc, 10);
			$this->assertSame(null, $cache->read('test'));
			$cache->write('test', $data, 10);
			$this->assertEquals($data, $cache->read('test'));
		}
	}
	
