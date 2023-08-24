<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Formatter;

use Xentral\Components\I18n\Formatter\Exception\TypeErrorException;

/**
 * Parse and format float numbers..
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class FloatFormatter extends AbstractFormatter implements FormatterInterface
{
    private \NumberFormatter $numberFormatter;
    protected int $parseType = \NumberFormatter::TYPE_DOUBLE;
    protected int $formatterStyle = \NumberFormatter::DECIMAL;
    
    
    
    /**
     * Initialize PHP \NumberFormatter.
     *
     * @return void
     */
    protected function init(): void
    {
        $this->setMinDigits(0);
        $this->setMaxDigits(100);
    }
    
    
    
    public function isStrictValidPhpVal($input): bool
    {
        return is_numeric($input);
    }
    
    
    
    /**
     * Parse string from user input and store as float in object.
     * If parsing fails, an Exception is thrown.
     *
     * @param string $input
     *
     * @return self
     */
    public function parseUserInput(string $input): self
    {
        $this->setOriginalInput($input);
        
        // Sanitize string
        $input = trim(strtolower(stripslashes(strval($input))), "abcdefghijklmnopqrstuvwxyz \t\n\r\0\x0B");
        
        if ($input === '') {
            // Check if user has entered an empty value and we are in strictness MODE_NULL
            if ($this->getStrictness() == FormatterMode::MODE_NULL) {
                $this->setParsedValue(null);
                return $this;
            }
            
            // Check if user has entered an empty string and we are in strictness MODE_EMPTY
            if ($this->getStrictness() == FormatterMode::MODE_EMPTY) {
                $this->setParsedValue('');
                return $this;
            }
            
            throw new TypeErrorException(
                "Value " . var_export($input, true) . " is not a valid type for " . get_class(
                    $this
                ) . " with strictness {$this->getStrictness()->name}"
            );
        }
        
        
        // From here on, $input must contain a parseable number
        
        
        // This can cause problems in germany, because of . as thousands sep. Imagine user enters 1.000 (=1000).
//        if (is_numeric($input)) {
//            // $input is already numeric. No further parsing needed.
//            $this->setParsedValue(floatval($input));
//            return $this;
//        }
        
        if (($output = $this->getNumberFormatter()->parse($input, $this->parseType)) === false) {
            // could not parse number
            // as a last resort, try to parse the number with locale de_DE
            // This is necessary, because of many str_replace, where dot is replaced with comma
            $fmtLr = new \NumberFormatter('de_DE', \NumberFormatter::DECIMAL);
            if (!$output = $fmtLr->parse($input, $this->parseType)) {
                throw new \RuntimeException("{$this->getNumberFormatter()->getErrorMessage()}. \$input={$input}");
            }
        }
        
        $this->setParsedValue($output);
        return $this;
    }
    
    
    
    /**
     * Return a string representing the PHP value as a formatted value.
     * Throws an Exception if no value was set before or the value is of the wrong type.
     *
     * @return string
     */
    public function formatForUser(): string
    {
        return ($this->isNullValidPhpValue($this->getPhpVal()) || $this->isEmptyValidPhpValue($this->getPhpVal()))
            ? ''
            : $this->getNumberFormatter()->format($this->getPhpVal());
    }
    
    
    
    /**
     * Return a string that can be used in an SQL query to format the value for presentation to a User.
     * Should return the same string as if it was formatted by FormatterInterface::formatForUser(), but directly from
     * the database.
     * This function does not need a native PHP value, but a table column is needed.
     *
     * @param string $col
     *
     * @return string
     */
    public function formatForUserWithSqlStatement(string $col): string
    {
        $min_decimals = $this->getNumberFormatter()->getAttribute(\NumberFormatter::MIN_FRACTION_DIGITS);
        $max_decimals = $this->getNumberFormatter()->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
        return ("FORMAT({$col},LEAST('{$max_decimals}',GREATEST('{$min_decimals}',LENGTH(TRIM(TRAILING '0' FROM SUBSTRING_INDEX(CAST({$col} AS CHAR),'.',-1))))),'{$this->getLocale()}')");
    }
    
    
    
    /**
     * Set the minimum displayed fraction digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMinDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Set the maximum displayed fraction digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMaxDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Set the minimum displayed integer digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMinIntDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MIN_INTEGER_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Set the maximum displayed integer digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMaxIntDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MAX_INTEGER_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Set the minimum displayed significant digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMinSignificantDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MIN_SIGNIFICANT_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Set the maximum displayed significant digits for formatted output.
     *
     * @param int $digits
     *
     * @return $this
     */
    public function setMaxSignificantDigits(int $digits): self
    {
        $this->getNumberFormatter()->setAttribute(\NumberFormatter::MAX_SIGNIFICANT_DIGITS, $digits);
        return $this;
    }
    
    
    
    /**
     * Return a \NumberFormatter object from cache. If the object does not exist, it is created first.
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter(): \NumberFormatter
    {
        if (!isset($this->numberFormatter)) {
            $this->numberFormatter = new \NumberFormatter($this->getLocale(), $this->formatterStyle);
            $this->numberFormatter->setAttribute(\NumberFormatter::LENIENT_PARSE, 1);
        }
        return $this->numberFormatter;
    }
}