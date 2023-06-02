# Working with tests

When developing run all the tests once with `phpunit` if you are just getting started.
This will setup the test database for you in mongodb, as there is a test listener class which runs the migrations
for a new database named `jikan-test`. (by default, feel free to override it if required)

> Please note that the test database should not be the same as the development database you have already indexed data into, as
> the tests will drop all collections in mongodb at the end of the test run.

When writing tests you should not use `DatabaseMigration` and `DatabaseTransaction` traits.  
- If the search index requires resetting between tests, use `ScoutFlush` trait.   
- If the database contents requires resetting between tests, use `SyntheticMongoDbTransaction` trait.

> `ScoutFlush`: Runs the `scout:flush` artisan command.

> `SyntheticMongoDbTransaction`: empties the mongodb database between tests,
> by gathering all app models, and dropping all items in them.


## Rationale
Jikan API uses mongodb. We think that the laravel ecosystem is not very well tailored for mongodb in terms of
unit tests, or at least that's our experience so far. If you try to use `DatabaseMigration` trait in tests,
you would run the migration for each test, which takes around 5-6 seconds on a somewhat powerful machine.
The next sensible choice would be `DatabaseTransaction` trait, however that would require the contributors to
set up a mongodb replicaset locally ([1](https://github.com/jenssegers/laravel-mongodb#transactions))([2](https://www.mongodb.com/docs/manual/core/transactions/)).
The middle ground is to run migrations once during the test lifecycle, and during local development it is enough to run the migrations once.
This significantly reduces the time required to run the tests.
