<?php
declare(strict_types=1);


namespace iggyvolz\windows;

use FFI;

final class WindowsFfi
{
    public readonly FFI $user32;
    public readonly FFI $shell32;
    public readonly FFI $kernel32;

    private function __construct()
    {
        $this->user32 = FFI::cdef(<<<CDEF
        typedef unsigned int UINT;
        typedef int64_t LONG_PTR;
        typedef LONG_PTR LRESULT;
        typedef void *PVOID;
        typedef LONG_PTR HANDLE;
        typedef HANDLE HWND;
        typedef HANDLE HDC;
        typedef uint64_t UINT_PTR;
        typedef UINT_PTR WPARAM;
        typedef HANDLE HINSTANCE;
        typedef LONG_PTR LPARAM;
        typedef HANDLE HICON;
        typedef HICON HCURSOR;
        typedef HANDLE HBRUSH;
        typedef const char *LPCSTR;
        typedef LRESULT (*WNDPROC)(HWND,UINT,WPARAM,LPARAM);
        typedef int BOOL;
        typedef int WINBOOL;
        typedef unsigned short WORD;
        typedef WORD ATOM;
        typedef struct tagWNDCLASSEXA {
          UINT      cbSize;
          UINT      style;
          WNDPROC   lpfnWndProc;
          int       cbClsExtra;
          int       cbWndExtra;
          HINSTANCE hInstance;
          HICON     hIcon;
          HCURSOR   hCursor;
          HBRUSH    hbrBackground;
          LPCSTR    lpszMenuName;
          LPCSTR    lpszClassName;
          HICON     hIconSm;
        } WNDCLASSEXA, *PWNDCLASSEXA, *NPWNDCLASSEXA, *LPWNDCLASSEXA;
        ATOM RegisterClassExA(
          const WNDCLASSEXA *unnamedParam1
        );
        BOOL UnregisterClassA(
          LPCSTR    lpClassName,
          HINSTANCE hInstance
        );
        typedef unsigned long DWORD;
        typedef HANDLE HMENU;
        typedef void *LPVOID;
        HWND CreateWindowExA(
          DWORD     dwExStyle,
          LPCSTR    lpClassName,
          LPCSTR    lpWindowName,
          DWORD     dwStyle,
          int       X,
          int       Y,
          int       nWidth,
          int       nHeight,
          HWND      hWndParent,
          HMENU     hMenu,
          HINSTANCE hInstance,
          LPVOID    lpParam
        );
        LRESULT DefWindowProcA(
          HWND   hWnd,
          UINT   Msg,
          WPARAM wParam,
          LPARAM lParam
        );
        BOOL ShowWindow(
          HWND hWnd,
          int  nCmdShow
        );
        BOOL UpdateWindow(
          HWND hWnd
        );
        typedef long LONG;
        typedef struct tagPOINT {
          LONG x;
          LONG y;
        } POINT, *PPOINT, *NPPOINT, *LPPOINT;
        typedef struct tagMSG {
          HWND   hwnd;
          UINT   message;
          WPARAM wParam;
          LPARAM lParam;
          DWORD  time;
          POINT  pt;
          DWORD  lPrivate;
        } MSG, *PMSG, *NPMSG, *LPMSG;
        BOOL GetMessageA(
          LPMSG lpMsg,
          HWND  hWnd,
          UINT  wMsgFilterMin,
          UINT  wMsgFilterMax
        );
        BOOL PeekMessageA(
          LPMSG lpMsg,
          HWND  hWnd,
          UINT  wMsgFilterMin,
          UINT  wMsgFilterMax,
          UINT  wRemoveMsg
        );
        BOOL TranslateMessage(
          const MSG *lpMsg
        );
        LRESULT DispatchMessageA(
          const MSG *lpMsg
        );
        BOOL DestroyWindow(
          HWND hWnd
        );
        typedef struct tagRECT {
         LONG left;
         LONG top;
         LONG right;
         LONG bottom;
        } RECT, *PRECT, *NPRECT, *LPRECT;
        typedef struct {
          HDC hdc;
          WINBOOL fErase;
          RECT rcPaint;
          WINBOOL fRestore;
          WINBOOL fIncUpdate;
          unsigned char rgbReserved[32];
        } PAINTSTRUCT,*PPAINTSTRUCT,*NPPAINTSTRUCT,*LPPAINTSTRUCT;
        HDC BeginPaint(HWND hWnd,LPPAINTSTRUCT lpPaint);
        WINBOOL EndPaint(HWND hWnd,const PAINTSTRUCT *lpPaint);
        BOOL InvalidateRect(
          HWND       hWnd,
          const RECT *lpRect,
          BOOL       bErase
        );
        CDEF, "User32.dll");
        $this->shell32 = FFI::cdef(<<<CDEF
        typedef int64_t LONG_PTR;
        typedef LONG_PTR HANDLE;
        typedef int BOOL;
        typedef HANDLE HWND;
        void DragAcceptFiles(
          HWND hWnd,
          BOOL fAccept
        );
        CDEF, "Shell32.dll");
        $this->kernel32 = FFI::cdef(<<<CDEF
        typedef unsigned long DWORD;
        DWORD GetLastError();
        CDEF, "Kernel32.dll");
    }


    private static ?WindowsFfi $self = null;

    public static function get(): self
    {
        return self::$self ??= new self();
    }
}