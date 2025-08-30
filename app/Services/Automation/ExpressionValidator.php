<?php

namespace App\Services\Automation;

class ExpressionValidator
{
    /**
     * Allowed pure functions and their signatures (simple type system).
     * Signature format: [returnType, [argType1, argType2, ...]]
     */
    private array $functionSignatures = [
        'trim' => ['string', ['string']],
        'toLower' => ['string', ['string']],
        'toUpper' => ['string', ['string']],
        'substring' => ['string', ['string', 'number', 'number']],
        'length' => ['number', ['string']],
        'toNumber' => ['number', ['string']],
        'round' => ['number', ['number']],
        'floor' => ['number', ['number']],
        'ceil' => ['number', ['number']],
        'toBoolean' => ['boolean', ['string']],
        'regexGroup' => ['string', ['string', 'string', 'number']],
        'jsonPath' => ['json', ['json', 'string']],
        'parseDate' => ['datetime', ['string', 'string']],
        'formatDate' => ['string', ['datetime', 'string']],
    ];

    /**
     * Parse and validate a transform expression without executing it.
     * Limited grammar: funcName(arg1, arg2, ...)
     */
    public function validateTransform(string $expr, array $inputTypes): array
    {
        $expr = trim($expr);
        if ($expr === '') {
            return ['ok' => false, 'error' => 'Empty expression'];
        }

        if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*)\)$/', $expr, $m)) {
            return ['ok' => false, 'error' => 'Invalid expression syntax'];
        }
        $func = $m[1];
        $argsStr = trim($m[2]);

        if (!isset($this->functionSignatures[$func])) {
            return ['ok' => false, 'error' => "Function '$func' is not allowed"]; 
        }

        $args = $this->splitArgs($argsStr);
        $sig = $this->functionSignatures[$func];
        $expectedArgs = $sig[1];
        if (count($args) !== count($expectedArgs)) {
            return ['ok' => false, 'error' => 'Wrong number of arguments'];
        }

        // Infer argument types: variables/placeholders are provided via $inputTypes map
        foreach ($args as $i => $arg) {
            $argType = $this->inferType($arg, $inputTypes);
            if ($argType === null) {
                return ['ok' => false, 'error' => "Unresolvable argument type at position " . ($i+1)];
            }
            $expected = $expectedArgs[$i];
            if (!$this->isTypeCompatible($argType, $expected)) {
                return ['ok' => false, 'error' => "Type mismatch for arg " . ($i+1) . ": expected $expected, got $argType"]; 
            }
        }

        return ['ok' => true, 'returnType' => $sig[0]];
    }

    private function splitArgs(string $args): array
    {
        $res = [];
        $depth = 0;
        $buf = '';
        $len = strlen($args);
        for ($i = 0; $i < $len; $i++) {
            $ch = $args[$i];
            if ($ch === '(') { $depth++; $buf .= $ch; continue; }
            if ($ch === ')') { $depth--; $buf .= $ch; continue; }
            if ($ch === ',' && $depth === 0) { $res[] = trim($buf); $buf = ''; continue; }
            $buf .= $ch;
        }
        if (trim($buf) !== '') { $res[] = trim($buf); }
        if (count($res) === 1 && $res[0] === '') { return []; }
        return $res;
    }

    private function inferType(string $arg, array $inputTypes): ?string
    {
        $arg = trim($arg);
        if (preg_match('/^\d+(?:\.\d+)?$/', $arg)) { return 'number'; }
        if ($arg === 'true' || $arg === 'false') { return 'boolean'; }
        if ((str_starts_with($arg, '"') && str_ends_with($arg, '"')) || (str_starts_with($arg, "'") && str_ends_with($arg, "'"))) { return 'string'; }
        if ($arg === 'null') { return 'json'; }
        // Placeholder reference
        if (preg_match('/^\{\{\s*([^}]+)\s*\}\}$/', $arg, $m)) {
            $key = $m[1];
            return $inputTypes[$key] ?? null;
        }
        return null;
    }

    private function isTypeCompatible(string $from, string $to): bool
    {
        if ($from === $to) { return true; }
        if (($from === 'number' && $to === 'string') || ($from === 'string' && $to === 'number')) {
            return true;
        }
        return false;
    }
}


