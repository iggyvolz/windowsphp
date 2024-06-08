<?php

namespace iggyvolz\windows;

use FFI;
use FFI\CData;

final class PaintStruct
{
    public function __construct(public readonly CData $cdata)
    {
    }

    public static function create(FFI $ffi): self
    {
        return new self($ffi->new("PAINTSTRUCT"));
    }

    public function addr(): CData
    {
        return FFI::addr($this->cdata);
    }

    public function getHDC(): CData
    {
        return $this->cdata->hdc;
    }

    public function getErase(): bool
    {
        return $this->cdata->fErase === 1;
    }

    public function getLeft(): int
    {
        return $this->cdata->rcPaint->left;
    }

    public function getRight(): int
    {
        return $this->cdata->rcPaint->right;
    }

    public function getTop(): int
    {
        return $this->cdata->rcPaint->top;
    }

    public function getBottom(): int
    {
        return $this->cdata->rcPaint->bottom;
    }
}