<?php
require(dirname(dirname(__FILE__))) . '/net_ip.php';

class Net_Ip_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider randomAddresses
     */
    public function testValidate($addr)
    {
        $this->assertTrue(Net_Ip::validate($addr));
    }
    
    /**
     * @dataProvider v4RandomAddresses
     */
    public function testValidate4($addr)
    {
        $this->assertTrue(Net_Ip::validate($addr, Net_Ip::V4));
    }
    
    /**
     * @dataProvider v6RandomAddresses
     */
    public function testValidate6($addr)
    {
        $this->assertTrue(Net_Ip::validate($addr, Net_Ip::V6));
    }
    
    /**
     * @dataProvider v6RandomAddresses
     */
    public function testV6compression($addr)
    {
        $this->assertEquals($addr, Net_Ip::uncompress($addr));
        $this->assertEquals($addr, Net_Ip::uncompress(Net_Ip::compress($addr)));
    }
    
    
    public function randomAddresses()
    {
        return array_merge(self::v4RandomAddresses(), self::v6RandomAddresses());
    }
    
    public function v4RandomAddresses()
    {
        $addresses = array();
        for ($i = 0; $i < 10; $i++) {
            $parts = array();
            for($j = 0; $j < 4; $j++) {
                $parts[] = rand(0,255);
            }
            $addresses[] = array(implode('.', $parts));
        }
        return $addresses;
    }
    
    
    public function v6RandomAddresses()
    {
        $addresses = array();
        for ($i = 0; $i < 10; $i++) {
            $parts = array();
            for($j = 0; $j < 8; $j++) {
                $parts[] = str_pad(base_convert(rand(0,65535), 10, 16), 4, '0', STR_PAD_LEFT);
            }
            $addresses[] = array(implode(':', $parts));
        }
        return $addresses;
    }
}