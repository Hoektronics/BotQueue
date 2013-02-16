#!/bin/bash

#########################################################################
#
# usage: ./make_release.sh [version]
#
# running this script will make a release file suitable for publishing.
#
# NOTE: I'm dumb, so you need to run this from the top level of botqueue.
# EG: bumblebee/make_release.sh x.y.z
#
#########################################################################

#init up
VERSION=${1:-`date +%Y-%m-%d`}
TO_DIR="bumblebee-$VERSION"

#run our command
git archive --format=zip --prefix="$TO_DIR/" HEAD:bumblebee/ > "$TO_DIR.zip"

#done!
echo "Release v$VERSION created as ${TO_DIR}.zip"
