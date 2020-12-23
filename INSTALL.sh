#!/bin/bash

# This script aims to adhere to the Google Bash Style Guide:
# https://google.github.io/styleguide/shell.xml

function usage() {
    echo "$0: install files from this project into an active Kimai installation."
    echo ""
    echo "usage: $0 [-l] [-f] KIMAI_DIRECTORY_PATH"
    echo "    KIMAI_DIRECTORY_PATH: full system path to directory containing Kimai's index.php"
    echo "    -l : link; create hard-links instead of file copies; recommended for development."
    echo "    -f : force; overwrite of any existing files; if not given, and if any existing files are noted, no files will be copied/linked."
}

# Count arguments so we can decrement the count for each option. This way we can
# know how many non-option args we have.
ARGCOUNT=$#;

LINK=0;
FORCE=0;
while getopts ":lf" options; do
    case $options in
        l ) ARGCOUNT=$((ARGCOUNT-1));
            LINK=1;;
        f ) ARGCOUNT=$((ARGCOUNT-1));
            FORCE=1;;
        h ) usage
            exit 1;;
        \? ) usage
            exit 1;;
        * ) usage
            exit 1;;
    esac
done
shift $(($OPTIND - 1))

# Full system path to the directory containing this file, with trailing slash.
# This line determines the location of the script even when called from a bash
# prompt in another directory (in which case `pwd` will point to that directory
# instead of the one containing this script).  See http://stackoverflow.com/a/246128
mydir="$( cd -P "$( dirname "$(readlink -f "${BASH_SOURCE[0]}")" )" && pwd )/"

# Ensure sufficient arguments (noting that we already discounted for each given option).
if [[ "$ARGCOUNT" -lt "1" ]]; then
  usage;
  exit 1
fi

KIMAI_DIRECTORY_PATH=$1;
if [[ ! -d "$KIMAI_DIRECTORY_PATH" ]]; then
  >&2 echo "ERROR: KIMAI_DIRECTORY_PATH $KIMAI_DIRECTORY_PATH is not a directory. Exiting.";
  exit 1;
fi

cd $KIMAI_DIRECTORY_PATH;
# Ensure target dir is git-tracked.
if ! git ls-files index.php; then
  >&2 echo "ERROR: $KIMAI_DIRECTORY_PATH is not tracked in a git repo. Exiting.";
  exit 1;
fi

# Ensure target has no modified git-tracked files.
if [[ -n $(git -C $KIMAI_DIRECTORY_PATH diff-index HEAD) ]]; then
  >&2 echo "ERROR: $KIMAI_DIRECTORY_PATH git repo has modified tracked files. Exiting.";
  exit 1;
fi

cd $mydir;
# If -f is not specified, test for existing files, and fail with error if any are found.
if [[ "$FORCE" != "1" ]]; then
  for f in $(find -type f -path "*/*/*" -not -path '*/\.git/*' -not -path '*/\nbproject/*'); do
    if [[ -f "$KIMAI_DIRECTORY_PATH/$f" ]]; then
      >&2 echo "ERROR: File $KIMAI_DIRECTORY_PATH/$f exists, but -f not specified; exiting."
      exit 1;
    fi
  done
fi

# For all relevant files, remove any existing and then copy/link to target directory.
for f in $(find -type f -path "*/*/*" -not -path '*/\.git/*' -not -path '*/\nbproject/*'); do
  rm -f "$KIMAI_DIRECTORY_PATH/$f";
  if [[ "$LINK" == "1" ]]; then
    >&2 echo "Linking $f..."
    if ! cp --parents -l $f $KIMAI_DIRECTORY_PATH; then
      >&2 echo "ERROR: Linking failed. Exiting.";
      exit 1;
    fi
  else
    >&2 echo "Copying $f..."
    if ! cp --parents -n $f $KIMAI_DIRECTORY_PATH; then
      >&2 echo "ERROR: Copying failed. Exiting.";
      exit 1;
    fi
  fi
done

>&2 echo "Done."
exit 0;