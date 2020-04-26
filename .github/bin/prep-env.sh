#!/usr/bin/env bash

GITHUB_SHA="$1"
GITHUB_REF="${3:-${2}}"
GIT_BRANCH="${GITHUB_REF:-${$( git symbolic-ref --quiet HEAD )#refs/heads/}}"

# Output
OUTPUT_BRANCH="${GIT_BRANCH}"
OUTPUT_HASH="${GITHUB_SHA:-$( git log -1 --format='%H' )}"

# Set params
echo "::set-output name=branch::${OUTPUT_BRANCH}"
echo "::set-output name=remote::${OUTPUT_REMOTE}"
echo "::set-output name=hash::${OUTPUT_HASH}"
echo "branch → ${OUTPUT_BRANCH}"
echo "remote → ${OUTPUT_REMOTE}"
echo "hash → ${OUTPUT_HASH}"

# Set environment name
test "$GIT_BRANCH" = "master" && echo "::set-output name=env::Production" || echo "::set-output name=env::Staging"
