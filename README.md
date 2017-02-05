# PHPUnit Broom Wagon

A PHPUnit test listener that reports on slow-running tests. Forked from [johnkary/phpunit-speedtrap](https://github.com/johnkary/phpunit-speedtrap) to allow more control over slow thresholds and support for PHPUnit 5.

### Broom Wagon?

The broom wagon is the name given to the vehicle that follows a cycling road race "sweeping" up stragglers who are unable to make it to the finish of the race within the time permitted. As a cyclist it makes sense to me.

![Broom wagon](http://i.imgur.com/9KvpKdo.jpg)

## Installation

Install via Composer

```bash
composer require --dev keystone/phpunit-broom-wagon
```

## Usage

Enable the listener by adding it to your `phpunit.xml` configuration file.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <listeners>
        <listener class="Keystone\PHPUnit\BroomWagon\TestListener" />
    </listeners>
</phpunit>
```

Now run your test suite as normal. If tests take longer than the slow threshold (500ms by default), then
they will be reported on in the console after the suite completes.

## Configuration

Within the configuration a number of arguments can optionally be passed to the test listener.

```xml
<phpunit bootstrap="vendor/autoload.php">
    <listeners>
        <listener class="Keystone\PHPUnit\BroomWagon\TestListener">
            <arguments>
                <!-- Suite threshold -->
                <integer>100</integer>
                <!-- Group thresholds -->
                <array>
                    <element key="database">
                        <integer>1000</integer>
                    </element>
                    <element key="browser">
                        <integer>5000</integer>
                    </element>
                </array>
                <!-- Report length -->
                <integer>10</integer>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

*Suite threshold*: The first argument is the overall suite threshold (default 500ms). This is the number of milliseconds a test can take to execute before it is deemed as slow.

*Group thresholds*: The second argument is an array of group thresholds. Each test `@group` annotation can have a different threshold. A use case for this is to group all tests that hit the database and be a little more relaxed with the slow threshold.

*Report length*: The third argument is the number of slow tests to display in the PHPUnit output (default 10).

## Annotations

```php
/**
 * @slowThreshold 2000
 */
class SomeTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @slowThreshold 5000
     */
    public function testLongRunningProcess()
    {
    }
}
```

## Credits

- [Tom Graham](https://github.com/tompedals)
- [John Kary (author of PHPUnit Speed Trap)](https://github.com/johnkary)

## License

Released under the MIT Licence. See the bundled LICENSE file for details.
