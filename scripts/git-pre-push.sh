#!/bin/sh

scripts_dir=$(dirname $(readlink -f $0))
$scripts_dir/check-versions.bash || exit 1

exit 0
