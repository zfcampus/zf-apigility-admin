#!/bin/bash

FIXER_CONFIG="`dirname $0`/../.php_cs"
FIXER_PATH="`dirname $0`/../vendor/fabpot/php-cs-fixer/php-cs-fixer"

srcCS=$(php $FIXER_PATH fix -v --dry-run --config=$FIXER_CONFIG --level=psr2 ./src --fixers=unused_use)
testCS=$(php $FIXER_PATH fix -v --dry-run --config=$FIXER_CONFIG --level=psr2 ./test --fixers=unused_use)

if [[ "$srcCS" || "$testCS" ]];
then
    echo -en '\E[31m'"$srcCS
$testCS\033[1m\033[0m";
    printf "\n";
    echo -en '\E[31;47m'"\033[1mCoding standards check failed!\033[0m"
    printf "\n";
    exit 2;
fi

echo -en '\E[32m'"\033[1mCoding standards check passed!\033[0m"
printf "\n";

echo $srcCS$testCS
