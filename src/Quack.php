<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace QuackCompiler;

require_once dirname(__FILE__) . '/toolkit/QuackToolkit.php';

use \Exception;
use \QuackCompiler\Lexer\Tag;
use \QuackCompiler\Lexer\Tokenizer;
use \QuackCompiler\Parser\TokenReader;
use \QuackCompiler\Parser\SyntaxError;
use \QuackCompiler\Scope\Scope;

class Quack
{
    private $argv;

    public function __construct($argv)
    {
        $this->argv = array_slice($argv, 1);
    }

    public function read()
    {
        $files = $this->getFileNames();

        $this->inArguments('-v', '--version') && print $this->getVersionContent();
        $this->inArguments('-h', '--help') && print $this->getHelpContent();

        try {
            array_walk($files, function($file) {
                if (!file_exists($file)) {
                    echo "File [$file] not found";
                    exit(1);
                }

                $global_scope = new Scope();
                $lexer = new Tokenizer(file_get_contents($file));
                $parser = new TokenReader($lexer);
                $parser->parse();

                if (!$this->inArguments('--disable-scope')) {
                    $parser->ast->injectScope($global_scope);
                }

                if (!$this->inArguments('--disable-typechecker')) {
                    $parser->ast->runTypeChecker();
                }

                echo $parser->format();
            });
        } catch (Exception $e) {
            echo $e;
            exit(1);
        }
    }

    private function getFileNames()
    {
        return array_filter($this->argv, function($x) {
            return '-' !== substr($x, 0, 1);
        });
    }

    private function inArguments()
    {
        return sizeof(array_intersect($this->argv, func_get_args())) > 0;
    }

    private function noArguments()
    {
        return 0 === sizeof($this->argv);
    }

    private function getHelpContent()
    {
        return file_get_contents('./help.txt');
    }

    private function getVersionContent()
    {
        return file_get_contents('./version.txt');
    }
}

$compiler = new Quack($argv);
$compiler->read();
