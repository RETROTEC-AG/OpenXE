<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Formatter;

use Xentral\Components\I18n\Formatter\FloatFormatter;

class IntegerFormatter extends FloatFormatter
{
    
    
    protected function init(): void
    {
        $this->parseType = \NumberFormatter::TYPE_INT64;
        parent::init();
        parent::setMaxDigits(0);
    }
    
    
    
    public function setPhpVal(mixed $input): AbstractFormatter
    {
        return parent::setPhpVal(is_numeric($input) ? intval($input) : $input); // TODO: Change the autogenerated stub
    }
    
    
    
    public function isStrictValidPhpVal($input): bool
    {
        return is_integer($input);
    }
    
    
    
    public function setMinDigits(int $digits): \Xentral\Components\I18n\Formatter\FloatFormatter
    {
        return $this;
    }
    
    
    
    public function setMaxDigits(int $digits): \Xentral\Components\I18n\Formatter\FloatFormatter
    {
        return $this;
    }
    
    
}