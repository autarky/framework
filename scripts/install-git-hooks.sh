#!/bin/sh

scripts_dir=$(dirname $(readlink -f $0))
ln -sf $scripts_dir/git-pre-push.sh .git/hooks/pre-push
