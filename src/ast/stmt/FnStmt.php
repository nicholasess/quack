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
namespace QuackCompiler\Ast\Stmt;

use \QuackCompiler\Parser\Parser;

class FnStmt implements Stmt
{
    public $name;
    public $by_reference;
    public $body;
    public $parameters;
    public $is_bang;
    public $is_pub;

    public function __construct($name, $by_reference, $body, $parameters, $is_bang, $is_pub)
    {
        $this->name = $name;
        $this->by_reference = $by_reference;
        $this->body = $body;
        $this->parameters = $parameters;
        $this->is_bang = $is_bang;
        $this->is_pub = $is_pub;
    }

    public function format(Parser $parser)
    {
        $source = $this->is_pub
            ? 'pub fn '
            : 'fn ';

        if ($this->by_reference) {
            $source .= '* ';
        }

        $source .= $this->name;

        if (sizeof($this->parameters) > 0) {
            $source .= '[ ';

            $source .= implode('; ', array_map(function ($param) {
                $subsource = '';
                $obj = (object) $param;

                if ($obj->ellipsis) {
                    $subsource .= '... ';
                }

                if ($obj->by_reference) {
                    $subsource .= '*';
                }

                $subsource .= $obj->name;

                return $subsource;
            }, $this->parameters));

            $source .= ' ]';
        } else {
            $source .= $this->is_bang
                ? '!'
                : '[]';
        }

        $source .= PHP_EOL;

        if (null !== $this->body) {
            $parser->openScope();

            foreach ($this->body as $stmt) {
                $source .= $parser->indent();
                $source .= $stmt->format($parser);
            }

            $parser->closeScope();

            $source .= $parser->indent();
            $source .= 'end';
            $source .= PHP_EOL;
        }

        return $source;
    }
}
