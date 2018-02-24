#!/bin/bash

STOP_REQUESTED=false
trap "STOP_REQUESTED=true" TERM INT SIGTERM

wait_signal() {
    while ! $STOP_REQUESTED; do
        sleep 1
    done
}

wait_signal
