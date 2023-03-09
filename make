#!/bin/sh

##########################################################################
# Synox make.                                                            #
#                                                                        #
# @author  demorfi <demorfi@gmail.com>                                   #
# @version 2.0                                                           #
# @source https://github.com/demorfi/synox                               #
# @license https://opensource.org/license/mit/                           #
##########################################################################

BUILD_DIR=`pwd`/builds
VERSION=2.0
PACKAGES="au-synox bt-synox ht-synox"
EXTENSIONS="aum dlm host"
PATH_DIR=`pwd`

build()
{
  clean
  if [ ! -d $BUILD_DIR ]; then
    mkdir $BUILD_DIR
  fi

  for i in $PACKAGES
  do
    cd $PATH_DIR/src/$i
    ./make
    for j in $EXTENSIONS
    do
      if [ $j ] && [ -f "$PATH_DIR/src/$i/synox.$j" ]; then
        cp $PATH_DIR/src/$i/synox.$j $BUILD_DIR
      fi
    done
    cd $PATH_DIR
  done

  cp LICENSE $BUILD_DIR
  cp README.md $BUILD_DIR
  cd $BUILD_DIR
  (cd $BUILD_DIR ; zip -r synox-$VERSION.zip ./)
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

  for i in $PACKAGES
  do
    cd $PATH_DIR/src/$i
    ./make clean
  done
}

if [ ! $1 ]; then
  build
  exit 0
fi

if [ $1 ] && [ $1 = "clean" ]; then
  clean
  exit 0
fi

exit 0
