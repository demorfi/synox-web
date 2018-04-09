#!/bin/sh

##########################################################################
# Synox make.                                                            #
#                                                                        #
# @author  demorfi <demorfi@gmail.com>                                   #
# @version 1.0                                                           #
# @source https://github.com/demorfi/synox                               #
# @license http://opensource.org/licenses/MIT Licensed under MIT License #
##########################################################################

BUILD_DIR=`pwd`/builds
VERSION=2.0
PACKAGES="au-synox bt-synox ht-synox"
EXTENSIONS="aum dlm host"
PATH_DIR=`pwd`

default()
{
    for i in $PACKAGES
    do
        sub_make "$i"
    done
    build
}

clean()
{
    if [ -d $BUILD_DIR ]; then
        echo "del $BUILD_DIR"
        rm -rf $BUILD_DIR
    fi

    if [ -f synox-$VERSION.zip ]; then
        echo "del synox-$VERSION.zip"
        rm -f synox-$VERSION.zip
    fi
}

sub_make()
{
    if [ ! -d $BUILD_DIR ]; then
        mkdir $BUILD_DIR
    fi

    cd $PATH_DIR/src/$1
    ./make

    for i in $EXTENSIONS
    do
        if [ $i ] && [ -f "$PATH_DIR/src/$1/synox.$i" ]; then
            cp $PATH_DIR/src/$1/synox.$i $BUILD_DIR
        fi
    done
}

build()
{
    cd $PATH_DIR
    cp LICENSE $BUILD_DIR
    cp README.md $BUILD_DIR
    cp -R web $BUILD_DIR
    (cd $BUILD_DIR ; zip -r synox-$VERSION.zip ./ ; mv synox-$VERSION.zip ../)
}

if [ ! $1 ]; then
    default
    exit 0
fi

if [ $1 ]; then
    if [ $1 = "clean" ]; then
        clean
        exit 0
    fi

    sub_make "$1"
fi

exit 0
