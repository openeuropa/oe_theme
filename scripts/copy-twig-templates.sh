#!/bin/bash

# Source directory containing *.twig files
source_dir="./ecl-build/src/implementations/twig/components"

# Destination directory to copy files with "ecl-" prefix
destination_dir="./components"

# Ensure an empty destination directory exists
[ ! -d $destination_dir ] || rm -rf $destination_dir
mkdir -p "$destination_dir"

# Use 'find' to locate all *.twig files in subdirectories
find "$source_dir" -type f -name "*.twig" -print0 | while read -d $'\0' file; do
    # Extract the filename and extension
    filename=$(basename "$file")

    # Get the last directory in the file path
    last_dir="$(basename "$(dirname "$file")")"

    # Create a directory in the destination with the last_dir name
    target_dir="$destination_dir/twig-component-$last_dir"
    mkdir -p "$target_dir"

    # Append "ecl-" to the filename
    new_filename="ecl-${filename}"

    # Copy the file to the target directory with the new name
    cp "$file" "$target_dir/$new_filename"

    echo "Copied $file to $target_dir/$new_filename"
done
