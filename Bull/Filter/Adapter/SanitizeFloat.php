<?php
/**
 * 
 * Sanitizes a value to a float.
 * 
 * @package Bull.Filter.Adapter
 * 
 * @author Gu Weigang <guweigang@baidu.com>
 * 
 * 
 */
class Bull_Filter_Adapter_SanitizeFloat extends Bull_Filter_Adapter_Abstract
{
    /**
     * 
     * Forces the value to a float.
     * 
     * Attempts to extract a valid float from the given value, using an
     * algorithm somewhat less naive that "remove all characters that are not
     * '0-9.,eE+-'".  The result may not be expected, but it will be a float.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @return float The sanitized value.
     * 
     * @todo Extract scientific notation from weird strings?
     * 
     */
    public function __invoke($value)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->objManager->getRequire() &&
            $this->objManager->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize.  non-string, or already numeric, get converted in
        // place.
        if (! is_string($value) || is_numeric($value)) {
            return (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract a float from it.
        
        // remove all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = str_replace('+', '', $value);
        
        // reduce multiple decimals and minuses
        $value = preg_replace('/[\.-]{2,}/', '.', $value);
        
        // remove all decimals without a digit or minus next to them
        $value = preg_replace('/([^0-9-]\.[^0-9])/', '', $value);
        
        // remove all chars except digit, decimal, and minus
        $value = preg_replace('/[^0-9\.-]/', '', $value);
        
        // remove all trailing decimals and minuses
        $value = rtrim($value, '.-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return (float) $value;
        }
        
        // remove all minuses not at the front
        $is_negative = ($value[0] == '-');
        $value = str_replace('-', '', $value);
        if ($is_negative) {
            $value = '-' . $value;
        }
        
        // remove all decimals but the first
        $pos = strpos($value, '.');
        $value = str_replace('.', '', $value);
        if ($pos !== false) {
            $value = substr($value, 0, $pos)
                   . '.'
                . substr($value, $pos);
        }
        
        // looks like we're done
        return (float) $value;
    }
}