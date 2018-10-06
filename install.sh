#! /bin/bash

# Print header
function header {
    echo "Todo List Installer"
}

# Check php version
function php_check {
    echo -e 'Check php is installed...\c'

    if type php > /dev/null; then
        pass
    else
         failed
         exit 0
    fi
}

# Run composer
function run_composer {
    composer install --no-progress
}

# Run the app
function run_app {
    php crawler.php
}

# Check is there any error
function check {
    if [ $1 == 0 ]; then
        pass
    else
        failed
    fi
}

function pass {
    echo -e "\x1B[32mPassed!\x1B[0m\n"
}

function failed {
    echo -e "\x1B[31mFailed!\x1B[0m\n"
    mplayer support/git/sad-trompone.mp3  > /dev/null &
    exit 1
}

header
php_check
run_composer
clear
run_app
exit 0