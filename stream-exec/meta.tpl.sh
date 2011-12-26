#!/bin/sh

test -z "${1}" && echo "Enjoying"
test x"${1}" = "xartist" && echo "{ARTIST}"
test x"${1}" = "xtitle" && echo "{NAME}"

