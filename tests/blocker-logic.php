<?php

declare(strict_types=1);

namespace pocketmine\world {
    class World {}
}

namespace pocketmine\player {
    class Player {
        public function getName(): string {
            return 'ExamplePlayer';
        }
    }
}

namespace NhanAZ\CommandBlocker\Tests {
    use InvalidArgumentException;
    use NhanAZ\CommandBlocker\blocker\command\CommandBlocker;
    use NhanAZ\CommandBlocker\blocker\command\Limit;
    use NhanAZ\CommandBlocker\blocker\WorldBlocker;
    use NhanAZ\CommandBlocker\blocker\Worlds;
    use pocketmine\player\Player;
    use pocketmine\world\World;
    use ReflectionClass;
    use ReflectionProperty;
    use RuntimeException;

    $source = dirname(__DIR__) . '/src/NhanAZ/CommandBlocker/';
    require $source . 'utils/LanguageTrait.php';
    require $source . 'blocker/command/Limit.php';
    require $source . 'blocker/command/CommandBlocker.php';
    require $source . 'blocker/WorldBlocker.php';
    require $source . 'blocker/Worlds.php';

    function check(bool $condition, string $message): void {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    $rule = (new ReflectionClass(CommandBlocker::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty(CommandBlocker::class, 'command'))->setValue($rule, 'help');
    (new ReflectionProperty(CommandBlocker::class, 'arguments'))->setValue($rule, ['2']);
    (new ReflectionProperty(CommandBlocker::class, 'permissions'))->setValue($rule, ['pocketmine.command.help']);
    (new ReflectionProperty(CommandBlocker::class, 'limit'))->setValue($rule, new Limit('warn', 3, 60));

    check($rule->isArgumentsBlocked(['2']), 'Configured argument should match');
    check(!$rule->isArgumentsBlocked([]), 'Missing argument should not match');
    check(!$rule->isArgumentsBlocked(['3']), 'Different argument should not match');

    $first = new WorldBlocker([$rule]);
    check($first->getCommandBlocker('help') === $rule, 'Direct command rule should be found');
    check($first->testPermissions(['pocketmine.command.help']) === $rule, 'Permission rule should be found');
    check($first->testPermissions([]) === null, 'Empty permissions must not match');

    $worldA = new World();
    $worldB = new World();
    $second = new WorldBlocker([]);
    Worlds::set($worldA, $first);
    Worlds::set($worldB, $second);
    check(Worlds::get($worldA) === $first && Worlds::get($worldB) === $second, 'World cache lost an entry');
    Worlds::remove($worldA);
    check(!Worlds::has($worldA) && Worlds::has($worldB), 'World cache removal affected another world');

    $rule->trigger(new Player());
    $rule->trigger(new Player());
    $state = (new ReflectionProperty(CommandBlocker::class, 'triggered'))->getValue($rule);
    check($state['exampleplayer']['VL'] === 2, 'Limit counter should include the first blocked attempt');

    foreach ([['action' => 'unknown', 'amount' => 1, 'interval' => 1], ['action' => 'warn', 'amount' => 0, 'interval' => 1]] as $invalid) {
        try {
            Limit::fromArray($invalid);
            throw new RuntimeException('Invalid limit was accepted');
        } catch (InvalidArgumentException) {
        }
    }
    echo "PASS: command arguments, permission match, world cache, limit count and validation\n";
}
