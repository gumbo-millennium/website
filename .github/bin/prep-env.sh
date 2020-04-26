#!/usr/bin/env bash

# Repo and mode
GITHUB_REPO="$1"
GITHUB_MODE="$2"

# Pull request
GITHUB_HEAD_REF="$3"

# Push
GITHUB_REF="$4"

# Get variables
DEPLOY_PR_REF="${GITHUB_HEAD_REF}"
DEPLOY_PUSH_REF="${GITHUB_REF}"
DEPLOY_REF="${DEPLOY_PR_REF:-${DEPLOY_PUSH_REF}}"
DEPLOY_REF="${DEPLOY_REF#refs/heads/}"

# Test what deployment to do
DEPLOY_NAME=Staging
DEPLOY_URL=https://staging.gumbo-millennium.nl/
if [ "$DEPLOY_REF" = "master" ]; then
    DEPLOY_NAME=Production
    DEPLOY_URL=https://www.gumbo-millennium.nl/
fi

# Determine hash
DEPLOY_HASH="$( git log -1 --format='%H' )"
if [ "$GITHUB_MODE" = "pull_request" ]; then
    DEPLOY_HASH="$( git log -2 --format='%H' | tail -n1 )"
fi

# Debug
echo "Setting ref to $DEPLOY_REF, deploying $DEPLOY_HASH as $DEPLOY_NAME"

# Set outputs
echo "::set-output name=branch::${DEPLOY_REF}"
echo "::set-output name=name::${DEPLOY_NAME}"
echo "::set-output name=remote::https://github.com/${GITHUB_REPO}"
echo "::set-output name=hash::${DEPLOY_HASH}"
echo "::set-output name=app_url::${DEPLOY_URL}"
