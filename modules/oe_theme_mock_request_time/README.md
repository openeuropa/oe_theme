# OpenEuropa theme request time mock

This modules allows to set the current request time, allowing to test time-based cache invalidation scenarios.

## Usage

In order to set, get or clear mock request time while developing use the following Drush commands.

To set a mock request time run (date format must be 'Y-m-d H:i:s'):

```
drush mrt:set '2020-12-15 12:00:00' 
```

To get the current mock request time run:

```
drush mrt:get 
```

To clear the current mock request time run:

```
drush mrt:clear 
```

In your tests (such as Behat contexts) you can use the `oe_theme_mock_request_time.request_time_manager` service. 
