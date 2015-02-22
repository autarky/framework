#!/usr/bin/env bash

git_branch=$(git branch | sed -n '/\* /s///p')

# only master branch + minor version branches should be checked
if [[ $git_branch != "master" && ! $git_branch =~ [0-9].[0-9] ]]; then
	exit 0
fi

# gets the latest tag belonging to the current line of commits
git_tag=$(git describe --abbrev=0)

app_version=$(grep 'const VERSION' classes/Application.php | grep -E -o "[0-9\.]+")

if [[ $git_tag != $app_version ]]; then
	echo "ERROR: version mismatch"
	echo "Latest git tag is       $git_tag"
	echo "Application::VERSION is $app_version"
	exit 1
fi

exit 0
