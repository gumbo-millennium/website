#!/usr/bin/env bash

# Move to code dir
CODE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )"
cd "$CODE_DIR"

# Get directories
TYPOS="$( realpath "$CODE_DIR/.github/wrong-spellings" )"
declare -a TARGET_FILES=(app bootstrap config database docs html library resources routes tests)

# Run quick check, stopping on first failure
if ! grep -i -q -r -f "$TYPOS" "${TARGET_FILES[@]}"; then
    echo "All Gumbo Millenniums are typed correctly :)"
    exit 0
fi

# Show errors
echo -e "Found typos\n"
grep -i -r -f "$TYPOS" "${TARGET_FILES[@]}"

exit 1

