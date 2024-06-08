<?php

use iggyvolz\windows\Event\CharEvent;
use iggyvolz\windows\Event\LeftButtonDownEvent;
use iggyvolz\windows\Event\MiddleButtonDownEvent;
use iggyvolz\windows\Event\MouseMoveEvent;
use iggyvolz\windows\Event\PaintEvent;
use iggyvolz\windows\Event\RightButtonDownEvent;
use iggyvolz\windows\Event\XButtonDownEvent;
use iggyvolz\windows\PaintStruct;
use iggyvolz\windows\Window;
use iggyvolz\windows\WindowClassStyle;
use iggyvolz\windows\WindowStyle;
use Revolt\EventLoop;

require_once __DIR__ . "/vendor/autoload.php";

$window = Window::create("My Window", style: [
    ...WindowStyle::OverlappedWindow,
    WindowClassStyle::VRedraw,
    WindowClassStyle::HRedraw,
], dragAcceptFiles: true);
$window->addEventHandler(function (MouseMoveEvent $event): void {
    $x = $event->lParam & 0xffff;
    if ($x & 0x8000) $x |= 0xffff0000;
    $y = $event->lParam >> 16;
    echo "Current mouse position: ($x,$y)\n";
});
$window->addEventHandler(function (LeftButtonDownEvent $event): void {
    $x = $event->lParam & 0xffff;
    if ($x & 0x8000) $x |= 0xffff0000;
    $y = $event->lParam >> 16;
    echo "Bonk Left!  Current mouse position: ($x,$y)\n";
    $event->window->invalidate();
    $event->window->update();
});
$window->addEventHandler(function (RightButtonDownEvent $event): void {
    $x = $event->lParam & 0xffff;
    if ($x & 0x8000) $x |= 0xffff0000;
    $y = $event->lParam >> 16;
    echo "Bonk Right!  Current mouse position: ($x,$y)\n";
});
$window->addEventHandler(function (MiddleButtonDownEvent $event): void {
    $x = $event->lParam & 0xffff;
    if ($x & 0x8000) $x |= 0xffff0000;
    $y = $event->lParam >> 16;
    echo "Bonk Middle!  Current mouse position: ($x,$y)\n";
});
$window->addEventHandler(function (XButtonDownEvent $event): void {
    $x = $event->lParam & 0xffff;
    if ($x & 0x8000) $x |= 0xffff0000;
    $y = $event->lParam >> 16;
    $buttonNum = $event->wParam >> 16;
    echo "Bonk X$buttonNum!  Current mouse position: ($x,$y)\n";
});
$window->addEventHandler(function (CharEvent $event): void {
    $charCode = $event->wParam;
    if ($charCode === 27) {
        $event->window->close();
    }
    if ($charCode > 126 || $charCode < 32) {
        echo "[$charCode]\n";
    } else {
        echo chr($charCode) . "\n";
    }
});
$window->addEventHandler(function (PaintEvent $paintEvent): void {
    $paintEvent->window->paint(function(PaintStruct $ps): void {
        echo "I paint!\n";
        $top = $ps->getTop();
        $left = $ps->getLeft();
        $right = $ps->getRight();
        $bottom = $ps->getBottom();
        echo "My corners are ($top,$left), ($top,$right), ($bottom,$right), and ($bottom,$left)\n";
    });
});

// Immediately redraw the window
$window->invalidate();
$window->update();
EventLoop::run();