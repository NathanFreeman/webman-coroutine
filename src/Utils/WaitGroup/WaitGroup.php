<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup;

use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\RegisterMethods;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\DefaultWaitGroup;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RevoltWaitGroup;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RippleWaitGroup;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\SwooleWaitGroup;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\SwowWaitGroup;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\WaitGroupInterface;

/**
 * @method bool add(int $delta = 1)
 * @method bool done()
 * @method int count()
 * @method void wait(int $timeout = -1)
 */
class WaitGroup
{
    use RegisterMethods;

    /**
     * @var WaitGroupInterface|null
     */
    protected ?WaitGroupInterface $_interface;

    /**
     * @var string[]
     */
    protected static array $_handlers = [
        Factory::WORKERMAN_SWOW     => SwowWaitGroup::class,
        Factory::WORKBUNNY_SWOW     => SwowWaitGroup::class,
        Factory::WORKERMAN_SWOOLE   => SwooleWaitGroup::class,
        Factory::WORKBUNNY_SWOOLE   => SwooleWaitGroup::class,
        Factory::REVOLT_FIBER       => RevoltWaitGroup::class,
        Factory::RIPPLE_FIBER       => RippleWaitGroup::class,
        Factory::RIPPLE_FIBER_5     => RippleWaitGroup::class
    ];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->_interface = new (self::$_handlers[Factory::getCurrentEventLoop()] ?? DefaultWaitGroup::class)();
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        $this->_interface = null;
    }

    /** @inheritdoc  */
    public static function registerVerify(mixed $value): false|string
    {
        return is_a($value, WaitGroupInterface::class) ? WaitGroupInterface::class : false;
    }

    /** @inheritdoc  */
    public static function unregisterExecute(string $key): bool
    {
        return true;
    }

    /**
     * 代理调用WaitGroupInterface方法
     *
     * @codeCoverageIgnore 系统魔术方法，忽略覆盖
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->_interface, $name)) {
            throw new \BadMethodCallException("Method $name not exists. ");
        }

        return $this->_interface->$name(...$arguments);
    }
}
