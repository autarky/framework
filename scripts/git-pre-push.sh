#!/bin/sh

scripts_dir=$(dirname $(readlink -f $0))

$scripts_dir/check-versions.sh || exit 1

exit 0
