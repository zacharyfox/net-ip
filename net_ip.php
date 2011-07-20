<?php
class Net_Ip
{
    const V6 = 6;
    const V4 = 4;
    
    /**
     * Validate an IP address
     * 
     * @param string   $address IP Address in presentation format
     * @param constant $version IP version Net_Ip::V4 | Net_Ip::V6    		
     * 
     * @return boolean
     * @throws Exception
     */
    public static function validate($address, $version = null)
    {
        switch ($version) {
        case self::V4:
            return self::validate4($address);
            break;

        case self::V6:
            return self::validate6($address);
            break;

        case null:
            return self::version($address) === false ? false : true;
            break;
                
        default:
            throw new Exception("Invalid version");
            break;
        }

        return false;
    }
    
    
    /**
     * Validate a netmask in string notation
     * 
     * @param string   $netmask IP Netmask in presentation format
     * @param constant $version IP version Net_Ip::V4 | Net_Ip::V6
     * 
     * @return boolean
     * @throws Exception
     */
    public static function validateNetmask($netmask, $version = null)
    {
        if ($version === null && !($version = self::version($netmask))) {
            return false;
        }
        
        try {
            $bits = self::netmask2bitmask($netmask);
        } catch (Exception $e) {
            return false;
        }
        
        switch ($version) {
        case V4:
            ($bits >= 0 && $bits <= 32) ? true : false;
            break;

        case V6:
            ($bits >= 0 && $bits <= 128) ? true : false;
            break;
            
        default:
            throw new Exception("Invalid version");
            break;
        }

        return false;
    }
    
    
    /**
     * Determines if an address is in a given network
     *
     * @param string $address Address in presentation format
     * @param string $network Network in CIDR notation
     * 
     * @return boolean
     * @throws Exception
     */
    public static function inNetwork($address, $network)
    {
        if (!self::validate($address)) {
            throw new Exception("Invalid address");
        }

        list($networkAddress, $bitmask) = explode('/', $network);
        
        if (strncmp(ip2bin($address), ip2bin($networkAddress), $bitmask) == 0) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Determines if an address falls in a given range
     *
     * @param string $address Address in presentation format
     * @param string $start   Start address in presentation format
     * @param string $end     End address in presentation format
     * 
     * @return boolean
     * @throws Exception
     */
    public static function inRange($address, $start, $end)
    {
        if (!self::validate($address)) {
            throw new Exception("Invalid address");
        }
        
        if (!self::validate($start)) {
            throw new Exception("Invalid start address");
        }
        
        if (!self::validate($end)) {
            throw new Exception("Invalid end address");
        }
        
        if (inet_pton($address) >= inet_pton($start)
                && inet_pton($address) <= inet_pton($end)) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Compress an IPv6 Address
     * 
     * @param string $address IPv6 Address in presentation format
     * 
     * @return string IPv6 Address in compressed presentation format
     * @throws Exception
     */
    public static function compress($address)
    {
        if (!self::validate($address, self::V6)) {
            throw new Exception("Only IPv6 addresses can be compressed");
        }
        
        return inet_ntop(inet_pton($address));
    }
    
    
    /**
     * Uncompress an IPv6 Address
     * 
     * @param string $address IPv6 Address in presentation format
     * 
     * @return string IPv6 Address in uncompressed presentation format
     * @throws Exception
     */
    public static function uncompress($address)
    {
        if (!self::validate($address, self::V6)) {
            throw new Exception("Only IPv6 addresses can be compressed");
        }
        
        $address = inet_pton($address);
        $return  = '';
        foreach (str_split($address) as $chunk) {
            $return .= str_pad(dechex(ord($chunk)), 2, '0', STR_PAD_LEFT);
        }
        
        return implode(':', str_split($return, 4));
    }
    
    
    /**
     * Get the number of netmask bits from a netmask in presentation format
     *
     * @param string $netmask Netmask in presentation format
     * 
     * @return integer Number of mask bits
     * @throws Exception
     */
    public static function netmask2Bitmask($netmask)
    {
        if (!self::validate($netmask)) {
            throw new Exception("Invalid netmask format");
        }

        $binString = self::ip2bin($netmask);

        if (0 === preg_match('/^(1{0,})(0{0,})$/', $binString, $matches)) {
            throw new Exception("Invalid netmask");
        }
        
        return strlen($matches[1]);
    }
    
    
    /**
     * Get the IP version of an address or boolean false if it is not valid
     * 
     * @param string $address IP address in presentation format
     * 
     * @return integer|boolean Class constant for type or false
     */
    public static function version($address)
    {
        if (self::validate4($address)) {
            return self::V4;
        }
        
        if (self::validate6($address)) {
            return self::V6;
        }
        
        return false;
    }
    
    
    /**
     * Convert an IP in presentation format to it's binary string
     *
     * @param string $address IPv4 or IPv6 address in presentation format
     * 
     * @return string Address in 32 or 128 length binary string (1s and 0s)
     * @throws Exception
     */
    protected static function ip2bin($address)
    {
        if (!self::validate($address)) {
            throw new Exception("Invalid address for binary conversion");
        }
        
        $bin     = '';
        $address = inet_pton($address);
        foreach (str_split($address) as $char) {
            $bin .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        
        return $bin;
    }
    
    
    /**
     * Convert an IP in binary string to presentation format
     *
     * @param string $bin IPv4 or IPv6 address in binary string
     * 
     * @return string Address in presentation format
     * @throws Exception
     */
    protected static function bin2ip($bin)
    {
        if ((strlen($bin) !== 32 && strlen($bin) !== 128)
                || (1 === preg_match('/[^01]/', $bin))) {
            throw new Exception("Invalid binary string for address conversion");
        }
        
        $address = '';
        foreach (str_split($bin, 8) as $byte) {
            $address .= chr(bindec($byte));
        }
        
        return inet_ntop($address);
    }
    
    
    /**
     * Validates an IPv4 address
     * 
     * @param string $address IPv4 address in presentation format
     * 
     * @return boolean
     */
    protected static function validate4($address)
    {
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            ? true
            : false;
    }
    
    
    /**
     * Validates an IPv6 address
     * 
     * @param string $address IPv6 address in presentation format
     * 
     * @return boolean
     */
    protected static function validate6($address)
    {
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? true
            : false;
    }
}

