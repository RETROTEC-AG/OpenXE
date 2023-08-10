<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Formatter;

/**
 * AbstractFormatter.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
abstract class AbstractFormatter implements FormatterInterface
{
    private string $locale;
    private mixed $originalInput;
    private mixed $parsedValue;
    
    
    
    public function __construct(string $locale)
    {
        $this->locale = $locale;
        $this->init();
    }
    
    
    
    /**
     * Initialize the formatter. Overload this instead of the constructor.
     *
     * @return void
     */
    protected function init(): void
    {
    }
    
    
    
    /**
     * Set the native PHP value in the formatter.
     * The value must ALWAYS be of the requested type or an Exception is thrown.
     *
     * @param mixed $input
     *
     * @return self
     */
    protected function setParsedValue($value): self
    {
        $this->parsedValue = $value;
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
    public function setPhpVal($input): self
    {
        $this->parsedValue = $input;
        return $this;
    }
    
    
    
    /**
     * Get the native PHP value from the formatter.
     * The value must ALWAYS be of the requested type or an Exception is thrown.
     *
     * @return mixed
     */
    public function getPhpVal(): mixed
    {
        return $this->parsedValue;
    }
    
    
    
    /**
     * Return the locale used for output formatting.
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return $this->locale;
    }
    
    
    
    /**
     * Store original value before parsing.
     * @depracated
     * @param $input
     *
     * @return $this
     */
    protected function setOriginalInput($input): self
    {
        $this->originalInput = $input;
        return $this;
    }
}