#!/bin/bash

# Prevent re-initializing if we've already run in this shell
if [ "${FORM_MONITOR_ENV_INITIALIZED:-0}" = "1" ]; then
    return 0 2>/dev/null || exit 0
fi

LIB_PATHS=""

add_lib_path() {
    local lib_name="$1"
    local lib_path
    lib_path=$(find /nix/store -name "$lib_name" -type f 2>/dev/null | head -1 | xargs dirname 2>/dev/null || true)

    if [ -n "$lib_path" ]; then
        case ":$LIB_PATHS:" in
            *":$lib_path:"*) ;;
            *) LIB_PATHS="$LIB_PATHS:$lib_path" ;;
        esac
    fi
}

LIBRARIES=(
    "libglib-2.0.so*"
    "libnss3.so*"
    "libatk-1.0.so*"
    "libatspi.so*"
    "libdrm.so*"
    "libXcomposite.so*"
    "libXdamage.so*"
    "libXrandr.so*"
    "libGL.so*"
    "libXss.so*"
    "libasound.so*"
    "libatk-bridge-2.0.so*"
    "libatk-bridge.so*"
    "libcairo.so*"
    "libpango*.so*"
    "libgdk_pixbuf*.so*"
    "libgtk-3.so*"
    "libxkbcommon.so*"
)

for lib in "${LIBRARIES[@]}"; do
    add_lib_path "$lib"
done

if [ -n "$LIB_PATHS" ]; then
    LIB_PATHS="${LIB_PATHS#:}"
    if [ -n "${LD_LIBRARY_PATH:-}" ]; then
        export LD_LIBRARY_PATH="$LIB_PATHS:$LD_LIBRARY_PATH"
    else
        export LD_LIBRARY_PATH="$LIB_PATHS"
    fi
fi

for candidate in chromium chromium-browser google-chrome; do
    if command -v "$candidate" >/dev/null 2>&1; then
        export PUPPETEER_EXECUTABLE_PATH="$(command -v "$candidate")"
        export PUPPETEER_PRODUCT="${PUPPETEER_PRODUCT:-chrome}"
        break
    fi
done

export PUPPETEER_SKIP_DOWNLOAD="${PUPPETEER_SKIP_DOWNLOAD:-true}"
export FORM_MONITOR_ENV_INITIALIZED=1

return 0 2>/dev/null || true

