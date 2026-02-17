<?php

declare(strict_types=1);

namespace SquidIT\PhpCodingStandards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function sprintf;

/**
 * @implements Rule<FileNode>
 */
final class SingleClassPerFileRule implements Rule
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        /** @var FileNode $node */
        $declarations     = $this->findClassLikeDeclarations($node->getNodes());
        $declarationCount = count($declarations);

        if ($declarationCount <= 1) {
            return [];
        }

        $first  = $declarations[0];
        $errors = [];

        for ($i = 1; $i < $declarationCount; $i++) {
            $offending = $declarations[$i];

            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'File must contain a single class-like declaration. Found %s "%s" and %s "%s"',
                    $this->getClassLikeTypeName($first),
                    (string) $first->name,
                    $this->getClassLikeTypeName($offending),
                    (string) $offending->name,
                ),
            )
                ->identifier('squidit.singleClassPerFile')
                ->line($offending->getStartLine())
                ->build();
        }

        return $errors;
    }

    /**
     * @param array<int, Node> $stmts
     *
     * @return array<int, ClassLike>
     */
    private function findClassLikeDeclarations(array $stmts): array
    {
        $declarations = [];

        foreach ($stmts as $stmt) {
            if ($stmt instanceof ClassLike && $stmt->name !== null) {
                $declarations[] = $stmt;
            }

            if ($stmt instanceof Namespace_) {
                foreach ($stmt->stmts as $namespaceStmt) {
                    if ($namespaceStmt instanceof ClassLike && $namespaceStmt->name !== null) {
                        $declarations[] = $namespaceStmt;
                    }
                }
            }
        }

        return $declarations;
    }

    private function getClassLikeTypeName(ClassLike $node): string
    {
        return match (true) {
            $node instanceof Class_     => 'Class',
            $node instanceof Interface_ => 'Interface',
            $node instanceof Trait_     => 'Trait',
            $node instanceof Enum_      => 'Enum',
            default                     => 'Unknown',
        };
    }
}
