# Changelog

## 2.0.1

- Pin and bundle Commando 3.2.0 with its LGPL notice for DevTools builds.
- Fix world blocker caching, command matching, argument filtering and limit counts.
- Validate configuration entries before use and preserve valid blockers when another entry is malformed.
- Make `/commandblocker list <world>` work without a command argument and correct limit translations.
- Set the declared minimum API to 5.3.0 to match the bundled Commando virion.
- Add command matching tests, PHPStan at maximum level, PHAR validation and dependency update checks to CI.
