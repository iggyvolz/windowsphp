<?php
declare(strict_types=1);

namespace iggyvolz\windows;

use FFI;
use FFI\CData;
use iggyvolz\windows\Event\DestroyEvent;
use Random\Randomizer;
use Revolt\EventLoop;

final class WindowClass
{
    public readonly string $name;
    private readonly CData $nameC;

    /** @param list<WindowClassStyle> $style */
    public function __construct(
        public readonly array $style = [],
//        ?Icon   $icon = null,
//        ?Cursor $cursor = null,
//        ?Brush  $brush = null,
    )
    {
        $this->name = spl_object_hash($this);
        $class = WindowsFfi::get()->user32->new("WNDCLASSEXA");
        $class->cbSize = FFI::sizeof($class);
        $class->lpfnWndProc = static::windowProc(...);
        $class->style = WindowClassStyle::toInt(...$style);
        $this->nameC = WindowsFfi::get()->user32->new("char[" . strlen($this->name) + 1 . "]", owned: false);
        FFI::memcpy(FFI::addr($this->nameC), $this->name . "\0", strlen($this->name) + 1);
        $class->lpszClassName = $this->nameC;
        if (WindowsFfi::get()->user32->RegisterClassExA(FFI::addr($class)) === 0) {
            throw new \RuntimeException("Failed to register class");
        }
    }

    /**
     * @param list<WindowStyle|WindowStyleEx> $style
     */
    public function create(
        string $windowName,
        array  $style = WindowStyle::OverlappedWindow,
        ?int   $x = null,
        ?int   $y = null,
        ?int   $width = null,
        ?int   $height = null,
    ): Window
    {
        return new Window($style, $this, $windowName, $x, $y, $width, $height);
    }


    private function windowProc(int $hwnd, int $uMsg, int $wParam, int $lParam): int
    {
        $window = Window::get($hwnd);
        if (is_null($window)) {
            // The first couple events will happen before we assigned the pointer
            // Defer the event to next loop...
            EventLoop::defer(function () use ($hwnd, $uMsg, $wParam, $lParam) {
                $window = Window::get($hwnd);
                if (is_null($window)) {
                    throw new \RuntimeException("Failed to access window");
                }
                $window->handleEvent($uMsg, $wParam, $lParam);
            });
            // Handle it default
            return WindowsFfi::get()->user32->DefWindowProcA($hwnd, $uMsg, $wParam, $lParam);
        } else {
            return $window->handleEvent($uMsg, $wParam, $lParam);
        }
    }

    public function __destruct()
    {
        FFI::free($this->nameC);
        WindowsFfi::get()->user32->UnregisterClassA($this->name, null);
    }
}