#!/usr/bin/env php
<?php

/**
 * This file is part of the GlorpenCompassConnectorBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

$loader = require_once dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArgvInput;
use Glorpen\CompassConnectorBundle\Command\CompileCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Glorpen\CompassConnectorBundle\Tests\TestContainer;

$cmd = new CompileCommand();
$cmd->setContainer(new TestContainer());
$cmd->setHelperSet(new HelperSet(array(new DialogHelper())));
$cmd->run(new ArgvInput(array()), new ConsoleOutput());
