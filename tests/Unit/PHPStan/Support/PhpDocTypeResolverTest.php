<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\PhpDocTypeResolver;
use Throwable;

final class PhpDocTypeResolverTest extends TestCase
{
    private PhpDocTypeResolver $phpDocTypeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpDocTypeResolver = new PhpDocTypeResolver();
    }

    /**
     * @throws Throwable
     */
    public function testResolveNamedTagObjectTypeWithNullDocCommentReturnsNullSucceeds(): void
    {
        $resolvedType = $this->phpDocTypeResolver->resolveNamedTagObjectType(
            docCommentText: null,
            tagName: 'param',
            variableName: 'value',
        );

        self::assertNull($resolvedType);
    }

    /**
     * @throws Throwable
     */
    public function testResolveNamedTagObjectTypeWithComplexUnionResolvesObjectTypesSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @param ?\App\Dto\UserDto[]|int||array<string,int>|\App\Entity\OrderEntity $value
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveNamedTagObjectType(
            docCommentText: $docCommentText,
            tagName: 'param',
            variableName: 'value',
        );
        $this->assertResolvedObjectClassNameList(
            expectedObjectClassNameList: [
                'App\Dto\UserDto',
                'App\Entity\OrderEntity',
            ],
            resolvedType: $resolvedType,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveNamedTagObjectTypeWithMissingTypeReturnsNullSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @param $value
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveNamedTagObjectType(
            docCommentText: $docCommentText,
            tagName: 'param',
            variableName: 'value',
        );

        self::assertNull($resolvedType);
    }

    /**
     * @throws Throwable
     */
    public function testResolveNamedTagObjectTypeWithNoMatchingTagOrVariableReturnsNullSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @return \App\Dto\UserDto
 * @param \App\Dto\UserDto $other
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveNamedTagObjectType(
            docCommentText: $docCommentText,
            tagName: 'param',
            variableName: 'value',
        );

        self::assertNull($resolvedType);
    }

    /**
     * @throws Throwable
     */
    public function testResolveVarTagObjectTypeWithUnnamedVarDisallowedReturnsNullSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @var \App\Dto\UserDto
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveVarTagObjectType(
            docCommentText: $docCommentText,
            variableName: 'userDto',
            allowUnnamedVarTag: false,
        );

        self::assertNull($resolvedType);
    }

    /**
     * @throws Throwable
     */
    public function testResolveVarTagObjectTypeWithUnnamedVarAllowedResolvesObjectTypeSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @var \App\Dto\UserDto
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveVarTagObjectType(
            docCommentText: $docCommentText,
            variableName: 'userDto',
            allowUnnamedVarTag: true,
        );

        $this->assertResolvedObjectClassNameList(
            expectedObjectClassNameList: ['App\Dto\UserDto'],
            resolvedType: $resolvedType,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveVarTagObjectTypeWithInvalidUnnamedVarTagReturnsNullSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @var \App\Dto\UserDto $userDto
 * @var
 */
DOC;
        $resolvedType   = $this->phpDocTypeResolver->resolveVarTagObjectType(
            docCommentText: $docCommentText,
            variableName: 'value',
            allowUnnamedVarTag: true,
        );

        self::assertNull($resolvedType);
    }

    /**
     * @throws Throwable
     */
    public function testResolveVarTagIterableValueClassNameListWithComplexTypeResolvesUniqueClassesSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @var array<string, \App\Model\User>|Collection<int, \App\Model\User>|list<\App\Model\Role>|?\App\Model\Tag[]|int[]|()|Broken<,>|(\App\Model\Role[]&\App\Model\Role)|array{meta:list<\App\Model\Meta>} $items
 */
DOC;
        $classNameList  = $this->phpDocTypeResolver->resolveVarTagIterableValueClassNameList(
            docCommentText: $docCommentText,
            variableName: 'items',
            allowUnnamedVarTag: false,
        );
        sort($classNameList);

        self::assertSame(
            [
                'App\Model\Role',
                'App\Model\Tag',
                'App\Model\User',
            ],
            $classNameList,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveVarTagIterableValueClassNameListWithUnnamedVarTagResolvesClassSucceeds(): void
    {
        $docCommentText = <<<'DOC'
/**
 * @var list<\App\Projection\EntryDto>
 */
DOC;
        $classNameList  = $this->phpDocTypeResolver->resolveVarTagIterableValueClassNameList(
            docCommentText: $docCommentText,
            variableName: 'items',
            allowUnnamedVarTag: true,
        );

        self::assertSame(['App\Projection\EntryDto'], $classNameList);
    }

    /**
     * @param array<int, string> $expectedObjectClassNameList
     */
    private function assertResolvedObjectClassNameList(array $expectedObjectClassNameList, ?Type $resolvedType): void
    {
        if ($resolvedType === null) {
            self::fail('Expected object type, got null.');
        }

        $objectClassNameList = $resolvedType->getObjectClassNames();
        sort($objectClassNameList);
        sort($expectedObjectClassNameList);

        self::assertSame($expectedObjectClassNameList, $objectClassNameList);
    }
}
