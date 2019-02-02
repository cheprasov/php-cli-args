## CHANGELOG

### v3.0.0 (2019-02-02)
Major:
- Removed method `isFlagOrAliasExists`, please use isFlagExist
- Renamed method `isFlagExists` to `isFlagExist`, changed second parameter of the method.
- Will be thrown `CongifErrorException` if it is passed wrong config to constructor.
- Method `getArg($key)` returns `true` or `false` when filter is `flag`.

Patch:
- Fixed default default arguments of default params
