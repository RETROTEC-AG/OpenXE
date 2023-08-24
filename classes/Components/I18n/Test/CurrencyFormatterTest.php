<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Xentral\Components\I18n\Test;

require(__DIR__ . '/../../../bootstrap.php');

use Xentral\Components\I18n\Formatter\CurrencyFormatter;
use PHPUnit\Framework\TestCase;
use Xentral\Components\I18n\Formatter\FloatFormatter;
use Xentral\Components\I18n\Formatter\FormatterMode;

class CurrencyFormatterTest extends TestCase
{
    public function testStrictModeWithValue()
    {
        $floatFormatter = new CurrencyFormatter('de_DE', FormatterMode::MODE_STRICT);
        $output = $floatFormatter->setPhpVal(floatval(1234.56))->formatForUser();
        var_dump($output);
        $this->assertIsString($output);
        $this->assertEquals('1.234,56 EUR', $output);
        
        $floatFormatter = new CurrencyFormatter('de_DE', FormatterMode::MODE_STRICT);
        $output = $floatFormatter->setPhpVal(intval(1234))->formatForUser();
        var_dump($output);
        $this->assertIsString($output);
        $this->assertEquals('1.234 EUR', $output);
        
        $floatFormatter = new CurrencyFormatter('de_CH', FormatterMode::MODE_STRICT);
        $output = $floatFormatter->setPhpVal(floatval(1234.56))->formatForUser();
        var_dump($output);
        $this->assertIsString($output);
        $this->assertEquals('CHF 1’234.56', $output);
    }
    
}
