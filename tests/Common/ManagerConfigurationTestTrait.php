<?php

declare(strict_types=1);

namespace Yiisoft\Rbac\Tests\Common;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\Tests\Support\AuthorRule;
use Yiisoft\Rbac\Tests\Support\FakeAssignmentsStorage;
use Yiisoft\Rbac\Tests\Support\FakeItemsStorage;

trait ManagerConfigurationTestTrait
{
    protected function createManager(
        ?ItemsStorageInterface $itemsStorage = null,
        ?AssignmentsStorageInterface $assignmentsStorage = null,
        ?bool $enableDirectPermissions = null,
        ?bool $includeRolesInAccessChecks = null,
        ?DateTimeImmutable $currentDateTime = null,
    ): ManagerInterface {
        $arguments = [
            'itemsStorage' => $itemsStorage ?? $this->createItemsStorage(),
            'assignmentsStorage' => $assignmentsStorage ?? $this->createAssignmentsStorage(),
            'clock' => $currentDateTime === null
                ? null
                : new class ($currentDateTime) implements ClockInterface {
                    public function __construct(private readonly DateTimeImmutable $dateTime)
                    {
                    }

                    public function now(): DateTimeImmutable
                    {
                        return $this->dateTime;
                    }
                },
        ];
        if ($enableDirectPermissions !== null) {
            $arguments['enableDirectPermissions'] = $enableDirectPermissions;
        }

        if ($includeRolesInAccessChecks !== null) {
            $arguments['includeRolesInAccessChecks'] = $includeRolesInAccessChecks;
        }

        return new Manager(...$arguments);
    }

    protected function createItemsStorage(): ItemsStorageInterface
    {
        return new FakeItemsStorage();
    }

    protected function createAssignmentsStorage(): AssignmentsStorageInterface
    {
        return new FakeAssignmentsStorage();
    }

    protected function createFilledManager(
        ?ItemsStorageInterface $itemsStorage = null,
        ?AssignmentsStorageInterface $assignmentsStorage = null,
        ?bool $includeRolesInAccessChecks = null,
    ): ManagerInterface {
        $arguments = [
            $itemsStorage ?? $this->createItemsStorage(),
            $assignmentsStorage ?? $this->createAssignmentsStorage(),
            true,
        ];
        if ($includeRolesInAccessChecks !== null) {
            $arguments[] = $includeRolesInAccessChecks;
        }

        return $this
            ->createManager(...$arguments)
            ->addPermission(new Permission('Fast Metabolism'))
            ->addPermission(new Permission('createPost'))
            ->addPermission(new Permission('publishPost'))
            ->addPermission(new Permission('readPost'))
            ->addPermission(new Permission('deletePost'))
            ->addPermission((new Permission('updatePost'))->withRuleName(AuthorRule::class))
            ->addPermission(new Permission('updateAnyPost'))
            ->addRole(new Role('reader'))
            ->addRole(new Role('author'))
            ->addRole(new Role('admin'))
            ->addRole(new Role('myDefaultRole'))
            ->setDefaultRoleNames(['myDefaultRole'])
            ->addChild('reader', 'readPost')
            ->addChild('author', 'createPost')
            ->addChild('author', 'updatePost')
            ->addChild('author', 'reader')
            ->addChild('admin', 'author')
            ->addChild('admin', 'updateAnyPost')
            ->assign(itemName: 'Fast Metabolism', userId: 'reader A')
            ->assign(itemName: 'reader', userId: 'reader A')
            ->assign(itemName: 'author', userId: 'author B')
            ->assign(itemName: 'deletePost', userId: 'author B')
            ->assign(itemName: 'publishPost', userId: 'author B')
            ->assign(itemName: 'admin', userId: 'admin C');
    }
}
