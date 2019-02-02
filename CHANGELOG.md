## CHANGELOG

### v3.0.0 (2019-02-02)
Major:
- Removed method `isFlagOrAliasExists`, please use `isFlagExist`
- Renamed method `isFlagExists` to `isFlagExist`, and second parameter of the method is changed
- Will be thrown `CongifErrorException` if it is passed wrong config in constructor.
- Method `getArg($key)` returns  only `true` or `false` when filter is `flag`.

Patch:
- Fixed bug of using default empty values from config.
