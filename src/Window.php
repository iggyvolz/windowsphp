<?php
declare(strict_types=1);


namespace iggyvolz\windows;

use Closure;
use FFI\CData;
use iggyvolz\windows\Event\DestroyEvent;
use iggyvolz\windows\Event\QuitEvent;
use Revolt\EventLoop;
use RuntimeException;

class Window
{
    public readonly int $hwnd;
    public const int USE_DEFAULT = 0x80000000;

    /**
     * @param list<WindowStyle|WindowStyleEx> $style
     */
    public function __construct(
        array                       $style,
        public readonly WindowClass $windowClass,
        string                      $windowName,
        ?int                        $x = null,
        ?int                        $y = null,
        ?int                        $width = null,
        ?int                        $height = null,
    )
    {
        $exStyle = array_values(array_filter($style, fn($x) => $x instanceof WindowStyleEx));
        $style = array_values(array_filter($style, fn($x) => $x instanceof WindowStyle));
        $this->hwnd = WindowsFfi::get()->user32->CreateWindowExA(
            WindowStyleEx::toInt(...$exStyle),
            $this->windowClass->name,
            $windowName,
            WindowStyle::toInt(...$style),
            $x ?? self::USE_DEFAULT,
            $y ?? self::USE_DEFAULT,
            $width ?? self::USE_DEFAULT,
            $height ?? self::USE_DEFAULT,
            null,
            null,
            null,
            null
        );
        self::$instances[$this->hwnd] = \WeakReference::create($this);
        $this->addEventHandler(static function (DestroyEvent $event): void {
            $event->window->closed = true;
        });
        WindowsFfi::get()->user32->ShowWindow($this->hwnd, 1);
        WindowsFfi::get()->user32->UpdateWindow($this->hwnd);
    }

    /** @var array<int,\WeakReference<self>> */
    private static $instances = [];

    public static function get(int $ptr): ?self
    {
        return (self::$instances[$ptr] ?? null)?->get();
    }


    /**
     * @param list<WindowStyle|WindowStyleEx|WindowClassStyle> $style
     */
    public static function create(
        string $windowName,
        array  $style = WindowStyle::OverlappedWindow,
        ?int   $x = null,
        ?int   $y = null,
        ?int   $width = null,
        ?int   $height = null,
        bool   $dragAcceptFiles = false,
    ): self
    {
        $classStyle = array_values(array_filter($style, fn($x) => $x instanceof WindowClassStyle));
        $style = array_values(array_filter($style, fn($x) => !$x instanceof WindowClassStyle));
        $self = (new WindowClass($classStyle))->create($windowName, $style, $x, $y, $width, $height);
        $self->register();
        if ($dragAcceptFiles) $self->dragAcceptFiles();
        return $self;
    }

    public function handleMessages(): void
    {
        $msg = WindowsFfi::get()->user32->new("MSG");
        $msgAddr = \FFI::addr($msg);
        while (!$this->closed && (WindowsFfi::get()->user32->PeekMessageA($msgAddr, null, 0, 0, 1)) > 0) {
            WindowsFfi::get()->user32->TranslateMessage($msgAddr);
            WindowsFfi::get()->user32->DispatchMessageA($msgAddr);
        }
    }

    /** @var array<int,Closure(Event):?int> */
    private array $handlers = [];

    /**
     * @param Closure $handler
     * @param int|class-string<Event>|null $event
     * @return void
     */
    public function addEventHandler(Closure $handler, null|int|string $event = null): void
    {
        if (is_null($event)) {
            // TODO better error handling here
            $event = (new \ReflectionFunction($handler))->getParameters()[0]->getType()->getName();
        }
        if (is_string($event)) {
            $eventName = $event;
            $event = array_search($eventName, Event::EVENTS);
            if ($event === false) {
                throw new RuntimeException("Unknown event $eventName");
            }
        }
        $this->handlers[$event] ??= [];
        array_unshift($this->handlers[$event], $handler);
    }

    /** @internal */
    public function handleEvent(int $uMsg, int $wParam, int $lParam): int
    {
        $eventClass = Event::EVENTS[$uMsg] ?? Event::class;
        $event = new $eventClass($this, $uMsg, $wParam, $lParam);
        foreach ($this->handlers[$uMsg] ?? [] as $handler) {
            if (!is_null($result = $handler($event))) {
                return $result;
            }
        }
        return WindowsFfi::get()->user32->DefWindowProcA($this->hwnd, $uMsg, $wParam, $lParam);
    }

    private bool $closed = false;

    public function close(): void
    {
        WindowsFfi::get()->user32->DestroyWindow($this->hwnd);
    }

    public function dragAcceptFiles(bool $accept = true): void
    {
        WindowsFfi::get()->shell32->DragAcceptFiles($this->hwnd, $accept ? 1 : 0);
    }

    public function register(): void
    {
        EventLoop::repeat(0, function (string $callbackId) {
            if ($this->closed) {
                EventLoop::unreference($callbackId);
            } else {
                $this->handleMessages();
            }
        });
    }

    public function update(): void
    {
        WindowsFfi::get()->user32->UpdateWindow($this->hwnd);
    }

    public function invalidate(bool $erase = true): void
    {
        WindowsFfi::get()->user32->InvalidateRect($this->hwnd, null, $erase);
    }

    public function beginPaint(): PaintStruct
    {
        $paintStruct = PaintStruct::create(WindowsFfi::get()->user32);
        WindowsFfi::get()->user32->BeginPaint($this->hwnd, $paintStruct->addr());
        return $paintStruct;
    }
    public function endPaint(PaintStruct $paintStruct): void
    {
        WindowsFfi::get()->user32->EndPaint($this->hwnd, $paintStruct->addr());
    }

    /**
     * @param Closure(PaintStruct $ps):void $closure
     * @return void
     */
    public function paint(Closure $closure): void
    {
        $closure($ps = $this->beginPaint());
        $this->endPaint($ps);
    }
}