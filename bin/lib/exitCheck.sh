#!/usr/bin/env bash

# Checks the exit code of the previous command and bails if it's not zero
exitCheck() {
  if [ $1 != 0 ]; then
    echo "Got exit code $1, quitting."
    exit $1
  fi
}
