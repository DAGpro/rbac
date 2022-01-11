<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\CompositeRule;
use Yiisoft\Rbac\Permission;

final class CompositeRuleTest extends TestCase
{
    public function compositeRuleDataProvider(): array
    {
        return [
            'AND empty' => [CompositeRule::AND, [], true],
            'AND all true' => [CompositeRule::AND, [new EasyRule(true), new EasyRule(true)], true],
            'AND last false' => [CompositeRule::AND, [new EasyRule(true), new EasyRule(false)], false],

            'OR empty' => [CompositeRule::OR, [], true],
            'OR all false' => [CompositeRule::OR, [new EasyRule(false), new EasyRule(false)], false],
            'OR last true' => [CompositeRule::OR, [new EasyRule(false), new EasyRule(true)], true],
        ];
    }

    /**
     * @dataProvider compositeRuleDataProvider
     */
    public function testCompositeRule(string $operator, array $rules, bool $expected): void
    {
        $rule = new CompositeRule('rule', $operator, $rules);
        $result = $rule->execute('user', new Permission('permission'), []);
        $this->assertSame($expected, $result);
    }

    public function testInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Operator could be either \Yiisoft\Rbac\CompositeRule::AND or \Yiisoft\Rbac\CompositeRule::OR, "no_such_operation" given.');
        new CompositeRule('rule', 'no_such_operation', []);
    }

    public function testInvalidRule(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each rule should be an instance of \Yiisoft\Rbac\Rule, "string" given.');
        new CompositeRule('rule', CompositeRule::OR, ['invalid_rule']);
    }
}