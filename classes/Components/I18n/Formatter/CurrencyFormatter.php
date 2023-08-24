<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Formatter;

use Xentral\Components\I18n\Bootstrap;
use Xentral\Components\I18n\Formatter\FloatFormatter;

class CurrencyFormatter extends FloatFormatter
{
    private string $ccy;
    
    
    
    protected function init(): void
    {
        $this->parseType = \NumberFormatter::TYPE_CURRENCY;
        $this->formatterStyle = \NumberFormatter::CURRENCY;
        parent::init();
        
        $parsedLocale = \Locale::parseLocale($this->getLocale());
        $this->setCcy(
            Bootstrap::findRegion($parsedLocale['region'])[\Xentral\Components\I18n\Iso3166\Key::CURRENCY_CODE] ?? ''
        );
        
        // Set text representation for currency, not the symbol
//        $this->getNumberFormatter()->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $this->getCcy());
        $this->getNumberFormatter()->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $this->getCcy());
//        $this->getNumberFormatter()->setPattern(preg_replace('/^(?:\s|\xc2\xa0)+|(?:\s|\xc2\xa0)+$/u', '', str_replace("¤", '', $this->getNumberFormatter()->getPattern())));
    }
    
    
    
    public function __construct(string $locale, FormatterMode $strictness = FormatterMode::MODE_STRICT)
    {
        parent::__construct($locale, $strictness);
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
            : $this->getNumberFormatter()->formatCurrency($this->getPhpVal(), $this->getCcy());
    }
    
    
    
    public function getCcy(): string
    {
        return $this->ccy;
    }
    
    
    
    public function setCcy(string $ccy): void
    {
        $this->ccy = $ccy;
    }
    
    
}