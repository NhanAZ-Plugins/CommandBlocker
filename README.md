# CommandBlocker

Block selected player commands globally or in configured worlds on Axolotl-PM. Rules can match command aliases and specific argument lists. Optional limits can warn, kick or ban after repeated blocked attempts.

## Requirements and compatibility

The plugin declares Axolotl-PM API `5.3.0` because its bundled [Commando](https://github.com/JinodkDevTeam/Commando) virion declares that minimum. It uses PHP 8.1 or newer according to the server's requirements. The [build workflow](.github/workflows/ci.yml) pins the exact Axolotl-PM source used for PHPStan and PHAR validation. A successful check on one release does not establish compatibility with every release in the API 5 series.

## Installation

1. Download the verified PHAR from a successful [build workflow](.github/workflows/ci.yml) run or a matching GitHub release.
2. Put it in the server's `plugins` directory and start the server.
3. Edit `plugins/CommandBlocker/config.yml` and restart the server.

Commando is bundled in the PHAR, so no separate Commando plugin is required. The PHAR includes its LGPL license. See [third-party notices](THIRD_PARTY_NOTICES.md).

## Configuration and commands

Use `globals` for commands blocked in every world. Use `worlds` for world-specific rules. A rule may name a command directly, or provide an exact list of arguments and a limit. Malformed entries are logged and skipped. World names must match server world folder names.

```yaml
language: eng
globals: ["gamemode"]
worlds:
  world:
    teleport: []
    help:
      arguments: ["2"]
      limit:
        interval: 10
        amount: 4
        action: warn
```

`/commandblocker worlds` lists configured loaded worlds and their blocked commands. `/commandblocker list <world>` lists that world's blocked commands. Add a command name to show its arguments, aliases and limit. `/cb` is an alias. The permissions `commandblocker.command`, `commandblocker.command.worlds` and `commandblocker.command.list` all default to enabled for players.

The listener handles commands sent by players. It does not stop another plugin from executing a command through the console. Review privileged commands and proxy plugins separately.

## Building and testing

The [CI workflow](.github/workflows/ci.yml) runs PHP syntax checks, command matching tests and PHPStan at maximum level, then uses [DevTools](https://github.com/NhanAZ/DevTools) to build and inspect the PHAR. The workflow pins Commando and the server source for repeatable checks. For a local build after checking out submodules, run:

```shell
php -d phar.readonly=0 path/to/DevTools/bin/devtools.php build --project=. --json
```

## License and support

CommandBlocker is GPL-3.0 licensed. Commando is LGPL-3.0-or-later and credited in the [third-party notices](THIRD_PARTY_NOTICES.md). Report issues in this repository or contact [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
