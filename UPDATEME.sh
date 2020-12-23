#!/bin/bash

# This script aims to adhere to the Google Bash Style Guide:
# https://google.github.io/styleguide/shell.xml

function usage() {
    echo "$0: copy relevant files from Kimai installation to this project; use with caution."
    echo ""
    echo "usage: $0 KIMAI_DIRECTORY_PATH"
    echo "    KIMAI_DIRECTORY_PATH: full system path to directory containing Kimai's index.php"
}

LINK=0;
FORCE=0;
while getopts ":h" options; do
    case $options in
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
if [[ "$#" -lt "1" ]]; then
  usage;
  exit 1
fi

KIMAI_DIRECTORY_PATH=$1;
if [[ ! -d "$KIMAI_DIRECTORY_PATH" ]]; then
  >&2 echo "ERROR: KIMAI_DIRECTORY_PATH $KIMAI_DIRECTORY_PATH is not a directory. Exiting.";
  exit 1;
fi

cd $mydir;
# Ensure mydir is git-tracked.
if ! git ls-files README.md > /dev/null; then
  >&2 echo "ERROR: $mydir is not tracked in a git repo. Exiting.";
  exit 1;
fi

# Ensure target has no modified git-tracked files.
if [[ -n $(git -C $mydir diff-index HEAD) ]]; then
  >&2 echo "ERROR: $mydir git repo has modified tracked files. Exiting.";
  exit 1;
fi

# For all existing project files, remove any existing and then copy from kimai.
FILES=$(find -type f -path "*/*/*" -not -path '*/\.git/*' -not -path '*/\nbproject/*');
cd $KIMAI_DIRECTORY_PATH;
for f in $FILES; do
  rm -f "$mydir/$f";
  >&2 echo "Copying $f..."
  if ! cp --parents -n $f $mydir; then
    >&2 echo "ERROR: Copying failed. Exiting.";
    exit 1;
  fi
done

# For all files under kimai with 'civicrm' in the filename, remove any existing and then copy from kimai.
cd $KIMAI_DIRECTORY_PATH;
FILES=$(find -type f -iname "*civicrm*" -path "*/*/*" -not -path '*/\.git/*' -not -path '*/\nbproject/*');
for f in $FILES; do
  rm -f "$mydir/$f";
  >&2 echo "Copying $f..."
  if ! cp --parents -n $f $mydir; then
    >&2 echo "ERROR: Copying failed. Exiting.";
    exit 1;
  fi
done

>&2 echo "Done."
exit 0;