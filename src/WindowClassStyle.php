<?php
declare(strict_types=1);


namespace iggyvolz\windows;

enum WindowClassStyle: int
{
    use BitmapEnum;

    case ByteAlignClient = 0x1000;
    case ByteAlignWindow = 0x2000;
    case ClassDc = 0x40;
    case DoubleClicks = 0x8;
    case DropShadow = 0x20000;
    case GlobalClass = 0x4000;
    case HRedraw = 0x2;
    case NoClose = 0x200;
    case OwnDc = 0x20;
    case ParentDc = 0x80;
    case SaveBits = 0x800;
    case VRedraw = 0x1;
}