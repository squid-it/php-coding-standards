<?php

declare(strict_types=1);

namespace SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support;

use ArrayObject;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SquidIT\PhpCodingStandards\PHPStan\Support\DenyList;
use SquidIT\PhpCodingStandards\PHPStan\Support\TypeCandidateResolver;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\CustomDomainDto;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\DomainChildInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\DomainRootInterface;
use SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\UserlandDomainException;
use Throwable;

final class TypeCandidateResolverTest extends TestCase
{
    private TypeCandidateResolver $typeCandidateResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDefaultReflectionProvider();
        $this->typeCandidateResolver = new TypeCandidateResolver();
    }

    /**
     * @throws Throwable
     */
    public function testResolveUnionIgnoresNullAndFalseAndExpandsHierarchySucceeds(): void
    {
        $unionType = new UnionType([
            new ObjectType(CustomDomainDto::class),
            new NullType(),
            new ConstantBooleanType(false),
        ]);

        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType($unionType);
        sort($candidateNameList);

        $expectedCandidateNameList = [
            'customDomain',
            'customDomainDto',
            'domainBase',
            'domainBaseEntity',
            'domainChild',
            'domainRoot',
        ];
        sort($expectedCandidateNameList);

        self::assertSame($expectedCandidateNameList, $candidateNameList);
    }

    /**
     * @throws Throwable
     */
    public function testResolveExcludesInternalAndKeepsUserlandHierarchyCandidatesSucceeds(): void
    {
        $unionType = new UnionType([
            new ObjectType(ArrayObject::class),
            new ObjectType(UserlandDomainException::class),
        ]);

        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType($unionType);
        sort($candidateNameList);

        self::assertSame(['userlandDomainException'], $candidateNameList);
    }

    /**
     * @throws Throwable
     */
    public function testResolveWithClassDenyListSkipsDeniedClassSucceeds(): void
    {
        $denyList              = new DenyList([CustomDomainDto::class]);
        $typeCandidateResolver = new TypeCandidateResolver(denyList: $denyList);

        $candidateNameList = $typeCandidateResolver->resolvePHPStanType(new ObjectType(CustomDomainDto::class));

        self::assertSame([], $candidateNameList);
    }

    /**
     * @throws Throwable
     */
    public function testResolveDirectInterfaceTypeExpandsParentInterfacesSucceeds(): void
    {
        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType(new ObjectType(DomainChildInterface::class));
        sort($candidateNameList);

        self::assertSame(
            [
                'domainChild',
                'domainRoot',
            ],
            $candidateNameList,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveWithCandidateDenyListSkipsDeniedCandidateNamesSucceeds(): void
    {
        $denyList = new DenyList(
            candidateNameList: [
                'customDomainDto',
                'customDomain',
            ],
        );
        $typeCandidateResolver = new TypeCandidateResolver(denyList: $denyList);

        $candidateNameList = $typeCandidateResolver->resolvePHPStanType(new ObjectType(CustomDomainDto::class));
        sort($candidateNameList);

        self::assertSame(
            [
                'domainBase',
                'domainBaseEntity',
                'domainChild',
                'domainRoot',
            ],
            $candidateNameList,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapSkipsDenyListedInterfacesSucceeds(): void
    {
        $denyList = new DenyList(
            classNameList: [
                DomainChildInterface::class,
            ],
        );
        $typeCandidateResolver = new TypeCandidateResolver(denyList: $denyList);

        $interfaceBaseNameToTypeMap = $typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new UnionType([
                new ObjectType(DomainChildInterface::class),
                new ObjectType(DomainRootInterface::class),
            ]),
        );

        self::assertArrayNotHasKey('domainChild', $interfaceBaseNameToTypeMap);
        self::assertSame('DomainRootInterface', $interfaceBaseNameToTypeMap['domainRoot'] ?? null);
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapSkipsBuiltinInterfacesSucceeds(): void
    {
        $interfaceBaseNameToTypeMap = $this->typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new UnionType([
                new ObjectType(\ArrayAccess::class),
                new ObjectType(DomainRootInterface::class),
            ]),
        );

        self::assertArrayNotHasKey('arrayAccess', $interfaceBaseNameToTypeMap);
        self::assertSame('DomainRootInterface', $interfaceBaseNameToTypeMap['domainRoot'] ?? null);
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapSkipsDenyListedCandidateNameSucceeds(): void
    {
        $typeCandidateResolver = new TypeCandidateResolver(
            denyList: new DenyList(candidateNameList: ['domainRoot']),
        );

        $interfaceBaseNameToTypeMap = $typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new ObjectType(DomainRootInterface::class),
        );

        self::assertSame([], $interfaceBaseNameToTypeMap);
    }

    /**
     * @throws Throwable
     */
    public function testResolveNativeFallbackExpandsUserlandHierarchySucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType(new ObjectType(CustomDomainDto::class));
        sort($candidateNameList);

        self::assertSame(
            [
                'customDomain',
                'customDomainDto',
                'domainBase',
                'domainBaseEntity',
                'domainChild',
                'domainRoot',
            ],
            $candidateNameList,
        );
    }

    /**
     * @throws Throwable
     */
    public function testResolveNativeFallbackSkipsInternalClassesSucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType(new ObjectType(ArrayObject::class));

        self::assertSame([], $candidateNameList);
    }

    /**
     * @throws Throwable
     */
    public function testResolveNativeFallbackSkipsInternalParentsAndInterfacesSucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $candidateNameList = $this->typeCandidateResolver->resolvePHPStanType(new ObjectType(UserlandDomainException::class));

        self::assertSame(['userlandDomainException'], $candidateNameList);
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapNativeFallbackSkipsUnknownClassesSucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $interfaceBaseNameToTypeMap = $this->typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new ObjectType('SquidIT\Tests\PhpCodingStandards\Unit\PHPStan\Support\Fixtures\TypeCandidateResolver\UnknownInterface'),
        );

        self::assertSame([], $interfaceBaseNameToTypeMap);
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapNativeFallbackSkipsConcreteTypesSucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $interfaceBaseNameToTypeMap = $this->typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new ObjectType(CustomDomainDto::class),
        );

        self::assertSame([], $interfaceBaseNameToTypeMap);
    }

    /**
     * @throws Throwable
     */
    public function testResolveInterfaceBaseNameMapNativeFallbackSupportsGlobalInterfacesSucceeds(): void
    {
        $this->registerNativeFallbackReflectionProvider();
        $this->defineGlobalTypeCandidateResolverInterface();

        $interfaceBaseNameToTypeMap = $this->typeCandidateResolver->resolveInterfaceBaseNameToTypeMap(
            new ObjectType('TypeCandidateResolverGlobalInterface'),
        );

        self::assertSame(
            ['typeCandidateResolverGlobal' => 'TypeCandidateResolverGlobalInterface'],
            $interfaceBaseNameToTypeMap,
        );
    }

    private function registerDefaultReflectionProvider(): void
    {
        // @phpstan-ignore-next-line phpstanApi.method
        ReflectionProviderStaticAccessor::registerInstance(PHPStanTestCase::createReflectionProvider());
    }

    private function registerNativeFallbackReflectionProvider(): void
    {
        /** @var ReflectionProvider&Stub $reflectionProvider */
        $reflectionProvider = self::createStub(ReflectionProvider::class);
        $reflectionProvider->method('hasClass')->willReturn(false);

        // @phpstan-ignore-next-line phpstanApi.method
        ReflectionProviderStaticAccessor::registerInstance($reflectionProvider);
    }

    private function defineGlobalTypeCandidateResolverInterface(): void
    {
        if (interface_exists('TypeCandidateResolverGlobalInterface') === false) {
            eval('interface TypeCandidateResolverGlobalInterface {}');
        }
    }
}
