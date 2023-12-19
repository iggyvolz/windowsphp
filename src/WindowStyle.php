<?php
declare(strict_types=1);


namespace iggyvolz\windows;

enum WindowStyle: int
{
    use BitmapEnum;

    case Border = 0x00800000;
    case Caption = 0x00C00000;
    case Child = 0x40000000;
    const WindowStyle ChildWindow = self::Child;
    case ClipChildren = 0x02000000;
    case ClipSiblings = 0x04000000;
    case Disabled = 0x08000000;
    case DialogueFrame = 0x00400000;
    case HScroll = 0x00100000;
    const WindowStyle Iconic = self::Minimize;
    case Maximize = 0x01000000;
    case MaximizeBox = 0x00010000;
    case Minimize = 0x20000000;
    case MinimizeBox = 0x00020000;
    case Overlapped = 0x00000000;
    const array OverlappedWindow = [
        self::Overlapped,
        self::Caption,
        self::SysMenu,
        self::ThickFrame,
        self::MinimizeBox,
        self::MaximizeBox,
    ];
    case Popup = 0x80000000;
    const array PopupWindow = [
        self::Popup,
        self::Border,
        self::SysMenu,
    ];
    case SysMenu = 0x00080000;
    case ThickFrame = 0x00040000;
    const WindowStyle Tiled = self::Overlapped;
    case Visible = 0x10000000;
    case VScroll = 0x00200000;
}