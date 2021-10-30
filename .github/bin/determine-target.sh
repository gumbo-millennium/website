#!/usr/bin/env bash

# Get branch name
BRANCH_NAME="$(git rev-parse --abbrev-ref HEAD)"

# Flag main branch as production
if [ "$BRANCH_NAME" = "main" ]; then
    echo "::set-output name=name::production"
    exit 0
fi

# Flag develop branch as acceptance
if [ "$BRANCH_NAME" = "develop" ]; then
    echo "::set-output name=name::acceptance"
    exit 0
fi

# Fail if no environment could be determined
echo "::error ::Failed to determine deployment environment"
exit 1
