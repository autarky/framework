#!/usr/bin/env bash

git_branch=$(git branch | sed -n '/\* /s///p')

git_tag=$(git describe --abbrev=0)

if [[ $git_tag = "" ]]; then
	exit 0
fi

app_version=$(grep 'const VERSION' classes/Application.php | grep -E -o "[0-9\.]+")

if [[ $git_tag != $app_version ]]; then
	echo "ERROR: version mismatch"
	echo "Latest git tag is       $git_tag"
	echo "Application::VERSION is $app_version"
	exit 1
fi

exit 0
