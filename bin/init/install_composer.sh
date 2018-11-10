#!/usr/bin/env bash

ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../.." && pwd )"

. "$ROOT"/bin/lib/exitCheck.sh

EXPECTED_VERSION=1.7.3

# Check for existing installation
if [ -e "$ROOT"/bin/composer ]; then
    echo "Checking Composer version..."
    FOUND_VERSION=`( \
        cd "$ROOT" && \
        ./bin/composer --version | \
        tail -1 | \
        awk '{print $3}' | \
        "$ROOT"/bin/linux-sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[mGK]//g" \
    )`

    # Check version of installed composer
    if [ "$FOUND_VERSION" == "$EXPECTED_VERSION" ]; then
        # Version is as expected. We're done.
        echo "Composer ${EXPECTED_VERSION} already installed."
        exit 0
    else
        # Version does not match. Remove it.
        echo "Removing Composer version ${FOUND_VERSION}..."
        rm "$ROOT"/bin/composer
        exitCheck $?
    fi
fi

# Install composer
echo "Installing Composer ${EXPECTED_VERSION}..."
curl -L https://getcomposer.org/download/${EXPECTED_VERSION}/composer.phar > "$ROOT"/bin/composer
exitCheck $?

# Fix permissions
echo "Setting composer permissions..."
chmod 755 "$ROOT"/bin/composer
exitCheck $?

# Ensure ~/.composer exists for the Docker container to mount
echo "Initializing ~/.composer directory..."
if [ ! -d ~/.composer ]; then
    mkdir ~/.composer
    exitCheck $?
fi

echo "Composer v${EXPECTED_VERSION} installation complete."
