<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Formatter;

/**
 * Parse and format float numbers..
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class FloatFormatter extends AbstractFormatter implements FormatterInterface
{
    private \NumberFormatter $numberFormatter;
    
    
    
    /**
     * Initialize PHP \NumberFormatter.
     *
     * @return void
     */
    protected function init(): void
    {
        $this->setMaxDigits(20);
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
        
        $input = trim(strtolower(stripslashes($input)), "abcdefghijklmnopqrstuvwxyz \t\n\r\0\x0B");
        
        if (empty($input)) {
            // No input
            $this->setParsedValue(0.0);
            return $this;
        }
        
        // This can cause problems in germany, because of . as thousands sep. Imagine user enters 1.000 (=1000).
//        if (is_numeric($input)) {
//            // $input is already numeric. No further parsing needed.
//            $this->setParsedValue(floatval($input));
//            return $this;
//        }
        
        if (($output = $this->getNumberFormatter()->parse($input)) === false) {
            // could not parse number
            // as a last resort, try to parse the number with locale de_DE
            // This is necessary, because of many str_replace, where dot is replaced with comma
            $fmtLr = new \NumberFormatter('de_DE', \NumberFormatter::DECIMAL);
            if (!$output = $fmtLr->parse($input)) {
                throw new \RuntimeException("{$this->getNumberFormatter()->getErrorMessage()}. \$input={$input}");
            }
        }
        
        $this->setParsedValue(floatval($output));
        return $this;
    }
    
    
    
    /**
     * Set the native PHP value in the formatter.
     * The value must ALWAYS be of the requested type or an Exception is thrown.
     *
     * @param mixed $input
     *
     * @return self
     */
    public function setPhpVal(mixed $input): self
    {
        parent::setPhpVal(floatval($input));
        return $this;
    }
    
    
    
    /**
     * Get the native PHP value from the formatter.
     * The value must ALWAYS be of the requested type or an Exception is thrown.
     *
     * @return float
     */
    public function getPhpVal(): float
    {
        return parent::getPhpVal();
    }
    
    
    
    /**
     * Return a string representing the PHP value as a formatted value.
     * Throws an Exception if no value was set before or the value is of the wrong type.
     *
     * @return string
     */
    public function formatForUser(): string
    {
        return $this->getNumberFormatter()->format($this->getPhpVal());
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
     * Return a \NumberFormatter object from cache. If the object does not exist, it is created first.
     *
     * @return \NumberFormatter
     */
    private function getNumberFormatter(): \NumberFormatter
    {
        if (!isset($this->numberFormatter)) {
            $this->numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::DECIMAL);
            $this->numberFormatter->setAttribute(\NumberFormatter::LENIENT_PARSE, 1);
        }
        return $this->numberFormatter;
    }
}